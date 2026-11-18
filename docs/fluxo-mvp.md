# Fluxo e escopo do MVP do SorteandoAqui

Documento vivo, no mesmo formato de `docs/regras-de-negocio.md` e
`docs/arquitetura.md`: um tópico por vez, fechado com decisão explícita. As
regras de produto estão em `regras-de-negocio.md`; aqui fica o recorte do que
entra no MVP e em que ordem.

## Roteiro e andamento (fonte única)

**Fase 0 — Base e planejamento (concluída)**

- [x] Ambiente: Laravel 13, React/Inertia, Docker (MySQL, Redis, MinIO, Mailpit, Horizon)
- [x] Pesquisa do sistema legado (`brrifas-legado.md`)
- [x] Regras de negócio, 8 tópicos (`regras-de-negocio.md`)
- [x] Arquitetura, 7 seções (`arquitetura.md`)
- [x] Base técnica: Pint, defaults do provider, docblocks, Permission, Auditing, Data, testes em MySQL
- [x] GitHub: repositório, README, commits só com a identidade do autor e regra para qualquer IA
- [x] Corte de escopo do MVP (tópico 1 abaixo)

**Fase 1 — Planejamento do MVP (em andamento)**

- [x] Tópico 1: corte de escopo
- [x] Decisões que travam fatias:
  - [x] Campos do sorteio travados após publicar (trava na publicação, ver seção 6 de
        `regras-de-negocio.md`)
  - [x] Percentual da comissão do split (`marketplace_fee`): 5%, a reajustar depois
  - [x] Valores padrão:
    - [x] Expiração da reserva: input em horas, padrão de 1 hora, de 1 a 48
    - [x] Limite de reservas por comprador: padrão 10, de 1 até o total do sorteio
    - [x] Contador do Grátis: mês-calendário, zera no dia 1 de cada mês
    - [x] Revelação da cota premiada: padrão imediato, o organizador pode trocar
  - [x] Cobrança da taxa de ativação: PIX gerado pela conta Mercado Pago da plataforma, com
        ativação automática por webhook (sem conferência manual)
- [ ] Tópico 2: jornadas ponta a ponta
  - [x] Jornada do organizador
  - [ ] Jornada do comprador
- [ ] Tópico 3: ordem das fatias de implementação

**Fase 2 — Implementação por fatias (não começou)**

Ordem provisória, a fechar no tópico 3. Cada fatia vira commits pequenos, com testes e Pint verdes:

- [ ] Empresas e membros (pessoa física como própria empresa)
- [ ] Sorteio: criação, tiers, modalidades, regras do Grátis, edição
- [ ] Números e reservas: fixo e cotas, expiração, limite de reservas
- [ ] Compra sem login e link de acesso por e-mail
- [ ] PIX manual: baixa de pagamento pelo organizador
- [ ] Pesquisa técnica do Mercado Pago (documentação oficial: cobrança PIX, webhook, split,
      repasse programado, estorno), antes da cobrança da taxa de ativação
- [ ] Taxa de ativação (PIX via Mercado Pago, ativação automática por webhook)
- [ ] Página pública do sorteio e vitrine
- [ ] Resultado declarado e prêmio principal
- [ ] Admin mínimo (suspender conta) e e-mails em fila
- [ ] Cota premiada e ranking de compradores
- [ ] Cancelamento com estorno e reembolso individual
- [ ] Painel interno do Suporte, auditoria e seletor de várias empresas
- [ ] Split no Mercado Pago (última fatia)

**Pós-MVP:** colaboradores, assinatura e créditos, domínio próprio, segundo gateway, Vendedor e
leads, denúncia de sorteio.

## 1. Corte de escopo (fechado)

**Decisões do corte:**

- **PIX manual primeiro, split do Mercado Pago por último.** O split entra na
  primeira entrega do MVP, mas é a última fatia, para concentrar toda a
  energia nele.
- **A vitrine pública entra no MVP**, simples, mas visível, para os usuários
  perceberem a movimentação da plataforma.
- **A cobrança da taxa de ativação começa já no primeiro lançamento.** O tier
  Grátis (até 10 números fixos, com as regras de uso da seção 2 de
  `regras-de-negocio.md`) é **sempre grátis** e serve para o usuário testar o
  sistema.

**Recorte:**

| Núcleo do MVP | Ainda no MVP, depois do núcleo | Pós-MVP |
|---|---|---|
| Cadastro e login; pessoa física como própria empresa | Cota premiada e ranking de compradores | Colaboradores, assinatura e créditos de ativação |
| Criar sorteio: 3 tiers, números fixos e cotas, data opcional, expiração de reserva | Cancelamento do sorteio com estorno em massa | Domínio próprio por empresa |
| Taxa de ativação por faixa | Reembolso individual (exceção do organizador) | Segundo gateway de fallback |
| Página pública do sorteio com link direto | Painel interno: Suporte e auditoria | Papel de Vendedor e leads |
| Compra sem login: reserva atômica, limite de reservas, link por e-mail | Seletor de várias empresas por usuário | Denúncia de sorteio |
| Pagamento por **PIX manual** | **Vitrine pública**, simples e visível | |
| Resultado declarado e prêmio principal | Pagamento por **split no Mercado Pago**, com webhook (última fatia) | |
| Suspender conta (Admin mínimo) e e-mails em fila | | |

**Conformidade:** aceite de termos no cadastro e os avisos de LGPD entram
quando o fluxo chegar neles (ver "Itens jurídicos e de conformidade" em
`regras-de-negocio.md`).

## 2. Jornadas ponta a ponta (em construção)

### Jornada do organizador

Passos já decididos, na ordem em que acontecem:

1. **Cadastro e login.** Já existe no starter kit (Fortify). A pessoa física que se cadastra
   vira a própria empresa (seção 1 de `regras-de-negocio.md`).
2. **O painel é a porta de entrada.** Depois de entrar, a pessoa cai no painel da empresa.
   Quando ela ainda não tem nenhum sorteio, o painel mostra um estado vazio ("Você ainda
   não possui nenhum sorteio ativo") e **induz a criar o primeiro sorteio grátis**, com uma
   chamada em destaque, já que o Grátis existe para o usuário testar o sistema. Quem já tem
   sorteios vê a lista deles no lugar desse convite.
3. **Criação em 3 passos curtos**, para o formulário não assustar quem só quer testar (no
   BRRIFAS isso já era uma dificuldade mapeada). O indicador mostra "Passo 1 de 3" desde o
   início, voltar não perde o que foi digitado e cada passo salva o rascunho:
   1. **O sorteio:** nome do prêmio, descrição e fotos.
   2. **Números e valor:** quantidade de números e valor por número, com o tier e a taxa de
      ativação calculados ao vivo. A modalidade segue as regras (o Grátis só tem números
      fixos, e acima de 100 números vira cotas). Logo abaixo fica um bloco opcional e
      recolhido de **prêmios extras** (cota premiada e ranking de compradores, só na Básica e na
      Avançada; no Grátis o bloco não aparece), porque a
      cota premiada depende do total de números. Quem só quer testar não precisa abri-lo.
   3. **Regras:** expiração da reserva (padrão de 1 hora), limite de reservas (padrão de 10) e
      data prevista (opcional).
4. **Tela de revisão com pré-visualização.** A revisão não é um passo, é uma tela: mostra o
   sorteio exatamente como o comprador vai vê-lo, com a mesma página pública e uma faixa de
   "Pré-visualização". Traz o aviso de que, depois de publicar, valor, quantidade, modalidade e
   prêmios travam. A pessoa volta para editar qualquer passo ou confirma clicando em criar. Essa
   pré-visualização era uma feature que o time de UX do BRRIFAS queria e nunca entrou.

5. **Depois do "criar".**
   - No **Grátis**, o sorteio publica na hora e mostra "Seu sorteio está no ar", com o link para
     compartilhar.
   - Nos **pagos**, abre a tela de pagamento da taxa: valor, tier, QR code e "copia e cola" do PIX.
     A tela se atualiza sozinha quando o pagamento é confirmado e mostra "Seu sorteio está no ar",
     com o link.
   - Se a pessoa sair no meio, o sorteio fica no painel como "Aguardando pagamento", com um botão
     para pagar. Continua rascunho, ou seja, editável, até a ativação.
   - Se o PIX vencer, o botão gera um novo. No BRRIFAS a cobrança não podia ser regerada depois de
     expirar, e aqui isso não acontece.

6. **Forma de recebimento.** Sem isso o comprador não tem para onde pagar, então é uma peça
   central da jornada:
   - A empresa cadastra uma ou mais **contas de recebimento** e marca uma como **padrão**. Cada
     sorteio usa a conta padrão, a menos que a pessoa escolha outra conta para aquele sorteio.
     Para a pessoa física, a empresa é ela mesma.
   - Tipos de conta: **PIX manual** (o organizador informa a chave PIX, o nome e a cidade) e
     **Mercado Pago** (conexão da conta, para o split, última etapa do MVP).
   - No PIX manual **não há upload de QR code**: o sistema gera o QR code e o "copia e cola"
     com o valor exato de cada compra, e o dinheiro vai direto para a conta do organizador. A
     baixa do pagamento segue manual, pelo organizador. O formato do PIX (BR Code) será
     confirmado na pesquisa técnica antes de implementar.
   - Um passo chamado **Recebimento** entra na criação só quando a empresa ainda não tem conta
     de recebimento, ou seja, no primeiro sorteio (4 passos no total). Nos seguintes a conta
     padrão já vem escolhida, e a tela de revisão mostra "Recebendo em: conta X, trocar". O
     nome evita "Faturamento", que já é usado em "faturamento potencial" do tier.

7. **Cadastro mínimo, sem onboarding obrigatório.** O painel continua sendo a porta de entrada,
   sem wizard antes dele, para não afastar quem só quer testar. O mínimo é pedido na hora de
   publicar: o **nome de exibição** da empresa (já vem preenchido com o nome da pessoa) e a
   **conta de recebimento** (passo Recebimento). Foto e descrição curta são opcionais e dão
   confiança à página pública. O painel mostra um cartão discreto "Complete seu perfil" com o
   que falta, em vez de uma trava. O CPF ou CNPJ, que a nota de serviço da taxa e da comissão
   provavelmente exige em sorteios pagos, ficou como pesquisa (`pesquisas.md`).

8. **Acompanhamento do sorteio no ar.** Uma tela única do sorteio, com três áreas:
   - **Compartilhar:** o link do sorteio, com botão de copiar e o QR code.
   - **Andamento:** números vendidos, reservados e livres, e o valor arrecadado.
   - **Vendas:** a lista das compras com o status de cada uma. No PIX manual traz o botão
     "Marcar como pago", e nas reservas ativas o botão "Liberar".
   A tela começa simples e melhora com o feedback de quem usa.

9. **Encerramento e registro do resultado.**
   - A tela do sorteio tem o botão "Registrar resultado", disponível a qualquer momento, porque
     a decisão de encerrar é do organizador.
   - Ele informa o **número sorteado** e, opcionalmente, **como foi sorteado** (link da live,
     concurso da Loteria Federal etc.).
   - Só é aceito um número vendido e pago. Senão, uma mensagem explica o motivo: o número não
     existe no sorteio (com a faixa válida), não foi vendido, ou está reservado com o pagamento
     ainda não confirmado (o organizador dá baixa nas vendas e volta). Se o sorteio tem uma
     regra para número não vendido, como "vale o próximo", o organizador a aplica por fora e
     informa o número vencedor, porque o sorteio é sempre externo e a plataforma não decide o
     vencedor.
   - Só o organizador vê o ganhador completo (nome e contato), e o ganhador é avisado por
     e-mail.
   - A página pública mostra "Sorteio encerrado", o número sorteado e o nome do ganhador
     abreviado (primeiro nome e inicial). A cota premiada e o ranking aparecem juntos,
     calculados no encerramento.

Fora do caminho principal, com regras já fechadas: cancelamento com estorno e reembolso
individual (seções 4 e 6 de `regras-de-negocio.md`).

Nota para a ordem das etapas (Tópico 3): a pré-visualização reusa a página pública do sorteio,
então a tela de revisão depende dessa página.
