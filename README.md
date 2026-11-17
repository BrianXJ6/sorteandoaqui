# SorteandoAqui

Plataforma de sorteios online, pensada para influenciadores e para qualquer pessoa que queira
organizar um sorteio para o seu público. O organizador cria o sorteio, o comprador escolhe ou
recebe os números sem precisar de cadastro, e o pagamento cai direto na conta de quem organiza.

> **Estado do projeto:** em planejamento ativo. A fase de regras de negócio e a de arquitetura
> estão concluídas e documentadas; o código de domínio (sorteios, compras, pagamentos) ainda não
> foi escrito. Hoje o repositório tem a base técnica pronta: starter kit, ambiente Docker,
> padrões de código e pacotes de apoio.

## Stack

- **Backend:** Laravel 13, PHP 8.5, MySQL 8.4, Redis, Horizon (filas)
- **Frontend:** React com Inertia.js, TypeScript e Vite
- **Autenticação:** Laravel Fortify (login, 2FA e passkeys)
- **Armazenamento e e-mail em dev:** MinIO (compatível com S3) e Mailpit
- **Qualidade:** Laravel Pint (PSR-12), Larastan, PHPUnit com testes rodando em MySQL
- **Pacotes de domínio:** `spatie/laravel-permission`, `owen-it/laravel-auditing` e
  `spatie/laravel-data` (usado apenas como DTO)

## Como rodar localmente

Requer Docker. O ambiente completo (app, MySQL, Redis, MinIO, Mailpit) sobe com:

```bash
docker compose up -d
docker compose exec -u sail sorteandoaqui npm install
docker compose exec sorteandoaqui php artisan migrate
```

A aplicação fica em <http://localhost:8000>. No primeiro boot o container cria o `.env` a partir do
`.env.example` e instala as dependências do Composer. Os detalhes do ambiente e as pegadinhas
conhecidas estão em [`docker/develop/README.md`](docker/develop/README.md) e no `CLAUDE.md`.

Testes e estilo de código:

```bash
docker compose exec -u sail sorteandoaqui php artisan test
docker compose exec -u sail sorteandoaqui vendor/bin/pint
```

## Documentação

Cada decisão do projeto é registrada antes de virar código, um tópico por vez:

| Documento | Conteúdo |
|---|---|
| [`docs/regras-de-negocio.md`](docs/regras-de-negocio.md) | Decisões de produto: multi-tenancy leve, tipos de sorteio, monetização, compra sem login, pagamento (PIX manual e split) e papéis internos |
| [`docs/arquitetura.md`](docs/arquitetura.md) | Camadas, Actions e DTOs, organização de pastas, padrões, regras de código, pacotes e fluxo de trabalho |
| [`docs/fluxo-mvp.md`](docs/fluxo-mvp.md) | O que entra no MVP e em que ordem |
| [`docs/brrifas-legado.md`](docs/brrifas-legado.md) | Mapeamento de um sistema legado de rifas, usado como fonte de regras reaproveitáveis e de erros a não repetir |

## Como o projeto é conduzido

- Documentar antes de codar, em passos pequenos o bastante para revisar com atenção.
- Código e mensagens de commit em inglês; documentação em pt_BR.
- Um commit por pacote instalado e um por fatia pequena de domínio, sempre com testes e Pint verdes.
