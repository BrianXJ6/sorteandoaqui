# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

> Documentação em pt_BR por decisão do time: inglês é o padrão só para código (nomes de
> classes, variáveis, rotas internas etc.); documentação e modelo de trabalho seguem pt_BR.
> Exceção: `AGENTS.md` é gerado automaticamente pelo pacote `laravel/boost` em inglês (sem
> opção de idioma) e é sobrescrito a cada `composer update` — tratamos como saída de
> ferramenta, não como documentação nossa, e não tentamos traduzi-lo.

## O que é o SorteandoAqui

Uma plataforma de sorteios online (Laravel + Inertia/React). O nome da marca evita
deliberadamente a palavra "rifa" — ela performa mal em SEO e tráfego pago, e pode remeter a
jogo ilegal. "Sorteio" foi escolhido como um termo limpo e legítimo. Mantenha textos voltados
ao usuário, rotas e linguagem de marketing em torno de "sorteio", nunca "rifa".

**Quem usa:** qualquer pessoa, mas o foco principal são influenciadores realizando sorteios
para seus seguidores. Não é B2B-exclusivo — por exemplo, um responsável organizando um
pequeno sorteio escolar pra custear um passeio também é um usuário-alvo explícito (ver
monetização abaixo).

**Duas modalidades de sorteio (domínio principal, ainda não implementado):**

1. **Padrão** — um conjunto pequeno e fixo de números aparece na tela; o comprador escolhe
   número(s) específico(s), reserva, paga e aguarda o sorteio. Viável de renderizar porque a
   quantidade de números permanece pequena (**até 100**; acima disso a rifa é
   obrigatoriamente de cotas aleatórias).
2. **Cotas / aleatório** — o formato que a maioria dos influenciadores já usa hoje. Cotas
   muito baratas (ex: R$0,05 cada), centenas/milhares de cotas por sorteio. O comprador **não**
   escolhe os números — o sistema atribui números aleatórios disponíveis na compra. Deixar o
   usuário escolher entre milhares de números seria uma UX ruim e travaria o navegador
   tentando renderizar tantos itens selecionáveis.
3. Promoções por quantidade (pacotes de cotas, ex: 50 cotas por R$ 12,00) entram no MVP,
   depois do núcleo (decidido em 2026-11-18).

**Modelo de monetização (3 tipos de rifa, classificados automaticamente pelo faturamento
potencial = valor por número × quantidade):**

- **Grátis** (até R$ 1.000): só números fixos, até 10 números; 1 ativa por vez, máx. 3 por mês.
- **Básica** (até R$ 10.000): números fixos ou cotas aleatórias; taxa única de ativação por faixa.
- **Avançada** (acima de R$ 10.000, sem teto): mesma mecânica, faixas de taxa maiores.
- A taxa de ativação é sempre avulsa, por rifa. Assinatura com créditos de ativação é uma
  ideia ainda em aberto.
- **Recebimento:** PIX manual (esforço zero da plataforma) ou split via conta própria do
  organizador no gateway (Mercado Pago, OAuth). A plataforma **nunca** custodia o dinheiro.

Os detalhes completos e o porquê de cada decisão estão em `docs/regras-de-negocio.md`.

**Multi-tenancy (decidido): leve, "pooled".** App e banco únicos; as rifas pertencem a uma
**empresa** (`companies`) e não direto a um usuário — uma pessoa física que se cadastra é a
própria empresa dela. Pessoas se vinculam à empresa via `company_members` (papéis `owner` e
`colaborador`). Domínio próprio por empresa é um recurso adicional (middleware resolve
domínio → empresa), e um status da conta (ativo/suspenso/bloqueado) dá ao admin o controle
de acesso. Sem `TenantScope` global nem subdomínio por tenant como no xpandeai.

**Posicionamento legal (decidido):** "não somos responsáveis pela rifa, e não somos
intermediador de pagamento" — a plataforma vende só tecnologia, e o organizador responde
pela legalidade, pelo prêmio e pelas obrigações fiscais da rifa dele. O sorteio é sempre
externo e declarado pelo organizador; a plataforma nunca sorteia. Existe um alerta jurídico
em aberto (a lei de rifas só autoriza entidades filantrópicas), tratado como risco aceito e
monitorado — ver `docs/regras-de-negocio.md`, seção 6, antes de qualquer decisão de produto
que mexa nesse tema.

**O xpandeai (`../xpandeai`) é só referência de infraestrutura/configuração** — um projeto
Laravel irmão mais maduro cujo ambiente Docker (dev/prod) e padrões de `config/*.php`
(Redis, MySQL, MinIO, Mailpit, Horizon) foram trazidos para este repositório como ponto de
partida. O produto dele (um SaaS multi-tenant para negócios locais) e seu modelo de domínio
**não** são um modelo a ser imitado aqui — só as convenções de infra/config são relevantes.

## Estado atual

Starter kit Laravel 13 + React/Inertia (`laravel/react-starter-kit`) recém-criado, mais
Laravel Boost, Fortify (auth: login/registro/2FA/passkeys), Wayfinder (helpers de rota
tipados) e Horizon. **Ainda não existe código de domínio de sorteios** — `app/`, `routes/` e
`resources/js/` contêm por enquanto só o scaffolding de auth/settings do starter kit.

As fases de **regras de negócio** e de **arquitetura e convenções** estão concluídas (ver
`docs/`). A próxima etapa é o código de domínio, em passos pequenos e revisáveis.

## Documentação do projeto (ler antes de codar)

- `docs/regras-de-negocio.md` — decisões de produto, uma por tópico, com o porquê de cada
  uma, incluindo o risco jurídico aceito e monitorado.
- `docs/fluxo-mvp.md` — o que entra no MVP e em que ordem (em construção, tópico a tópico).
- `docs/pesquisas.md` — pesquisas e estudos de caso em aberto (concorrentes, jurídico), sem
  prazo e sem bloquear nada.
- `docs/arquitetura.md` — camadas, fluxo de requisição, DTO, pastas, padrões, clean code,
  pacotes de terceiros e empresa atual.
- `docs/brrifas-legado.md` — mapeamento do sistema legado BRRIFAS (regras que valem
  reaproveitar e erros que não podem se repetir).

## Ritmo de trabalho

- Documentar antes de codar: cada decisão entra no documento certo, não só na conversa.
- Passos pequenos o bastante para o time revisar com atenção — muitos arquivos e regras de
  uma vez cansam a revisão e escondem detalhes.
- Não escrever código de domínio sem alinhamento prévio.

## Commits

- Commits e descrições de PR levam **apenas a identidade do autor do repositório**. Nunca incluir
  `Co-Authored-By`, `Signed-off-by` de IA, "Generated with ..." nem qualquer outra linha de
  atribuição a ferramenta ou agente. Isso vale para qualquer IA e substitui o padrão que a
  ferramenta queira adicionar. A regra também está em `.ai/rules/git-commits.md`, que o Boost
  indexa para os agentes.
- Mensagens em inglês, no padrão Conventional Commits, um commit por pacote ou por fatia pequena.
- Só enviar para o GitHub quando o dono do repositório pedir.

## Ambiente de desenvolvimento (Docker)

A aplicação roda em Docker, não no PHP do host. Serviços (`compose.yaml`): `sorteandoaqui`
(app — Nginx + PHP 8.5-FPM + Supervisor rodando Horizon + scheduler), `mysql` (8.4), `redis`,
`minio` (storage compatível com S3), `mailpit` (captura de e-mail), além de `phpmyadmin` e
`redis-commander` como conveniência.

```bash
docker compose up -d                              # sobe tudo
docker compose logs -f sorteandoaqui              # logs do app (nginx/php-fpm/horizon/scheduler)
docker compose exec sorteandoaqui php artisan ...  # rodar artisan
docker compose exec sorteandoaqui composer ...     # rodar composer
docker compose exec -u sail sorteandoaqui npm ...   # rodar npm (como usuário sail)
```

- **App**: http://localhost:8000 · **Painel do Horizon**: http://localhost:8000/horizon
- **phpMyAdmin**: http://localhost:8888 · **Redis Commander**: http://localhost:8081
- **Console do MinIO**: http://localhost:8900 · **Mailpit**: http://localhost:8025

Detalhes completos (o que é supervisionado, comportamento de bootstrap no primeiro boot,
particularidades conhecidas) estão em `docker/develop/README.md` e
`docker/production/README.md`.

**Pegadinhas herdadas do ambiente de referência do xpandeai:**

- `node_modules` é um volume nomeado isolado dentro do container
  (`sorteandoaqui-node-modules-vol`), deliberadamente *não* compartilhado com o `node_modules`
  do host — o container é Alpine/musl e a maioria dos hosts é glibc, então bindings nativos
  (Vite/rolldown) de um lado quebram o outro. Rode `npm` sempre via `docker compose exec`,
  nunca assuma que o `node_modules` do host é usável dentro do container.
- Por causa desse volume nomeado, o diretório de montagem do `node_modules` já existe (vazio)
  desde o primeiro boot do container, então a checagem `[ ! -d node_modules ]` do
  `docker/develop/start-container` nunca dispara o `npm install` automaticamente num container
  novo. Rode `docker compose exec -u sail sorteandoaqui npm install` manualmente depois do
  primeiro `up`.
- O `supervisord.conf` (dev + prod) é copiado pra dentro da imagem no momento do build
  (`COPY`, não bind-mount) — editá-lo exige `docker compose build` + recriar o container, não
  basta reiniciar.
- O bucket do MinIO (`sorteandoaqui-local`) e sua policy de leitura pública **não** são
  criados por nenhum script — foram configurados manualmente via `mc` dentro do container
  `minio` (`mc alias set local http://localhost:9000 <key> <secret>`, `mc mb`,
  `mc anonymous set download`). Se o volume `minio-vol` for apagado algum dia, repita esse
  passo.
- Trocar o Horizon no `supervisord.conf` exige rebuild do container pra valer (ver item
  acima) — ele foi temporariamente substituído por um `queue:work` simples enquanto o pacote
  ainda não estava instalado, e revertido depois que o `laravel/horizon` foi adicionado.

## Internacionalização (preparo, não implementação)

MVP é pt_BR único, sem internacionalização por agora — mas todo texto voltado ao usuário
(mensagens de validação, notificações, UI) deve usar os arquivos de tradução do Laravel
(`__('arquivo.chave')`) desde já, em vez de string solta no código, mesmo só existindo
`lang/pt_BR/`. Isso deixa a arquitetura coerente pro dia em que outros idiomas entrarem —
não vamos nos aprofundar nesse tópico agora, só manter essa disciplina enquanto o código vai
sendo escrito.

## Convenções

As diretrizes do Laravel Boost (formatação via Pint, workflow Artisan-first, comandos de
teste, convenções de Inertia/React etc.) ficam no `AGENTS.md`, que é gerado, está em inglês e
não é versionado. Este `CLAUDE.md` importa esse arquivo em vez de duplicá-lo:

@AGENTS.md

As escolhas de arquitetura próprias do projeto (camadas, Actions, DTO etc.) ficam em
`docs/arquitetura.md`.
