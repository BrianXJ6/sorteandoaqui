# Arquitetura e convenções técnicas do SorteandoAqui

Documento vivo com as decisões de arquitetura tomadas antes de escrever o
código de domínio. Mesmo formato do `docs/regras-de-negocio.md`: um tópico por
vez, fechado com decisão explícita. Convenções genéricas de Laravel Boost,
Pint, testes e Inertia/React ficam no `AGENTS.md` e não são repetidas aqui —
este documento cobre só o que é escolha própria do projeto.

Código em inglês (classes, métodos, variáveis); documentação em pt_BR.

## 1. Camadas e fluxo de uma requisição (fechado)

**Fluxo de uma operação de domínio** (ex: criar uma rifa):

```
Rota (middleware: auth, conta ativa)
  → Controller
  → FormRequest  (valida + autoriza)  → getDto()
  → Action       (regra de negócio, transação)
  → Models
  → Resposta Inertia
```

| Camada | Faz | Não faz |
|---|---|---|
| **Controller** | Recebe o FormRequest, chama uma Action, devolve Inertia ou redirect | Regra de negócio, queries, `if` de domínio |
| **FormRequest** | Valida o formato da entrada e autoriza via Policy | Regra que depende do estado do banco (limite de rifas grátis, teto do tier) |
| **Action** | Executa uma operação de negócio inteira dentro de transação, aplica as regras e dispara efeitos colaterais (Job, Notification) | Conhecer HTTP, sessão ou Inertia |
| **Model** | Relacionamentos, casts, scopes e consultas reutilizáveis | Orquestrar operação com vários passos |
| **Job / Notification** | Trabalho assíncrono: e-mail, estorno via API, liberação de reserva expirada | Decidir regra de negócio |

**Onde mora a regra de negócio: na Action, nunca no FormRequest.** Assim a
regra vale para qualquer porta de entrada — tela, Job agendado, comando
artisan ou painel do Suporte. Exemplo: o limite de rifas grátis precisa ser
respeitado também pelo job que cancela rifas após 6 meses, e um FormRequest
não é executado por Jobs.

**Quando exigir Action:** sempre que houver regra de negócio, mais de um
passo, efeito colateral ou reuso. CRUD puro de configurações (como o
`ProfileController` do starter kit) pode ficar só no controller.

**Violação de regra de negócio:** a Action lança uma **exceção de domínio com
nome claro** (ex: `FreeRaffleLimitReached`). O handler converte em erro de
validação ou toast no Inertia, com mensagem via `__()` (disciplina de i18n do
`CLAUDE.md`). Não usamos objeto de resultado (sucesso/falha) por padrão.

**Transação:** quem controla é a Action. Operações sensíveis a concorrência,
como a reserva de número, usam **constraint única no banco + lock**, nunca
"checa e depois grava" sem proteção (bug do BRRIFAS; ver seção 4 do
`docs/regras-de-negocio.md`).

### DTO com `spatie/laravel-data`

O pacote entra **só como DTO tipado**; não substitui o FormRequest e não
carrega regras de validação.

- O **FormRequest continua dono da validação e da autorização.** O DTO não
  repete atributos de validação (`#[Required]`, `#[Max]` etc.) — uma só fonte
  de regras.
- FormRequests que alimentam uma Action com DTO implementam uma interface
  padrão (`ProvidesDto`, com `getDto(): Data`). Cada FormRequest estreita o
  retorno para o seu DTO concreto:

  ```php
  public function getDto(): CreateRaffleDto
  {
      return CreateRaffleDto::from($this->validated());
  }
  ```

  O controller usa sempre `$request->getDto()` e passa o resultado à Action.
- Passamos o **array** de `validated()`, não o `Request`, para o pacote não
  validar de novo.
- O DTO converte tipos na fronteira (string → Enum, texto → `Carbon`), então
  a Action recebe objetos tipados e não array solto.
- **Regra de uso:** DTO quando a Action recebe **mais de 2 parâmetros ou um
  dado estruturado** (ex: criação de rifa, com prêmios, cotas premiadas e
  tier). Com 1 ou 2 parâmetros simples (ex: liberar uma reserva por id), a
  Action recebe os parâmetros direto — sem DTO e sem interface.
- Fora de requisição web (Job, comando, Suporte), o DTO é montado na mão.

**Verificado na instalação (`spatie/laravel-data` 4.23):** compatível com
Laravel 13 e PHP 8.5. `Dto::from($request->validated())` mapeia snake_case para
camelCase e converte os tipos (string numérica vira `int`, valor vira Enum), com
`name_mapping_strategy.input` configurada para `SnakeCaseMapper` em
`config/data.php`. Usamos `validated()` (array) e não `safe()`.

## 2. Organização de pastas (fechado)

**Regra geral: sempre o padrão do Laravel.** Agrupa-se **por tipo e depois
por domínio** (`app/Actions/Raffles/...`), nunca por domínio primeiro
(`app/Domain/Raffles/...`). Motivo: é o padrão validado pela comunidade (um
dev acostumado com Laravel entra mais rápido), é onde os comandos `make:`
já colocam os arquivos e é onde o Fortify já está (`app/Actions/Fortify`).

```
app/
├── Actions/<Domínio>/       # operações de negócio (CreateRaffleAction)
├── Contracts/               # ProvidesDto e futuras interfaces
├── DTOs/<Domínio>/          # DTOs (CreateRaffleDto)
├── Enums/<Domínio>/
├── Exceptions/<Domínio>/    # exceções de domínio
├── Http/
│   ├── Controllers/<Área>/
│   ├── Middleware/
│   └── Requests/<Área>/
├── Jobs/<Domínio>/
├── Models/                  # plano, sem subpastas
├── Notifications/<Domínio>/
├── Policies/
├── Services/                # integrações externas (ex: Mercado Pago)
└── Providers/
```

**Domínios** (derivados das regras de negócio; novos entram conforme a
necessidade):

- `Companies` — empresa, membros, status da conta.
- `Raffles` — rifa, prêmios, tiers, resultado declarado.
- `Tickets` — reservas, cotas e compras.
- `Payments` — gateway, split, estorno.
- `Staff` — equipe interna (Suporte e Vendedor).

**Sufixos:** `Action` nas Actions (`CreateRaffleAction`) e `Dto` nos DTOs
(`CreateRaffleDto`) — facilita achar por busca e separa a identificação.
As demais classes seguem o padrão que o `make:` do Laravel gera.

**Testes** espelham a estrutura (`tests/Feature/<Domínio>/`,
`tests/Unit/Actions/<Domínio>/`). **Frontend** segue o starter kit (nomes
em kebab-case, `pages/`, `components/`, `hooks/`, `layouts/`), só com
subpastas por domínio (`pages/raffles/`, `components/raffles/`).

## 3. Padrões de projeto adotados (fechado)

1. **Sem Repository.** O Eloquent já é a camada de dados. Consulta
   reutilizável vira scope no Model (ex: `Raffle::active()`). Só se cria uma
   classe de consulta dedicada se uma consulta complexa se repetir muito —
   não antes.
2. **Enums nativos com valor string.** O rótulo vem de tradução
   (`__('raffles.status.active')`), nunca de texto solto, alinhado com a
   disciplina de i18n do `CLAUDE.md`. A comparação é sempre pelo Enum.
3. **E-mail e processamento assíncrono sempre em fila, depois do commit.**
   Notifications com `ShouldQueue`, disparadas a partir da Action com
   `afterCommit`, para nada sair antes da transação fechar. Nunca
   `Mail::send` direto. Jobs com `$tries` e `$backoff`.
4. **Webhook do gateway tratado num Job idempotente que reconsulta a API do
   Mercado Pago.** Nunca se confia no corpo recebido. É o padrão que o
   BRRIFAS já fazia certo, e o mesmo Job pode rodar duas vezes sem efeito
   duplicado.
5. **Efeitos explícitos, sem Observers nem Events para regra de negócio.** A
   Action chama tudo o que precisa, e quem lê vê o fluxo inteiro num lugar
   só. Exceção: o Laravel Auditing, que usa observer internamente.
6. **DTO sem `BaseDto` próprio.** Os DTOs estendem direto o `Data` do
   `spatie/laravel-data`. O mapeamento snake_case → camelCase fica na
   configuração global do pacote (`name_mapping_strategy.input`, já configurada). O
   XpandeAI usa um `BaseDTO` para "isolar o pacote", mas trocar o pacote
   mexeria em todos os DTOs de qualquer forma.

**Ponto em aberto que merece tópico próprio:** como o sistema sabe qual é a
**empresa atual** quando um usuário pertence a mais de uma. Sem `TenantScope`
global, isso decide como toda consulta é escopada.

## 4. Clean Code e SOLID (fechado)

### Docblocks e comentários (fechado)

- **Todo método tem docblock**: descrição de 1 linha (2 quando há contexto
  essencial), `@param` com FQCN e `@return`. Serve de visão rápida do que o
  método faz, do que recebe e do que devolve.
- **Formato:** um espaço depois da tag (sem alinhamento com espaço duplo) e
  uma linha em branco entre o grupo de `@param` e o `@return`:

  ```php
  /**
   * Create the raffle for the given company.
   *
   * @param \App\DTOs\Raffles\CreateRaffleDto $dto
   *
   * @return \App\Models\Raffle
   */
  public function handle(CreateRaffleDto $dto): Raffle
  ```

- **O texto do docblock é em inglês**, como todo o código (identificadores,
  mensagens de commit técnicas). Documentação fora do código segue pt_BR.
- **Escopo:** todo método das classes de `app/`. Testes e migrations ficam de
  fora. Classes simples usam FQCN (`\App\Models\User`); os tipos genéricos de
  array que o próprio Laravel já traz (ex: `array<string, ValidationRule|...>`)
  mantêm os nomes importados, porque em FQCN virariam linhas ilegíveis. Os
  métodos de `void` levam `@return void`.
- **Comentários no meio do código são evitados** — sujam a leitura. A
  explicação mora no docblock do método, não nas linhas de dentro. Só entra
  um comentário interno quando há uma **necessidade real**, ou seja, um
  "porquê" não óbvio (restrição escondida, workaround de bug).
- Decisão tomada pelo time depois de ponderar a alternativa de "sem
  docblock quando a assinatura já tem os tipos": o ganho de leitura rápida
  vale mais que a repetição.

**A configurar quando o Pint for ajustado (não agora):** o Pint precisa
**manter** essas tags (não remover as "redundantes") e aplicar o formato
acima sozinho, para a regra não depender de disciplina manual. Regras a
validar num arquivo de teste: `no_superfluous_phpdoc_tags`, `phpdoc_align`
e `phpdoc_separation`. O `pint.json` do XpandeAI (preset `psr12`, com
`no_superfluous_phpdoc_tags` desligada) é o ponto de partida.

### Regras de código (fechado)

1. **Responsabilidade única.** Uma Action é uma operação de negócio, com um
   único método público `handle()`. Se o nome pede um "e" (ex:
   `CreateRaffleAndChargeFee`), divide. Uma Action pode injetar outras
   Actions pelo construtor para compor. Sem classe-deus tipo `RaffleService`
   com vários métodos.
2. **Retorno antecipado.** Guard clauses no começo do método em vez de `if`
   aninhado, com no máximo 2 níveis de indentação. Sem limite numérico de
   linhas: o critério é "faz uma coisa só".

   ```php
   if (!$raffle->isActive()) {
       return;
   }

   if (!$raffle->hasPaidTickets()) {
       return;
   }

   // trabalho real, sem aninhamento
   ```

3. **Sem abstração prematura.** Não criar interface nem contrato para algo
   com uma só implementação. O gateway de pagamento começa como uma classe
   concreta que encapsula o SDK do Mercado Pago. **Necessidade futura
   conhecida:** um segundo gateway como fallback (regra de negócio: não
   perder venda se o Mercado Pago cair) — quando ele existir, extrai-se a
   interface. Não implementar agora.
4. **Injeção de dependência.** Classes nossas entram por construtor (nas
   Actions) ou por method injection (nos controllers). Facades do framework
   (`DB`, `Storage`, `Log`) são aceitas.
5. **Variações em Enum, não em `if` espalhado.** Tier da rifa, status e tipo
   de prêmio viram Enums com métodos (ex: `RaffleTier::fromPotentialRevenue()`).
   Regra nova vira `case` novo, não edição de vários `if/else`.
6. **Parâmetros de negócio são constantes na classe dona da regra** (Model
   ou Enum), nunca número mágico solto. Ex: `Raffle::RESERVATION_MIN_MINUTES`
   e `Raffle::RESERVATION_MAX_MINUTES`; tetos e limiares por tier em
   `RaffleTier`. Cada valor mora em um único lugar. `config/*.php` fica só
   para o que depende de ambiente (chaves, URLs, timeouts). Se um dia o
   Admin precisar editar taxas ou percentuais sem deploy, esse valor sai
   das constantes e vai para o banco — decidir quando surgir.
7. **Dinheiro sempre em centavos inteiros**, nunca `float`, formatando só na
   exibição (com cotas de R$ 0,05, o erro de arredondamento de float seria
   real).

## 5. Pacotes de terceiros (fechado)

Avaliação da lista de pacotes do XpandeAI (instalados e pendentes) contra o
que o SorteandoAqui precisa. Nada é instalado agora — só decisão do que entra
e quando.

| Pacote | Decisão | Quando |
|---|---|---|
| `spatie/laravel-permission` | Aplicar (já decidido, papéis internos) | Implementação |
| `owen-it/laravel-auditing` | Aplicar (já decidido, auditoria de edições do Suporte) | Implementação |
| `spatie/laravel-data` | Aplicar (já decidido, só como DTO) | Implementação |
| SDK do Mercado Pago | Aplicar (gateway decidido) | Fase de pagamento |
| `sentry/sentry-laravel` | Aplicar, com `company_id`/`user_id` no contexto e sem dado pessoal (LGPD) | Perto do deploy |
| `spatie/laravel-health` | Aplicar (banco, Redis, Horizon, agendador, disco) | Perto do deploy |
| `spatie/laravel-backup` | Aplicar só em produção, destino S3 | Perto do deploy |
| Telescope | Opcional, só dev | Sob demanda |
| Log Viewer | Ignorar (o Pail já cobre o log em dev) | — |
| Pulse | Ignorar por ora (faz sentido com tráfego real) | Pós-lançamento |
| Ziggy | Ignorar (o Wayfinder já faz esse papel) | — |
| Sanctum | Ignorar (sem API/app mobile; o Fortify cobre a sessão) | — |
| `qrcode.react` | Ignorar por ora (o QR do PIX vem do gateway) | Se surgir QR do link da rifa |
| WhatsApp API, agente de IA | Ignorar (fora do MVP) | — |

**Aproveitar do XpandeAI:** um `BaseDTO` abstrato que isola o
`spatie/laravel-data` (se o pacote for trocado, muda uma classe só) — a
decidir no tópico de padrões de projeto.

**Armadilhas documentadas no XpandeAI, a lembrar ao instalar:**
- Nunca usar `app()->...` dentro de `config/*.php`; só em Service Providers.
- `Gate::define` precisa de `?Authenticatable $user = null` para autorizar
  visitante.
- O Auditing não audita em testes por padrão (`audit.console = false`).
- O Telescope exige `dont-discover` no `composer.json` para não quebrar
  produção.

**Não importar do XpandeAI:** a regra de docblock sem formato definido (a
nossa está na seção 4) e a versão PHP (ele roda 8.4, nós 8.5 — confirmar a
compatibilidade de cada pacote com Laravel 13 e PHP 8.5 na hora de instalar).

## 6. Empresa atual (fechado)

Um usuário pode pertencer a mais de uma empresa (via `company_members`) — ex:
gestor ou agência que cuida de vários influenciadores. Como não há
`TenantScope` global, a "empresa atual" precisa ser explícita.

1. **Permitido.** Os papéis `owner` e `colaborador` ficam na vinculação, então
   a mesma pessoa pode ser dona de uma empresa e colaboradora de outra. Os
   papéis internos (Admin, Suporte, Vendedor) continuam no Spatie, separados.
2. **A empresa vai na URL** (`/empresas/{empresa}/sorteios`), nunca só na
   sessão — a sessão se confundiria com duas abas abertas em empresas
   diferentes.
   - A empresa é resolvida **só entre as empresas do usuário logado**. Quem
     não é membro recebe 404, que também não revela se a empresa existe.
   - Rotas aninhadas usam **scoped bindings** do Laravel: o sorteio de
     `/empresas/{empresa}/sorteios/{sorteio}` é buscado *através* da empresa,
     então um recurso de outra empresa nunca é encontrado, mesmo com o ID
     certo. Isso evita a armadilha de ordem entre binding e middleware de
     tenant documentada no XpandeAI, porque aqui a empresa é a raiz do
     binding.
   - Na implementação, cobrir com teste de regressão: não-membro recebe 404 e
     recurso de outra empresa não é encontrado.
3. **Experiência de uso** para ninguém se confundir de empresa:
   - Seletor de empresa sempre visível no topo da barra lateral, com nome e
     logo. Quem tem só uma empresa entra direto nela.
   - Ações sem volta citam a empresa pelo nome na confirmação (ativar e pagar,
     cancelar com estorno, estornar uma compra).
   - Depois do login: sem empresa vai para o cadastro da primeira (a pessoa
     física vira a própria empresa); com uma entra direto; com duas ou mais
     abre a **última usada**, com o seletor para trocar.
   - Empresa suspensa ou bloqueada aparece desabilitada no seletor, com o
     motivo.

A área autenticada fica no domínio principal, com a empresa na URL. O domínio
próprio de cada empresa (seção 1 de `docs/regras-de-negocio.md`) vale só para
as páginas públicas.

## 7. Fluxo de trabalho, Pint e defaults do projeto (fechado)

**Princípio de precedência:** decisões globais da linguagem PHP seguem a
**PSR** (independente de framework); decisões do framework seguem o **Laravel
e sua comunidade**. Consequências:

- O Pint parte do `pint.json` do XpandeAI (preset `psr12` com regras
  explícitas), ajustado com `phpdoc_align` (`left`) e `phpdoc_separation` para
  manter e formatar os docblocks (seção 4). Validado numa amostra: tags
  redundantes e FQCN são mantidos, e tags de tipos diferentes (`@param`,
  `@return`, `@throws`) ficam separadas por uma linha em branco.
- Negação sem espaço: `!$x`. A PSR-12 não define esse espaço, então vale o
  estilo do XpandeAI.
- Métodos de teste em camelCase (`testCreatesRaffle`), como pede a PSR-1.

**Git e commits:**

- Repositório criado na branch `main`. O `CLAUDE.md` é versionado; o
  `AGENTS.md` continua ignorado, porque é gerado.
- O Boost escreve suas diretrizes **só no `AGENTS.md`**: o `config/boost.php`
  fixa o `guidelines_path`, e o `CLAUDE.md` importa o arquivo com `@AGENTS.md`.
  Sem isso, o Boost injeta o bloco em inglês no `CLAUDE.md` a cada
  `composer require`, porque ele escolhe o `CLAUDE.md` sempre que o arquivo
  existe. O `CLAUDE.md` fica exclusivamente para o nosso fluxo, em pt_BR.
- Mensagens em inglês, no padrão Conventional Commits (`feat:`, `chore:`,
  `docs:`).
- **Um commit por pacote instalado** e **um commit por fatia pequena de
  domínio** (migration, model, factory e teste de um assunto). Nunca misturar
  domínios no mesmo commit. Todo commit deixa testes e Pint verdes. O objetivo
  é cada passo ser revisável.

**Defaults do projeto** (no `AppServiceProvider` único do starter kit, em
métodos privados; dividir em providers só quando crescer):

- Já vêm no starter kit: `DB::prohibitDestructiveCommands` em produção,
  `Date::use(CarbonImmutable)` e senha forte em produção (mínimo 12 e
  `uncompromised()`).
- Aplicados: `Model::shouldBeStrict()` fora de produção (lazy loading,
  atributo descartado ou inexistente viram exceção em dev e teste) e
  `URL::forceHttps()` em produção.
- Rate limiter: **sem limiter global por IP**. Seguidores que entram numa live
  pela mesma operadora de celular compartilham poucos IPs e seriam bloqueados
  juntos na hora da venda. O login já é protegido pelo Fortify. Os limiters
  entram junto com as fatias de reserva, compra e reenvio de bilhetes, mais
  estritos e só onde há risco de abuso. O `throttleWithRedis()` do XpandeAI
  também não é necessário: o cache já é Redis, e nos testes é `array`.
- Depois, no deploy: `trustProxies`.
- Do XpandeAI, ignorados: `JsonResource::withoutWrapping()` (sem Resources),
  `Schema::defaultStringLength(191)` (MySQL antigo),
  `automaticallyEagerLoadRelationships()` (esconde N+1) e
  `trustHosts(subdomains: true)` (briga com domínio próprio por empresa).

**Equipe interna:** fica na mesma tabela `users`, no guard `web`, com papel
global do Spatie (Admin, Suporte, Vendedor) e sem vínculo a empresa. Um login
só, e as rotas internas protegidas por middleware de papel.
