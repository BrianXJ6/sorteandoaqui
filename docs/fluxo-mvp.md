# Fluxo e escopo do MVP do SorteandoAqui

Documento vivo, no mesmo formato de `docs/regras-de-negocio.md` e
`docs/arquitetura.md`. As regras de produto estão em `regras-de-negocio.md`; aqui
ficam o recorte do que entra no MVP e o passo a passo até entregá-lo.

## Passo a passo (fonte única de andamento)

Uma lista só, em ordem. Cada etapa só começa quando a anterior termina, e cada etapa
fechada ganha sua seção neste documento. Não se pula de etapa.

**Concluído até aqui:** ambiente, pesquisa do legado, regras de negócio, arquitetura,
base técnica (Pint, defaults, docblocks, pacotes, testes em MySQL), GitHub e o corte de
escopo (seção abaixo).

**Planejamento do MVP**

- [ ] **Etapa 1** — Decisão: quais campos do sorteio ficam travados depois de publicar
- [ ] **Etapa 2** — Decisão: percentual da comissão do split (`marketplace_fee`)
- [ ] **Etapa 3** — Decisão: valores padrão (expiração da reserva, limite de reservas,
      contador do Grátis, revelação da cota premiada)
- [ ] **Etapa 4** — Decisão: como a plataforma cobra a taxa de ativação
- [ ] **Etapa 5** — Jornada do organizador, do cadastro até receber
- [ ] **Etapa 6** — Jornada do comprador, do link ao bilhete
- [ ] **Etapa 7** — Revisão final da ordem das etapas de implementação (8 a 21)

**Implementação** (ordem provisória, revista na etapa 7; cada etapa vira commits pequenos,
com testes e Pint verdes)

- [ ] **Etapa 8** — Empresas e membros (pessoa física como própria empresa)
- [ ] **Etapa 9** — Sorteio: criação, tiers, modalidades, regras do Grátis e edição
- [ ] **Etapa 10** — Números e reservas: fixo e cotas, expiração, limite de reservas
- [ ] **Etapa 11** — Compra sem login e link de acesso por e-mail
- [ ] **Etapa 12** — PIX manual: baixa de pagamento pelo organizador
- [ ] **Etapa 13** — Taxa de ativação
- [ ] **Etapa 14** — Página pública do sorteio e vitrine
- [ ] **Etapa 15** — Resultado declarado e prêmio principal
- [ ] **Etapa 16** — Admin mínimo (suspender conta) e e-mails em fila
- [ ] **Etapa 17** — Cota premiada e ranking de compradores
- [ ] **Etapa 18** — Cancelamento com estorno e reembolso individual
- [ ] **Etapa 19** — Painel interno do Suporte, auditoria e seletor de várias empresas
- [ ] **Etapa 20** — Pesquisa técnica do Mercado Pago (documentação oficial: split, PIX,
      repasse programado, estorno)
- [ ] **Etapa 21** — Split no Mercado Pago (última etapa do MVP)

**Fora do MVP:** colaboradores, assinatura e créditos, domínio próprio, segundo gateway,
Vendedor e leads, denúncia de sorteio.

## Corte de escopo (concluído)

**Decisões do corte:**

- **PIX manual primeiro, split do Mercado Pago por último.** O split entra na
  primeira entrega do MVP, mas é a última etapa, para concentrar toda a
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
| Resultado declarado e prêmio principal | Pagamento por **split no Mercado Pago**, com webhook (última etapa) | |
| Suspender conta (Admin mínimo) e e-mails em fila | | |

**Conformidade:** aceite de termos no cadastro e os avisos de LGPD entram
quando o fluxo chegar neles (ver "Itens jurídicos e de conformidade" em
`regras-de-negocio.md`).
