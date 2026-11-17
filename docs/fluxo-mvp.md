# Fluxo e escopo do MVP do SorteandoAqui

Documento vivo, no mesmo formato de `docs/regras-de-negocio.md` e
`docs/arquitetura.md`: um tópico por vez, fechado com decisão explícita. As
regras de produto estão em `regras-de-negocio.md`; aqui fica o recorte do que
entra no MVP e em que ordem.

Tópicos planejados: (1) corte de escopo, (2) jornadas ponta a ponta,
(3) ordem das fatias de implementação.

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
