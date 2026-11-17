# Regras de negócio do SorteandoAqui

Documento vivo com as decisões de produto/arquitetura tomadas antes da fase de
codificação. Cada tópico é discutido e fechado antes de virar código — ver
`docs/brrifas-legado.md` para o material de referência do sistema legado que
inspirou várias dessas regras (BRRIFAS), e `CLAUDE.md` para o contexto geral
do produto.

Convenção: cada tópico tem um resumo da decisão, o raciocínio por trás (pra
não perder o "porquê" depois), e pontos ainda abertos/adiados pra outro tópico.

## 1. Multi-tenancy e contas de organizador (fechado)

**Decisão:** multi-tenancy leve, do tipo *pooled* (banco e aplicação únicos,
isolamento por linha/dono) — não o padrão pesado do xpandeai (sem resolução
de tenant por subdomínio aplicada globalmente, sem `TenantScope` automático
em toda query).

**Por quê:** o histórico do BRRIFAS mostrou que o problema real nunca foi
"falta de branding" — era resolver branding fazendo **fork de todo o
código e subindo um servidor novo por cliente grande**, o que tornava o
controle de acesso (bloquear quem não pagou) manual e dependente do time de
infra entrar no servidor. A solução certa pra isso é centralizar dado e
controle, não multiplicar servidores.

**Modelo:**

- Sorteios pertencem a uma **empresa** (`companies`), não direto a um
  `user`. Uma empresa representa o negócio/influenciador; uma pessoa física
  que se cadastra é automaticamente sua própria empresa (1 registro em
  `companies`, 1 membro: ela mesma). Um usuário é uma pessoa que pode
  pertencer a uma ou mais empresas.
- Vínculo entre pessoa e empresa via tabela de membros (`company_members`:
  `company_id`, `user_id`, `role`). Dois papéis no MVP:
  - `owner` — dono da conta, único que fatura, convida/remove membros,
    exclui a conta.
  - `colaborador` — gerencia sorteios (cria, edita, acompanha vendas), sem
    acesso a cobrança nem a convites.
- Autorização via Policy padrão do Laravel: "o usuário autenticado é membro
  dessa conta e o papel dele permite essa ação" — não precisa de escopo
  automático global, é checagem direta por ação.
- **Domínio próprio por organizador** (white-label): feature adicional, não
  estrutural. Um middleware resolve "esse domínio pertence a qual conta" e
  serve a aplicação com a identidade daquela conta — mesma aplicação, mesmo
  banco, só muda o que é renderizado.
- **Controle administrativo central:** status da conta (`ativo` /
  `suspenso` / `bloqueado`), checado no mesmo middleware de autorização,
  independente do domínio usado pra acessar. Suspender é um update num
  campo — nunca mais depende de infra entrar em servidor.

**Gancho pra outro tópico:** "colaboradores" vira um diferencial pago — conta
simples (free) não permite convidar colaboradores (evita o owner precisar
compartilhar login/senha); planos pagos liberam convidar até N pessoas. Nível
de detalhe (limite por tier) fica pro tópico de **Monetização**.

**Adiado:** o que exatamente um domínio próprio altera na experiência além de
branding (ex: SEO próprio, analytics próprio) — discutir se/quando a demanda
aparecer de verdade.

## 2. Criação do sorteio e modalidades (fechado)

**Duas modalidades** (já descritas em `CLAUDE.md`, mantidas aqui só como
referência rápida):

1. **Números fixos** — comprador escolhe número(s) específico(s) numa grade.
2. **Cotas aleatórias** — sistema atribui número(s) disponível(is) na compra,
   sem o comprador escolher. Obrigatório acima de **100 números/cotas**
   (reduzido do limiar de 1000 que o BRRIFAS usava — 1000 bolinhas na tela é
   ruim de UX e o BRRIFAS já forçava esse modo acima de 1000 por esse
   mesmo motivo de performance/renderização).
3. A rifa **Grátis** fica travada em números fixos (nunca passa de 10
   números, então nunca atinge o limiar). Básica e Avançada podem ser fixo
   ou aleatório, seguindo a regra do limiar acima.

**Sem tempo real na tela de compra (decidido pro MVP):** nem WebSocket nem
polling. O grid carrega o estado atual uma vez no load da página; a
validação de verdade acontece no momento do clique de reservar/comprar —
atribuição/reserva **atômica no servidor** (constraint única + transação),
nunca confiando em dado vindo do cliente para decidir o que está disponível
(bug crítico do BRRIFAS: o pool de números candidatos vinha do próprio
cliente). Se o número já foi reservado por outra pessoa, a resposta da API
volta com erro e a tela reage só àquele clique. Pra modalidade de cotas
aleatórias isso é ainda mais simples — não existe grade pra sincronizar, só
um contador de progresso que recarrega no load da página. WebSocket fica
como possível melhoria futura, não MVP.

**Expiração de reserva (resolve o bug do BRRIFAS de número preso pra
sempre):** configurável por rifa pelo próprio dono (ele decide quanto tempo
um número reservado fica garantido pro comprador antes de voltar a ficar
disponível), com validação de **mínimo 10 minutos e máximo 48 horas** — o
teto de 48h funciona como válvula de escape automática, nunca dependendo do
dono configurar certo. Um job agendado roda periodicamente e libera reservas
vencidas com base nesse valor (que nunca passa de 48h).

**As 3 modalidades de criação/ativação de rifa:**

| | Grátis | Básica | Avançada |
|---|---|---|---|
| Faturamento potencial | até R$ 1.000 | de R$ 1.000 até R$ 10.000 | acima de R$ 10.000 (sem teto) |
| Modalidade de números | só fixo (máx. 10 números) | fixo ou aleatório (acima de 100 números, força aleatório) | fixo ou aleatório (mesma regra do limiar) |
| Uso | máx. 1 rifa grátis **ativa** por vez, máx. 3 por mês (contador mensal) | sem limite de quantidade | sem limite de quantidade |

- O teto de faturamento potencial é o que **classifica automaticamente** a
  rifa como Básica ou Avançada — não é uma escolha livre do dono, é
  derivado do valor por número × quantidade de números no momento da
  criação.
- Faturamento potencial acima de R$ 10.000 **obriga** a rifa a ser Avançada
  (não é opcional) — motivo: acima desse valor, custodiar o dinheiro de
  vendas na nossa própria conta antes de repassar ao dono passa a ser um
  risco fiscal/operacional desproporcional; a Avançada resolve isso com
  split automático no ato do pagamento (ver tópico de Monetização).

## 3. Monetização (parcialmente fechado)

**Tabela única de taxa de ativação**, por faixa de faturamento potencial —
reaproveitada do BRRIFAS (valores antigos usados como base, ajustáveis
depois com dado real de mercado):

| Faturamento potencial até | Taxa de ativação | Tier |
|---|---|---|
| R$ 1.000 | grátis (sem taxa) | Grátis |
| R$ 5.000 | R$ 100 | Básica |
| R$ 10.000 | R$ 250 | Básica |
| R$ 50.000 | R$ 500 | Avançada |
| R$ 70.000 | R$ 750 | Avançada |
| R$ 100.000 | R$ 997 | Avançada |
| (catch-all, acima de R$ 100.000) | R$ 1.497 | Avançada |

- **Ativação é sempre avulsa, por rifa, sem bundling** — uma empresa com 3
  rifas Básicas paga 3 taxas (uma por rifa, pela faixa correspondente),
  independente de serem Básicas ou Avançadas misturadas. Não existe desconto
  por volume nem taxa única "por conta".
- **Retenção de percentual sobre vendas:** nas rifas **Grátis e Básica**, o
  dinheiro das vendas entra primeiro na conta/gateway da plataforma (não na
  conta do organizador), a plataforma retém uma % (valor exato **ainda não
  definido**, fica pro detalhamento futuro de Pagamentos) e repassa o
  líquido pro saldo do dono da rifa, que ele saca depois. **Nas rifas
  Avançadas isso não existe** — o Split do gateway já divide o pagamento
  entre plataforma e organizador no ato da transação, sem custódia nossa e
  com declaração fiscal mais exata pros dois lados.
- **Revisado no tópico de Pagamento (seção 5):** a ideia de que a
  custódia (e, portanto, a carteira com saldo sacável) seria obrigatória
  foi **abandonada** — o modelo final (split direto pra conta do próprio
  organizador, ou PIX manual pessoal) nunca deixa dinheiro de terceiro
  parado na nossa plataforma. SorteandoAqui **não precisa de carteira com
  saldo/saque** — no máximo, um resumo financeiro somente-leitura por rifa
  (vendas, comissão, líquido) como conveniência de dashboard, não como
  estrutura de custódia.

**Ideia em aberto, não decidida ainda:** créditos de ativação ("tokens")
para contas assinantes — um plano daria um pool de créditos (ex: 500
tokens) que o dono consome pra ativar rifas sem pagar a taxa da tabela em
dinheiro (ex: 500 tokens ativam uma rifa de até R$ 50.000, ou 2 rifas de até
R$ 10.000 cada, consumindo a mesma lógica da tabela de faixas). Créditos
nunca seriam sacáveis como dinheiro, só uso interno na plataforma. Detalhar
quando entrarmos no tópico de planos de assinatura.

**Comissão do split (decidido, 2026-11-17): 5%** sobre as vendas feitas por split
(o `marketplace_fee`), no mesmo valor para todos os tiers. É um valor baixo de
propósito, para captar os primeiros usuários; quando o uso e a facilidade da
plataforma forem o motivo de eles ficarem, a comissão é reajustada. Fica como
constante na classe dona da regra (seção 4 de `arquitetura.md`). A ideia antiga
de reter uma % em custódia foi abandonada (seção 5). A comparação com o que os
concorrentes cobram está registrada em `docs/pesquisas.md`.

## 4. Reserva e compra de números (fechado)

**Compra rápida sem conta, mantida (decidido):** o comprador não precisa se
cadastrar — informa os dados e compra direto. Motivo: fricção de cadastro
mata venda por impulso, que é o perfil dominante de quem compra número de
sorteio.

**Falha do BRRIFAS identificada e corrigida:** lá, "Meus bilhetes" buscava
só por e-mail/telefone digitado e devolvia os dados na hora — qualquer
pessoa que soubesse o contato de outra conseguia ver os bilhetes dela. No
SorteandoAqui:

- **E-mail é obrigatório** na compra; telefone é **opcional**
  (complementar — útil pro organizador contatar o ganhador, e pronto pra
  quando a API do WhatsApp da Meta for integrada no futuro).
- Cada compra gera um **token único e não-adivinhável** (ligado ao grupo de
  bilhetes daquela transação).
- A tela de confirmação já mostra os bilhetes na hora (óbvio: quem está
  comprando é ela mesma, sem necessidade de prova nesse instante), e o
  link com o token é enviado por e-mail como comprovante/acesso futuro.
- Recuperação posterior ("reenviar meus bilhetes") nunca devolve dados
  direto na tela — só dispara um novo link de acesso pro e-mail informado
  (padrão "esqueci minha senha"). Sabendo só o e-mail de alguém não dá mais
  acesso aos bilhetes dela.
- Esse fluxo é só por e-mail no MVP (sem WhatsApp) — não existe estado
  intermediário capenga pra quem só tem telefone, porque telefone não é
  canal de recuperação de bilhete, só dado de contato complementar.

**Limite de reserva simultânea (não é limite de compra total):** o
problema real não é alguém comprar muito — isso é bom pra receita. O
problema é **sabotagem entre concorrência**: no BRRIFAS, o dono de uma
rifa concorrente conseguia chegar em outra rifa e reservar todos os
números, travando novos compradores de fecharem a compra. A combinação de
duas regras resolve isso:

- **Expiração de reserva** (seção 2, já fechada) limita **por quanto
  tempo** um número fica travado sem pagamento (máx. 48h).
- **Limite de reserva simultânea**, configurável pelo dono da rifa (1 a
  100, reaproveitando a faixa do BRRIFAS), limita **quantos** números/cotas
  a mesma identificação (e-mail, e telefone se informado) pode manter em
  status "reservado" ao mesmo tempo, **naquela rifa**. Uma vez pago, o
  número sai de "reservado" e vira "vendido" — não conta mais pro limite,
  então um comprador legítimo pode comprar em várias rodadas sem travar em
  nada. Vale pras duas modalidades (número fixo e cota aleatória).

**Liberação manual de reserva:** o dono da rifa ou um `colaborador` da
conta (papéis definidos no tópico de multi-tenancy) pode ver as reservas
ativas de uma rifa e liberar manualmente uma reserva antes do job
automático de 48h agir — cobre casos que o job periódico não resolve a
tempo (cancelamento a pedido do comprador, suspeita de fraude, etc.).

## 5. Pagamento (fechado)

**Mudança de rumo importante:** o desenho original (custódia nossa pra
Grátis/Básica, retendo % e repassando líquido pro organizador) foi
**abandonado**. Motivo: dinheiro passando pela nossa própria conta antes
do repasse nos colocaria, na prática, como vendedores do bilhete —
gerando obrigação de emitir nota fiscal por transação e cancelá-la em caso
de estorno, em potencialmente milhares de vendas pequenas (ex: cotas de
R$0,05). Isso é um risco fiscal desproporcional, identificado pelo próprio
usuário durante a discussão.

**Decisão final: duas formas de recebimento disponíveis pro organizador,
não travado em uma só — maleável por design.**

**1. PIX manual** — o organizador usa a própria chave PIX pessoal, fora de
qualquer integração nossa. **Esforço nosso é zero**: não verificamos
comprovante, não mediamos duplicidade, estorno ou qualquer disputa — ele
mesmo marca a cota como paga no próprio painel (um toggle simples, não um
fluxo de aprovação administrado por nós, ao contrário do BRRIFAS, onde
admins conferiam comprovante manualmente). **Não trava a ativação da
rifa** — a taxa de ativação é sempre cobrada e a rifa sempre ativa,
independente do modo escolhido. Em troca de não conectar gateway, o
organizador perde acesso às ferramentas de gestão financeira/estorno do
nosso dashboard (só disponíveis a quem usa split) — isso já funciona como
incentivo natural pra migrar, sem precisarmos forçar nada.

**2. Split (recomendado, habilitado automaticamente a quem conectar
gateway)** — **cada organizador precisa de sua própria conta no gateway**
(Mercado Pago, candidato principal — confiança do público BR, já é
referência no mercado de rifas), **conectada via OAuth** à aplicação da
SorteandoAqui. Não é uma sub-conta criada por nós "por debaixo" da nossa
conta — é a conta própria do organizador, só autorizada a receber
cobranças criadas pela nossa aplicação em nome dele. Se o organizador já
está dispondo a conectar qualquer conta de gateway, não existe razão pra
oferecer um modo "conectado mas sem split" — conectar já habilita split
automaticamente (não custa nada extra a ele, e nós ganhamos comissão).

- Na cobrança (PIX ou outro meio), o gateway já nasce com a regra de split
  configurada: o valor líquido vai direto pra conta MP do organizador, e
  nossa comissão (`marketplace_fee`) vai direto pra conta MP da
  plataforma. **O dinheiro nunca passa pela nossa conta antes do
  organizador receber a parte dele.**
- Comissão justificada pela tecnologia fornecida (confirmação automática,
  dashboard financeiro, fluxo de estorno gerenciável por nós) — não por
  "sermos o intermediário financeiro" no sentido regulatório. Isso importa
  além do fiscal: existe diferença entre ajudar pagamentos a acontecerem e
  ser classificado como **Instituição de Pagamento** perante o Banco
  Central (regime regulatório pesado). Usando o split de um gateway já
  licenciado para isso (MP), a SorteandoAqui nunca precisa desse registro
  — somos só o integrador tecnológico por cima da instituição licenciada.
- **Estorno:** reversão **proporcional** entre as partes é o padrão
  adotado (se o organizador ficou com 90% e nós com 10%, o estorno tira
  90/10 dos dois saldos) — decisão de negócio nossa, não imposição técnica
  (a maioria dos gateways permite configurar diferente). Risco operacional
  conhecido: se o organizador já sacou antes do estorno, não há de onde
  tirar automaticamente. **Isso não é resolvido com carteira/retenção
  nossa** (nunca temos custódia do saldo dele) — a mitigação precisa vir
  de uma capacidade do próprio gateway (ex: repasse programado/com atraso
  em vez de liberação instantânea pro organizador) — validar se o gateway
  escolhido oferece isso.
- **Quem emite nota fiscal da venda do bilhete é o organizador** (a venda
  é dele, o dinheiro cai na conta dele) — não é responsabilidade da
  SorteandoAqui. Nossa própria nota fiscal é **sempre de serviço
  tecnológico** (comissão de intermediação), e pode ser **consolidada
  periodicamente** (por rifa encerrada ou por período, não por
  transação) — desenho nosso, evita o problema de "100 cotas vendidas +
  estorno = 100 interações fiscais" também do nosso lado.
- **Posicionamento legal da SorteandoAqui, travado (frase-âncora da
  decisão):** "não somos responsáveis pela rifa, e não somos
  intermediador de pagamento" — a plataforma fornece **apenas
  tecnologia**, cobrada via comissão (split) ou taxa de ativação (ambos os
  modos). Não é responsável pela legalidade do sorteio, veracidade do
  prêmio, nem por qualquer regularização fiscal ou regulatória da rifa
  (histórico no Brasil inclui exigências específicas pra rifas/sorteios,
  ex: autorizações ligadas à Caixa Econômica Federal em certos casos). E
  não é classificada como **Instituição de Pagamento** perante o Banco
  Central — quem desempenha esse papel regulado é o gateway (MP), nós só
  integramos por cima. Isso precisa estar **formalizado em contrato**
  (Termo de Uso/Contrato do Organizador, com aceite explícito no cadastro
  ou na criação da rifa) — não é só um princípio verbal, é o que sustenta
  essa posição se for questionada.

**Fricção de onboarding (organizador leigo sem conta de gateway):** não é
mais um problema a "resolver" — o modo PIX manual já é o fallback
sempre disponível, sem bloquear a ativação da rifa e sem nenhum esforço
nosso. O que fazemos é **incentivar a migração pro split** (que é
estritamente melhor pros dois lados: ele ganha ferramentas de gestão
financeira/estorno, nós ganhamos comissão) via tutorial guiado de conexão
da conta MP dentro do próprio app, e oferta de consultoria/suporte
assistido pra quem travar nesse passo — nunca via obrigação.

**Itens jurídicos, contábeis e de gateway conhecidos (sem sessão jurídica
dedicada; os dois últimos são verificações técnicas da fase de pagamento):**
- Enquadramento tributário exato da SorteandoAqui (CNAE como
  tecnologia/intermediação de pagamentos).
- Redação do Termo de Uso/Contrato do Organizador (responsabilidade
  fiscal/regulatória, veracidade do prêmio).
- Prazo legal de emissão de NF-e/NFS-e (varia por tipo e município) —
  aplicável à nota de comissão da plataforma.
- Se existe exigência regulatória específica pra rifas/sorteios no Brasil
  além do fiscal comum.
- Se o gateway escolhido suporta repasse programado/com atraso pro
  organizador (mitigação de risco de estorno sem saldo), e como configurar.
- Mecânica exata de split/estorno via PIX no gateway escolhido (validar
  contra documentação oficial, não assumir).

**Necessidade futura registrada (não implementar agora):** um **segundo
gateway de pagamento como fallback** — a regra de negócio é não perder
venda se o Mercado Pago sair do ar. Cuidado de desenho a lembrar: o split
depende de cada organizador ter uma conta conectada via OAuth *naquele*
gateway, então um failover de split exige que o organizador também conecte
uma conta no segundo gateway. O PIX manual não é afetado.

## 6. Sorteio e resultado (fechado, com risco jurídico aceito)

**Mecanismo do sorteio:** sempre **externo e declarado pelo organizador**
(Loteria Federal, live, qualquer método por fora) — **não** construir um
botão de "sortear pela nossa própria plataforma" (RNG nosso). Decisão
embasada em pesquisa jurídica (ver alerta abaixo): concorrentes reais do
mercado brasileiro (Plataforma Rifa, RifaUp, 123Rifas, Sortify) adotam
essa mesma postura deliberadamente, e a Plataforma Rifa declara
textualmente "não realizamos sorteios" mesmo ao conferir automaticamente
contra a Loteria Federal. **Verificação automática contra API da Loteria
Federal também fica fora do MVP** — mesmo isso já é tratado com cautela
pelo mercado, e a variabilidade de regras (3 primeiros dígitos, 3 últimos,
etc., conforme cada rifa define) adicionaria complexidade desproporcional
ao valor. Resultado fica **declarado/confiado**, como no BRRIFAS, mas como
**dado estruturado** (`winning_number`/`winner_id`/`drawn_at` reais, nunca
texto solto sobrescrevendo um campo — correção técnica óbvia, sem
discussão de negócio necessária).

**Múltiplos prêmios por rifa, desde o MVP** — modelo com 3 tipos de prêmio
por rifa, não excludentes entre si:
1. **Sorteio principal** — resolvido pelo mecanismo acima, define o(s)
   colocado(s) (1º, e opcionalmente 2º/3º).
2. **Cota premiada** — número(s) específico(s) definido(s) pelo
   organizador na criação (ex: 1234, 9999, 5050), que premia(m)
   automaticamente quem comprar aquele número, independente do sorteio
   principal.
3. **Ranking de compradores** — prêmio pra quem mais comprou cotas (1º,
   2º lugar em volume).

**Revelação da cota premiada é configurável pelo organizador** — ele
escolhe entre revelar **imediatamente** (assim que o número é vendido) ou
só **no encerramento**, junto do resultado principal. Padrão sugerido:
imediato (é o que o mercado já faz — funciona como gatilho de continuidade
de compra), mas a escolha é sempre do organizador.

**Data prevista de sorteio é opcional.** Se vazia, mostrar aviso
transparente ("Esse sorteio não tem data prevista de sorteio") — não é
responsabilidade nossa quando/como o sorteio ocorre, mas isso precisa ser
visível pro comprador. **Teto de 6 meses** pra rifas sem sorteio realizado
(recurso finito, não pode ficar "pendurada" pra sempre): aviso automático
~30 dias antes do limite notificando o organizador; se não agir, a rifa é
**cancelada automaticamente** no limite, acionando o fluxo de estorno
abaixo.

**Cancelamento de rifa (abandono no meio do caminho):** estorno de todos
os compradores que já pagaram.
- **Split**: estorno **automatizável via job** chamando a API do gateway
  — por isso cada compra precisa guardar a referência da transação no
  gateway (`payment_id` ou equivalente), não é necessário duplicar CPF ou
  outro dado fiscal do comprador (o gateway já guarda o que precisa do
  lado dele). O nome/e-mail/telefone já coletados no fluxo de compra
  (seção 4) já bastam pra identificar quem reembolsar.
- **PIX manual**: 100% manual, sem job nenhum nosso — o organizador lida
  com isso usando os contatos que ele mesmo coletou. Consistente com o
  princípio de "esforço zero nosso" desse modo (seção 5).

**Campos travados depois de publicar (decidido, 2026-11-17, de partida e
revisável):** enquanto o sorteio é rascunho, tudo é editável. A trava acontece
**na publicação (ativação)**, e não na primeira venda, porque a taxa de
ativação depende do valor por número e da quantidade: se esses campos ficassem
editáveis depois de pagar a taxa, daria para pagar a faixa barata e aumentar a
quantidade depois.

| Trava na publicação | Continua editável |
|---|---|
| Valor por número e quantidade total | Descrição e imagens |
| Modalidade (fixo ou cotas) e tier | Data prevista do sorteio |
| Estrutura de premiação (prêmios, cotas premiadas, ranking) | Tempo de expiração da reserva e limite de reservas (valem só para as reservas novas) |

Se for preciso corrigir um campo travado por erro, o Suporte faz isso com
registro de auditoria (seção 7).

**⚠️ ALERTA JURÍDICO DE ALTA PRIORIDADE — modelo de negócio, não só essa
feature:** pesquisa jurídica (2026-11-13, duas rodadas) encontrou que a
base legal de rifas no Brasil (Lei 5.768/1971, hoje sob a Secretaria de
Prêmios e Apostas/Ministério da Fazenda) **só autoriza rifas de entidades
filantrópicas/sem fins lucrativos** — não haveria caminho legal de
autorização pra rifa comercial de pessoa física/empresa com fins de
lucro, independente de quem realiza o sorteio. Caso real: um influenciador
foi condenado em 2026 por promover rifa comercial sem
autorização (escalou a estelionato/lavagem por fraude comprovada, mas a
raiz foi "loteria não autorizada").

Duas teses levantadas pelo usuário pra contornar isso foram pesquisadas e
**nenhuma das duas se sustentou**:

- **"Sorteio" é juridicamente diferente/mais permitido que "rifa"** —
  **refutada pelo próprio texto da lei.** O Art. 1º da Lei 5.768/1971 usa
  "sorteio" como o nome do mecanismo regulado; o critério real não é a
  palavra usada, é se a participação é **gratuita** (vinculada a
  propaganda/compra de outra coisa) ou **paga** (comprar o número/cota é
  o próprio produto — o modelo da SorteandoAqui). Renomear a marca não
  muda o enquadramento.
- **Plataforma como "ferramenta neutra"** (analogia do caderno: quem
  vende a ferramenta não responde pelo uso que o comprador faz dela) —
  **não confirmada, fica em aberto.** Não há precedente específico sobre
  plataforma de rifa protegida (ou responsabilizada) por fornecer a
  infraestrutura funcional (números, premiação, pagamento). O art. 19 do
  Marco Civil protege conteúdo gerado por terceiro (post/comentário) —
  categoria diferente de fornecer a ferramenta que roda o mecanismo de
  uma atividade regulada. Na prática, plataformas reais de rifa só se
  protegem via cláusula própria de Termos de Uso (interesse próprio, não
  validação jurídica independente), e há disputas reais registradas onde
  essas plataformas são puxadas pra brigas sobre repasse de prêmio/valor
  mesmo assim. É um argumento de defesa plausível, não uma proteção
  estabelecida.

**Conclusão: nenhuma das duas teses abre caminho legal novo.** A decisão
de nunca realizar o sorteio pela nossa própria plataforma fica reforçada.

**Terceira rodada de pesquisa (factual: já penalizaram alguma
plataforma?):** não foi encontrado nenhum caso de plataforma de
tecnologia de rifa (RifaUp, 123Rifas, Plataforma Rifa, Sortify etc.)
penalizada, multada, bloqueada ou processada no Brasil — todo enforcement
encontrado (influenciadores condenados, Operação Rifa do MP-RS, outros) tem como alvo o
**organizador/influenciador individual**, nunca a empresa de tecnologia
por trás. Pontos de contexto que pesam contra ler isso como "plataforma
está segura": (1) o relatório de agenda regulatória 2025 da SPA já
**planeja** classificar fornecedores de tecnologia por nível de risco e
contribuição técnica pro dano — ainda não decidido a favor da plataforma,
só sinaliza que a pergunta está em elaboração; (2) no mercado adjacente de
apostas (mesmo órgão, SPA), já houve notificação de 33 instituições de
pagamento e multa direta a uma empresa operadora (R$2,3 milhões) — não
é a mesma lei de rifa, é só postura regulatória análoga do mesmo órgão;
(3) o Ministério da Fazenda já declarou publicamente que o modelo de rifa
paga usado por influenciadores não tem forma de legalização — a lacuna de
enforcement contra plataforma parece refletir fiscalização ainda não ter
chegado lá, não uma posição de isenção.

**Decisão final deste alerta: risco aceito por ora, não um bloqueio.**
Avaliado o conjunto das 3 pesquisas e a réplica do usuário (correta em
separar "jogo de azar" de "loteria não autorizada", e em tratar "SPA
regular fornecedores de tecnologia" como política futura ainda não
definida, não presente), a decisão é seguir construindo e tratar isso
como risco monitorado, com gatilhos explícitos de revisão (não um "ficar
de olho" vago):
- A SPA publicar norma específica sobre rifas (não só apostas).
- Qualquer ação de fiscalização/judicial contra uma **plataforma** de
  rifa (não organizador individual) aparecer.
- O negócio atingir volume que justifique, na visão do próprio usuário,
  pagar por validação jurídica formal.

Sem isso, não se bloqueia planejamento/arquitetura nem lançamento por
conta desse alerta — mas ele fica registrado como decisão consciente de
risco aceito, não como problema resolvido.

## 7. Papéis internos e painel administrativo (fechado)

**Diferente dos papéis `owner`/`colaborador`** (seção 1 — esses existem
**dentro da conta de um organizador**). Este tópico é sobre quem trabalha
**na própria SorteandoAqui**.

**3 papéis internos, simples, sem matriz de permissão granular por
agora:**

- **Admin** — acesso total: financeiro, configuração, edição de
  qualquer coisa.
- **Suporte** — só atendimento ao cliente. Pode editar campos "travados"
  de uma rifa (trava anti-fraude definida na seção 6) **quando identificar
  que é correção legítima**, não tentativa de manipular o sorteio —
  critério de julgamento do próprio suporte, não automatizado. **Sem
  acesso a nenhum dado financeiro/monetário da empresa.**
- **Vendedor** — acompanha o próprio progresso de vendas: leads de
  usuários que se cadastraram mas não ativaram nenhuma rifa, contatados
  via WhatsApp pra conversão. Mesmo conceito do "Seller" do BRRIFAS
  (telesales/CRM interno, nunca um programa de afiliados de criador) —
  já validado como padrão reaproveitável. Detalhe de atribuição/tracking
  de leads fica pra refinar depois.

**Pacotes técnicos já escolhidos** (registrado aqui porque já é decisão,
não só sugestão):
- **`spatie/laravel-permission`** pros papéis/grupos de acesso — cresce
  de "só papel simples" pra permissão granular sem troca de ferramenta,
  se um dia for preciso refinar.
- **`owen-it/laravel-auditing`** pra trilha de auditoria — toda edição do
  Suporte em campo travado de rifa precisa ficar registrada (quem, quando,
  o que mudou), senão a trava anti-fraude da seção 6 fica inútil assim que
  alguém no Suporte pode contornar sem rastro.

**Painel administrativo interno:** sem spec fechado agora, por decisão
explícita — "constrói organicamente conforme a aplicação crescer". Único
mínimo necessário: Admin vê financeiro/configuração geral, Suporte vê
dados de atendimento + edição de rifa (auditada), Vendedor vê os próprios
leads.

## 8. Descoberta de rifas, confiança e reembolso individual (fechado)

**Descoberta pública:** as duas abordagens convivem, não são excludentes
— toda rifa tem uma página própria com link direto (compartilhável pelo
organizador pra audiência dele), **e** existe uma vitrine/catálogo
público na plataforma pra descoberta orgânica. Organizador pode marcar
uma rifa como "não listada" (só acessível por link) se preferir não
aparecer na vitrine — padrão sugerido é listado, pra ajudar organizador
novo/pequeno a ser descoberto e reforçar SEO da plataforma.

**Confiança e combate a golpe do organizador:** mesmo com a posição
jurídica de "não somos responsáveis pela rifa" (seção 5-6), na prática um
comprador lesado reclama com a gente de qualquer jeito — é questão de
reputação, não só jurídica. Mecanismo de **denúncia visível na página da
rifa** pro comprador reportar problema; denúncia é analisada por Suporte,
que escala pra Admin decidir suspensão/bloqueio de conta (mecanismo já
existente, definido na seção 1). Não é prioridade alta no volume esperado
(usuário observa que raramente alguém compra de completo desconhecido —
geralmente é alguém próximo ou influenciador já acompanhado), mas o
mecanismo precisa existir.

**Reembolso individual antes do sorteio (comprador individual, não a
rifa inteira):** **não permitido por regra padrão** — comprador não tem
direito automático de reembolso só porque mudou de ideia. Mas o painel
do organizador tem uma ação de **exceção manual** ("estornar essa
compra específica"), à critério exclusivo dele, pra casos esporádicos
(ex: comprador pediu por razão pessoal e o organizador decidiu atender).
Mesma mecânica de estorno já definida: via API do gateway se for split,
manual entre organizador e comprador se for PIX manual — não é regra
nova, só mais um gatilho pra mecânica que já existe.

## Itens jurídicos e de conformidade (fora do planejamento atual)

**Decisão (2026-11-17): não há sessão jurídica dedicada, e os itens jurídicos
deixaram de ser pendência de planejamento.** O foco é o fluxo do MVP. O risco
regulatório das rifas segue como **risco aceito e monitorado**, com gatilhos de
revisão definidos na seção 6.

O que fica como contexto, e que só vira trabalho quando o fluxo do MVP chegar
nele:

- **Aceite de termos no cadastro** (Termo de Uso/Contrato do Organizador, com
  a posição "não somos responsáveis pela rifa", seção 5).
- **LGPD** nas telas e fluxos: aviso de cookies, política de privacidade e
  consentimento na coleta de dados de compradores e organizadores.
- **Fiscal**: enquadramento (CNAE) e nota de serviço da comissão, conforme
  seção 5.
