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
- [ ] Decisões que travam fatias:
  - [x] Campos do sorteio travados após publicar (trava na publicação, ver seção 6 de
        `regras-de-negocio.md`)
  - [x] Percentual da comissão do split (`marketplace_fee`): 5%, a reajustar depois
  - [x] Valores padrão:
    - [x] Expiração da reserva: input em horas, padrão de 1 hora, de 1 a 48
    - [x] Limite de reservas por comprador: padrão 10, de 1 até o total do sorteio
    - [x] Contador do Grátis: mês-calendário, zera no dia 1 de cada mês
    - [x] Revelação da cota premiada: padrão imediato, o organizador pode trocar
  - [ ] Como a plataforma cobra a taxa de ativação (meio de pagamento próprio, antes do split)
- [ ] Tópico 2: jornadas ponta a ponta (organizador e comprador)
- [ ] Tópico 3: ordem das fatias de implementação
- [ ] Pesquisa técnica do Mercado Pago (split, PIX, repasse programado, estorno), antes da última fatia

**Fase 2 — Implementação por fatias (não começou)**

Ordem provisória, a fechar no tópico 3. Cada fatia vira commits pequenos, com testes e Pint verdes:

- [ ] Empresas e membros (pessoa física como própria empresa)
- [ ] Sorteio: criação, tiers, modalidades, regras do Grátis, edição
- [ ] Números e reservas: fixo e cotas, expiração, limite de reservas
- [ ] Compra sem login e link de acesso por e-mail
- [ ] PIX manual: baixa de pagamento pelo organizador
- [ ] Taxa de ativação
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
