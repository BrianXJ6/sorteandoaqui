# BRRIFAS — referência de migração para o SorteandoAqui

> Documento de pesquisa, não de arquitetura. Consolida o que foi encontrado no código do
> **BRRIFAS** (`/home/brian/pessoal/brrifas`), um sistema de rifas online descontinuado
> (nome interno "tecausas", último commit: "Ultimo lançamento - projeto descontinuado"),
> estudado para extrair regras de negócio e fluxos reaproveitáveis para o **SorteandoAqui**.
> Pesquisa feita por 5 sub-agentes em paralelo (2026-11-12), cada um cobrindo uma fatia do
> sistema, 100% leitura — nada no BRRIFAS foi alterado.
>
> **Nada aqui é uma decisão já tomada para o SorteandoAqui.** É o mapa do que existe, do que
> funciona, do que é gambiarra e do que vale a pena reaproveitar (como regra de negócio, não
> como código — a stack é totalmente diferente: Laravel 8/PHP 7.3-8.0/Blade+Vue2 lá,
> Laravel 13/PHP 8.5/Inertia+React aqui).

## Stack e contexto do BRRIFAS

- Laravel 8.54, PHP `^7.3|^8.0`, sem pasta `migrations` — schema mantido só como
  `database/schema/mysql-schema.dump` (squash), que por sua vez está **desatualizado** em
  relação ao código em pelo menos uma tabela (`user_configs` — ver seção 3).
- Frontend: 3 bundles JS separados via Laravel Mix — `web` (site público, Blade + componentes
  Vue pontuais, sem SPA), `user` (SPA Vue 2 completa, painel do criador de rifa) e `admin`
  (SPA Vue 2 completa, painel interno da operação).
- Pagamentos: Gerencianet/Efí (taxa de ativação da rifa) + Mercado Pago (venda de bilhetes).
- Produto original: "rifas/apostas/sorteios para causas sociais" (TECausas) — qualquer
  pessoa podia criar uma rifa, sem curadoria.

## 1. Modelo de domínio e banco de dados

### Tabelas principais e relacionamentos

```
admin_types 1─N admins 1─N users (FK users.admin_id — vínculo de CRM/televendas, não hierarquia)
locations 1─N users, 1─N raffles (location = ESTADO/UF, não cidade — só 27 linhas)

users 1─N raffles
users 1─1 user_wallets 1─N user_wallet_extracts ─N1 user_banks (opcional)
users 1─N user_addresses (texto livre, sem FK pra locations — 2ª fonte de "estado" desconectada)
users 1─N user_banks ─N1 banks (~190 bancos/instituições brasileiras, seed estático)
users 1─1 user_configs (credenciais Mercado Pago DO PRÓPRIO usuário)

raffle_categories, raffle_fees, raffle_numbers, raffle_draw_types, raffle_winner_types
  ─N1─ raffles (tabelas de lookup, sem lógica própria — só listagem)

raffles 1─N raffle_images · 1─1 raffle_charges (cobrança PIX da taxa de ativação)
raffles 1─N raffle_tickets (1 por número vendido/reservado) · 1─N orders

orders 1─N raffle_tickets (order_id nullable — "reserved" ainda não tem order)
orders N─1 users (nullable — comprador de bilhete é SEMPRE anônimo na prática)

raffle_tickets.number_id → SEM FK/constraint, é um inteiro gerado em runtime (0..N-1)
```

**Tabelas-chave:**

| Tabela | Papel | Pontos de atenção |
|---|---|---|
| `raffles` | A rifa em si | `paid_at` = rifa ativada (taxa de setup paga); `status` = pausar/retomar vendas; `gateway` (bool) = liga checkout automático MP, **mas nenhum código escreve nesse campo** — só editável via SQL direto |
| `raffle_tickets` | Cada número individual, vendido ou reservado | `status` só tem `reserved`/`paid` — **não existe** `cancelado`/`expirado`; sem UNIQUE em `(raffle_id, number_id)` |
| `raffle_numbers` | Catálogo de "quantidade total de números" (100 até 100.000) | **Anti-padrão grave**: o `id` da linha É a quantidade (`$incrementing = false`), e o código usa esse FK como limite de loop direto |
| `orders` | Pedido de compra (só existe para pagamento via MP automático) | Várias colunas mortas: `user_id` nunca preenchido, `fee_amount`/`paid_at`/`our_order` nunca usados; enum `status` tem 2 valores nunca atingidos (`Processando`/`Erro`); enum `gateway` tem 4 valores nunca usados (plano multi-gateway abandonado) |
| `raffle_charges` | Cobrança PIX única da taxa de ativação | 1:1 com `raffles` (UNIQUE), sem regeneração possível se expirar |
| `user_wallets` / `user_wallet_extracts` | Saldo sacável do criador de rifa | Ledger bem desenhado, mas **nunca foi fechado** (ver seção 4) |
| `user_configs` | Credenciais MP do criador | Model espera `gateway`/`gateway_name`/`mp_client_id`/`mp_client_secret`, mas só `mp_public_key`/`mp_access_token` existem no dump — **vestígio de OAuth nunca implementado** |

### Bug confirmado na camada de Model

`app/Models/User.php` **não declara** `userWallet()` nem `userAddresses()`, mas
`UserWalletController`, `UserAddressController` e o próprio `UserSeeder` chamam esses
métodos — qualquer uma dessas rotas quebraria em runtime (`BadMethodCallException`) no
snapshot atual do código. A lógica em volta (saldo, extrato, endereço) é válida; só a
"ligação" Eloquent está incompleta/ausente.

### Outras inconsistências de schema

- `raffle_fees.amount`/`user_wallet_extracts.value` são `decimal` sem `unsigned`, enquanto
  todo o resto dos campos monetários é `unsigned` — assimetria sem razão aparente.
- Soft delete só no topo da árvore (`raffles`, `users`, `admins`); filhos (`orders`,
  `raffle_tickets`, `raffle_images`, `raffle_charges`) têm `ON DELETE CASCADE` físico — soft
  delete do pai não cascateia pros filhos; o próprio time já precisou contornar isso com
  `withTrashed()` manual.
- `user_addresses.uf` é texto livre sem FK pra `locations` — duas fontes de verdade
  desconectadas pra "estado".
- Seeders de `Admin`/`User` têm **dados pessoais reais hardcoded** (nome, e-mail, endereço,
  hash de senha) — nunca reaproveitar como está.

## 2. Núcleo do domínio: criar rifa, vender números, sortear

### Criação da rifa (pelo organizador, autenticado)

Campos: categoria, estado (localização do prêmio), tipo de sorteio, tipo de vencedor,
quantidade de números, valor por bilhete, `ticket_auto`, nome do prêmio, detalhes/regulamento,
CPF do responsável (se ainda não tem). Regras:

- **Só 1 rifa pendente de pagamento por vez**, por usuário.
- **`ticket_auto` é forçado para `true` acima de 1000 números**, independente do que o front
  mandou — confirma que o BRRIFAS já reconhecia a necessidade das duas modalidades que o
  SorteandoAqui também vai ter (padrão/manual vs. cotas em massa/aleatório).
- Taxa de ativação calculada por faixa de **faturamento potencial** (`valor_bilhete ×
  quantidade`), cobrada do organizador via PIX (Gerencianet), **uma única vez por rifa**, nunca
  por bilhete vendido. Tabela de faixas (fixa, não percentual):

  | Faturamento potencial até | Taxa |
  |---|---|
  | R$ 1.000 | R$ 50 |
  | R$ 5.000 | R$ 100 |
  | R$ 10.000 | R$ 250 |
  | R$ 50.000 | R$ 500 |
  | R$ 70.000 | R$ 750 |
  | R$ 100.000 | R$ 997 |
  | R$ 999.999 (catch-all) | R$ 1.497 |

- A rifa só fica **comprável de verdade** quando `paid_at` é preenchido — via webhook da
  Efí confirmando o PIX da taxa, **ou** via bypass manual de um admin (`updateUserRafflePaid`,
  sem nenhuma auditoria de quem/por quê).
- Apagar rifa não-paga é **hard delete** (`forceDelete`), inconsistente com o soft delete
  usado no restante do model.

### As duas modalidades de venda — confirmadas, mas mal implementadas

- **Manual** (`ticket_auto = false`): comprador escolhe números específicos num grid.
- **Automática/cotas** (`ticket_auto = true`, obrigatório acima de 1000 números): comprador só
  informa a quantidade.
- **Falha grave no modo automático**: o "pool" de números candidatos pro sorteio aleatório é
  enviado pelo **próprio cliente** (`sort[]` no payload), e o servidor só valida colisão
  *depois* de já ter escolhido — sem retry, falha tudo se colidir. Isso não é nem
  server-authoritative nem resistente a manipulação. **No SorteandoAqui isso precisa ser
  atribuição atômica no servidor**, nunca confiando em dado vindo do cliente para decidir
  "quais números estão livres".

### Fluxo de compra (sempre sem conta — identificação só por e-mail/telefone)

1. Front carrega o grid de números por `GET .../tickets/template`.
2. Comprador informa nome/e-mail/telefone (texto livre, sem verificação) e reserva (manual ou
   automático) — isso cria linhas `raffle_tickets` com `status = reserved`.
3. Limite de bilhetes por comprador (`reservation_limit`, configurável pelo organizador,
   1-100) é checado somando reservas existentes por telefone OU e-mail.
4. Pagamento via Mercado Pago (Checkout Pro, credenciais do **organizador**, não da
   plataforma) **ou**, se a rifa não tem gateway configurado, PIX manual com comprovante por
   WhatsApp e confirmação manual pelo organizador.
5. "Meus bilhetes" depois da compra é buscado só por e-mail/telefone, sem token — **qualquer
   pessoa que souber o contato de outra pode ver os bilhetes dela.**

### Falha crítica e transversal a quase tudo: nenhuma reserva expira

Não existe coluna de prazo (`reserved_until`/`expires_at`) em `raffle_tickets`, nem cobrança
PIX da taxa de ativação, e `app/Console/Kernel.php` está **vazio** — zero jobs agendados no
projeto inteiro. Um número reservado e nunca pago fica bloqueado **para sempre**, até o
organizador apagar manualmente ou o sorteio inteiro ser encerrado (que apaga em massa todo
`reserved` daquela rifa). Isso é a lacuna de negócio mais séria encontrada — qualquer pessoa
pode "sequestrar" números sem nunca pagar.

### Condição de corrida confirmada (sem lock, sem constraint)

A checagem de disponibilidade e a criação das reservas não estão em transação, não usam
`lockForUpdate`, e **não há UNIQUE KEY em `(raffle_id, number_id)` no banco**. Duas requisições
simultâneas para o mesmo número podem ambas passar a checagem antes de qualquer gravação.
**Recomendação para o projeto novo**: unique constraint real na tabela de cotas + transação
com lock cobrindo check+insert, ou melhor, números pré-existentes como linhas e "reservar" via
`UPDATE ... WHERE status = 'available' LIMIT N` atômico.

### O sorteio é 100% manual — "Loteria Federal" é só rótulo

`RaffleDrawType` ("Direto com organizador" / "Loteria federal") e `RaffleWinnerType" (9
opções, incluindo múltiplos prêmios) são **puramente decorativos** — nenhum código lê esses
campos para mudar o comportamento. O sorteio real:

1. Organizador digita manualmente o número sorteado (presumivelmente conferido por fora, ex.
   no site da Caixa).
2. Sistema só valida que aquele número existe com `status = paid` — essa é a única checagem
   de integridade.
3. Apaga todas as imagens e todos os `raffle_tickets` com `status = reserved` (bilhetes nunca
   pagos somem).
4. **O resultado não é dado estruturado** — vira texto HTML sobrescrevendo o campo
   `important_details`. Não existe `winning_number`/`winner_user_id`/`drawn_at`.
5. A rifa é soft-deleted (encerrada) e some da listagem.
6. `RaffleWinnerType` promete múltiplos prêmios (2º, 3º...), mas o endpoint de sorteio só
   aceita **um único vencedor** e já fecha a rifa — inconsistência entre cadastro e
   funcionalidade real.

### E-mails

3 Mailables (reserva registrada, confirmação de pagamento, "você foi sorteado"), todos
**síncronos** (sem `ShouldQueue`) e com falha sempre engolida silenciosamente
(`catch (\Throwable $th) {}` sem log). Sob falha de SMTP, ninguém é avisado e ninguém percebe.

## 3. Pagamentos — dois gateways, papéis bem distintos

| | Gerencianet/Efí | Mercado Pago |
|---|---|---|
| Para quê | Taxa única de ativação da rifa | Venda dos bilhetes |
| Quem paga | O organizador | O comprador do bilhete |
| Quem recebe | A plataforma (conta única, global) | **Direto a conta MP do organizador** (sem split/marketplace) |
| Método | Só PIX | PIX/saldo MP em produção (cartão, boleto e carteira digital **excluídos explicitamente**) |

Não existe comissão sobre venda de bilhete — a monetização da plataforma é só a taxa fixa de
ativação. Cada vendedor usa seu **próprio** access token/public key do Mercado Pago, cadastrado
manualmente (texto plano, sem criptografia) por um admin de suporte — sem nenhum self-service
nem OAuth real (campos de OAuth existem no Model mas são código morto).

**Padrão bom a preservar**: nenhum dos dois webhooks confia no corpo da notificação — ambos
reconsultam a API oficial do gateway para confirmar o status real antes de agir. É a mitigação
correta contra forjar pagamento aprovado via payload falso.

**Padrões a não repetir**:
- Webhook da Efí só processa a **última** notificação de um lote (`Arr::last`), descartando
  eventos anteriores.
- Nenhuma validação de assinatura/HMAC em nenhum dos dois webhooks (mitigado só pela
  reconsulta acima).
- `raffles.gateway` (liga checkout automático) não tem write-path em nenhum lugar do código —
  parece editado só via SQL direto pela equipe, sem nenhuma tela de self-service.
- Status de pagamento MP diferentes de `approved`/`refunded`/`charged_back`/`cancelled`
  (`pending`, `in_process`, `rejected`) **não têm tratamento algum** — o bilhete fica
  "reserved" pra sempre.
- Nenhuma expiração/retry da cobrança PIX de setup — se expira, única saída é apagar a rifa
  inteira e recriar (constraint UNIQUE em `raffle_charges.raffle_id` impede nova cobrança).
- Certificado mTLS da Efí referenciado por caminho hardcoded com nome do tenant
  (`producao-373073-tecausas.pem`) — não generalizável.
- SDKs usados (`gerencianet-sdk-php` ^4.1, `mercadopago/dx-php` ^2.4 "Checkout Pro" v1) são
  antigos — ambos têm versões mais novas hoje (Efí rebatizada, MP com SDK v3 e webhooks
  assinados). Só a lógica de negócio é reaproveitável, não os SDKs em si.

## 4. Usuários, autenticação, carteira, "vendedores" e admin

### Autenticação — guards totalmente separados

`User` (cliente final, cria rifas) e `Admin` (equipe interna) são modelos, guards (`user`/
`admin` por sessão, `userAPI`/`adminAPI` via Sanctum) e tabelas de reset de senha
(`password_user_resets`/`password_admin_resets`) **completamente independentes**, sem
hierarquia entre eles. Só `User` precisa verificar e-mail (`MustVerifyEmail`) — mas o
middleware `verified` está registrado e **nunca é aplicado** a nenhuma rota, então isso é
puramente decorativo na prática. Não existe autorregistro de `Admin` — só outro Admin já
logado pode criar um colaborador.

### 🔴 Achado crítico de segurança — backdoor hardcoded

`AdminLoginController::login` aceita uma senha literal fixa no código (omitida aqui de propósito) para logar como
**qualquer** Admin (bastando saber o e-mail), ignorando totalmente o hash bcrypt real. Isso é
um backdoor de desenvolvimento/suporte esquecido em produção. **Nunca replicar esse padrão.**

### 🔴 Achado crítico de segurança — ausência total de RBAC no admin

Todas as rotas `admin/*` exigem só "estar autenticado como algum admin" — não existe nenhuma
Policy/Gate checando `admin_type_id`. Na prática, qualquer colaborador tipo `Seller`
(televendas) pode, pela API:
- Criar um novo Admin com tipo `Master` (se promovendo a super-admin).
- Trocar o tipo de qualquer admin existente, inclusive rebaixar/promover à vontade.
- Excluir qualquer conta de Admin, inclusive os fundadores.
- **Sobrescrever as credenciais de Mercado Pago de qualquer cliente** sem notificação —
  risco direto de desvio de dinheiro de vendas de bilhete.

O conceito `AdminType` (`Master`/`Seller`) existe no modelo de dados mas não autoriza nada de
verdade. **No SorteandoAqui, qualquer rota administrativa sensível precisa de Policy/Gate
checando o papel — "autenticado" não pode ser sinônimo de "autorizado".**

### Carteira digital (`UserWallet`/`UserWalletExtract`)

Ledger bem desenhado (saldo disponível/bloqueado/total + extrato com `type`
`deposit`/`withdraw` e `status` `processing`/`completed`/`error`), mas **o ciclo nunca foi
fechado**:
- **Crédito**: não há nenhum fluxo de negócio real que credite saldo — o único lugar que
  credita é o seeder de demonstração (um bônus de R$1000 "pros 100 primeiros clientes", ou
  seja, uma promoção de lançamento que existiu). A venda de bilhetes nunca credita a carteira
  porque cai direto na conta MP do próprio organizador (ver seção 3) — a carteira parece ter
  sido pensada para bônus/cashback administrativo, não comissão de venda.
- **Saque**: `withdraw` move saldo de `available` pra `blocked` e cria um extrato
  `processing` — mas **nenhum código muda esse status pra `completed`/`error`** nem debita de
  fato o `blocked_balance`. O fechamento do saque (PIX saindo de verdade) é 100% manual/fora
  do sistema, sem auditoria.

### "Seller" — não é afiliado de rifa, é televendas interno

Importante não confundir: o sistema de `Seller` do BRRIFAS é sobre colaboradores internos
(`Admin` com `admin_type = Seller`) prospectando e atendendo uma carteira de **clientes**
(`User`, via `users.admin_id`), avaliados por quanto **taxa de ativação** os clientes deles
pagaram — não é um programa de afiliados que vende rifa de terceiro, nem influenciador
indicando influenciador. Sem hierarquia entre sellers (estrutura plana). "Comissão" aqui é só
um ranking/relatório informativo — o pagamento de fato ao vendedor, se existe, é fora do
sistema (folha de pagamento manual). Tem bug de corrida (`startProspection`: SELECT+UPDATE
sem lock, dois atendentes podem disputar o mesmo lead) e uma query de ranking com join errado
comentado ao lado do correto ("RASCUNHOS" no código).

**Se o SorteandoAqui quiser um programa de afiliados/indicação real entre criadores de
sorteio, isso precisa ser desenhado do zero — nada daqui é diretamente reaproveitável para
esse caso, só o padrão geral "fila de leads + relatório agregado" como inspiração de UI de
painel.**

### Endereço/localização

`Location` = só as 27 UFs do Brasil (não cidade). `UserAddress` é cadastro 100% manual, texto
livre, **sem nenhuma integração de busca por CEP** e sem FK pra `Location` (duas
representações de "estado" desconectadas). Combinado com o bug da seção 1 (relação ausente no
Model), tudo indica que a feature de endereço ficou inacabada/não é usada em nenhum fluxo de
negócio real (nem entrega de prêmio, nem nota fiscal).

## 5. Frontend — 3 apps misturadas (Blade + Vue 2)

- **`web`** (site público): não é SPA — componentes Vue isolados montados sobre páginas Blade
  tradicionais, dados injetados já serializados no HTML (`:raffle="{{ $raffle }}"`),
  navegação por reload de página normal.
- **`user`** e **`admin`**: duas SPAs Vue 2 completas (vue-router + vuex), cada uma montada
  sobre uma única view Blade catch-all. Transição do site público pro dashboard é sempre
  **hard redirect de página inteira** — nunca client-side routing cross-bundle.
- Não há motivo técnico pra manter essa separação em 3 apps no stack novo — Inertia já resolve
  login→dashboard sem reload, e o Wayfinder (já em uso no SorteandoAqui) substitui o
  `RouteService::laravelRoutes()` artesanal do BRRIFAS (que varria `Route::getRoutes()` pra
  expor nomes de rota ao JS) de forma muito melhor.

### Mapa de telas por persona

- **Visitante anônimo**: home comercial (focada em quem vai *criar* rifa, não em quem compra),
  sobre, FAQ, contato, e a tela central `/rifa/{code}/{slug}` (carrossel + grid de números +
  checkout — um componente Vue monolítico de 678 linhas).
- **Comprador** (sem conta): mesma tela acima — grid com legenda por status e contador, modo
  automático com formulário simples vs. manual com grid clicável, barra sticky de seleção com
  total antes de abrir o checkout.
- **Organizador** (`/cliente/painel/*`): home com CTA de criar rifa (bloqueado até e-mail
  verificado e sem rifa pendente), formulário extenso de criação com cálculo de taxa em tempo
  real, QR Code PIX de ativação, gestão de fotos (crop 16:9 obrigatório, máx. 6), "Encerrar
  sorteio" manual, financeiro (faturamento, ranking de compradores), carteira e dados
  bancários, banners fixos de pendência (e-mail não verificado / sem conta bancária) sempre
  visíveis até resolvidos.
- **Admin/colaborador** (`/admin/dashboard/*`): 3 papéis por `v-if` (gerência/suporte/
  vendedor), ranking de vendedores, atendimento ao cliente (busca por e-mail/telefone/CPF),
  liberação manual de pagamento de taxa, cadastro manual de credenciais MP do cliente,
  gestão de colaboradores.

### Decisões de UX que valem preservar (conceito, não código)

- Compra sem exigir conta — fricção mínima pro comprador de bilhete (mas trocar a
  identificação texto-livre por link assinado/OTP).
- Grid de números com legenda por cor + filtro + contadores.
- Barra sticky de seleção com total dinâmico antes do checkout.
- Trava automática pra modo "cotas aleatórias" acima de certo volume — decisão de produto
  validada (grid gigante é ruim de usar).
- Cálculo de taxa em tempo real durante a criação da rifa.
- Banners fixos de pendência no topo do dashboard.
- Upload de imagem com crop obrigatório em proporção fixa antes do envio.

### Anti-padrões a não repetir

- Identificação do comprador só por e-mail/telefone sem token (já citado).
- Aprovação manual de pagamento via WhatsApp + botão "Liberar" no admin.
- Feedback de pagamento lido só da querystring (`?payment=success`), sem validar nada no
  servidor — pura decoração, falsificável editando a URL.
- Tela de compra inteira num componente monolítico de 678 linhas.
- Duas "homes" nunca limpas (resíduo de pivot/A-B test).
- Acesso a componente ancestral via `this.$parent.$parent.$parent` em vez de evento/callback
  explícito.

## 6. Lista consolidada de riscos de segurança (por gravidade)

**Crítico**
1. Senha-mestra hardcoded que loga como qualquer Admin.
2. Zero RBAC nas rotas de admin — qualquer colaborador pode se promover a super-admin ou
   roubar credenciais de pagamento de clientes.
3. Comprador identificado só por e-mail/telefone, sem token — qualquer um vê bilhetes de
   outra pessoa sabendo seu contato.
4. Credenciais de Mercado Pago de clientes em texto plano no banco.

**Alto**
5. Condição de corrida real na reserva de números (sem lock, sem unique constraint).
6. Nenhuma expiração de reserva nem de cobrança PIX — bloqueio indefinido por inação.
7. Seleção "aleatória" decidida a partir de dado enviado pelo cliente, não pelo servidor.
8. Ciclo de saque da carteira nunca fecha (fica "processing" pra sempre, sem auditoria).
9. Webhooks sem validação de assinatura (mitigado parcialmente por reconsulta à API).

**Médio**
10. `raffles.gateway` editável só via SQL direto, sem write-path na aplicação.
11. Verificação de e-mail decorativa (middleware registrado, nunca aplicado).
12. Dados pessoais reais hardcoded em seeders.

## 7. O que vale reaproveitar como regra de negócio (não como código)

- Duas modalidades de venda (manual vs. cotas aleatórias) com trava automática por volume —
  já validado como necessidade real pelo próprio mercado.
- Taxa única de ativação por faixa de faturamento potencial, como um dos modelos de
  monetização (compatível com o "pagamento por sorteio" já definido para o SorteandoAqui).
- Ledger de carteira (disponível/bloqueado/total + extrato tipado) como desenho de dados para
  saldo sacável — mas fechando o ciclo completo (crédito automático, job de processamento de
  saque, idempotência).
- Grid de seleção de números com legenda por status + contadores + barra de seleção sticky.
- Padrão de nunca confiar no corpo do webhook — sempre reconsultar a API do gateway.
- Guards de autenticação separados (cliente vs. staff interno) como modelo válido — desde que
  acompanhado de RBAC real desta vez.

## 8. O que precisa ser corrigido/repensado de verdade, não só modernizado

- Expiração de reserva (número e cobrança) via job agendado.
- Atribuição de número atômica e server-authoritative (unique constraint + lock), nunca
  confiando em pool enviado pelo cliente.
- Resultado do sorteio como dado estruturado (`winning_number`, `winner_id`, `drawn_at`), não
  texto solto sobrescrevendo um campo de detalhes.
- RBAC real (Policies/Gates) em qualquer rota administrativa sensível.
- Identificação do comprador via link assinado/OTP em vez de texto livre.
- Decidir de verdade se "sorteio pela Loteria Federal" é um recurso implementado (verificação
  automática do resultado oficial) ou só uma informação declarada pelo organizador — no
  BRRIFAS era só rótulo decorativo sem nenhum enforcement.
- Se for reaproveitar o conceito de carteira/saque, fechar o ciclo (hoje fica pendurado em
  "processing" para sempre).

## Fontes

Relatórios de 5 sub-agentes (2026-11-12), cada um cobrindo: banco de dados/Models, núcleo do
domínio de rifas, pagamentos, usuários/carteira/vendedores/admin, e frontend/rotas. Arquivos
exatos lidos estão listados ao final de cada relatório original (não reproduzidos aqui por
brevidade) — qualquer dúvida de detalhe, os caminhos absolutos dentro de
`/home/brian/pessoal/brrifas` estão nos relatórios desta rodada de pesquisa.
