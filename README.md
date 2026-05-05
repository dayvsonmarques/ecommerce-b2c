# ecommerce-b2c

## Visão Geral

Este repositório é um monorepo de e-commerce B2C com arquitetura headless.
A aplicação combina:

- **API Laravel 12** (`laravel-app`) como backend headless RESTful.
- **Painel administrativo Next.js** (`admin`) para gestão de catálogo, pedidos e clientes.
- **Loja pública Next.js** (`storefront`) para experiência do cliente final.
- **Design e templates** (`design`) com exemplos e componentes visuais.

A arquitetura é descrita em detalhes em `ARCHITECTURE.md`.

## Arquitetura

A implementação atual segue o modelo:

- **Camada Cliente**: Next.js Admin e Storefront usando React, TypeScript, Tailwind e React Query/Zustand.
- **API Gateway**: rotas `api/v1/*` com autenticação, CORS e middleware.
- **Backend Laravel**: controllers, services, repositories, models, eventos, listeners e jobs.
- **Persistência**: MySQL, Redis e sistema de arquivos para cache/logs.

## Estrutura de Pastas

- `laravel-app/` - API Laravel, banco de dados, serviços, autenticação, catálogo, carrinho, pedidos e pagamentos.
- `admin/` - Painel administrativo Next.js baseado em template NextAdmin.
- `storefront/` - Loja pública Next.js baseada em template Solid.
- `design/` - Materiais e templates de design para UI/UX. Estes arquivos são referências externas e não fazem parte do código de produção do monorepo.
- `nextjs-frontend/` - Pasta atualmente vazia / placeholder para aplicação front-end adicional.

## Observação sobre `design/`

A pasta `design/` contém projetos externos usados apenas como referência visual e de layout. Ela não deve ser comitada como parte do código-fonte principal e está incluída em `.gitignore`.

## Tecnologias Principais

- Backend: **PHP 8.2**, **Laravel 12**, **Sanctum**, **Pest**, **MySQL**, **Redis**.
- Frontend: **Next.js 16**, **React 19**, **TypeScript**, **Tailwind CSS**.
- Arquitetura: API headless, SSR/SSG para cliente, serviços desacoplados e testes.

## Histórico de commits por branch

O repositório segue a convenção de commits definida em `commit-conventions.md`:

- Formato: `prefix(scope): short message`
- Prefixos: `feat`, `fix`, `chore`, `docs`, `style`, `refactor`, `perf`, `test`
- Mensagens devem ser curtas e claras.

### Branchs atuais

- `main`
  - `feat`: guest cart, pt-BR URLs, public shipping estimate, and fix Suspense
  - `feat`: add admin panel, storefront, CMS pages, product images, and fix auth
  - `chore`: update docs and fix tenancy middleware for SQLite dev mode
  - `feat`: implement multi-tenancy with isolated databases per store and branch support

## Como executar localmente

### 1. Backend Laravel

```bash
cd laravel-app
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run dev
```

> O backend espera um ambiente PHP configurado e pode usar MySQL/Redis conforme `config/database.php`.

### 2. Admin Next.js

```bash
cd ../admin
npm install
npm run dev
```

### 3. Storefront Next.js

```bash
cd ../storefront
npm install
npm run dev
```

> Se necessário, ajuste portas para evitar conflito entre `admin` e `storefront`.

## Documentação e Referências

- `ARCHITECTURE.md` - Diagrama completo da arquitetura e fluxo de módulos.
- `EXECUTIVE_SUMMARY.md` - Resumo executivo do projeto.
- `QUICK_REFERENCE.md` - Referências rápidas de uso e comandos.

## Observações

- `nextjs-frontend/` está presente como espaço reservado e não contém código atualmente.
- Para desenvolver novas funcionalidades, comece pela API em `laravel-app` e integre via rotas `api/v1`.
- A estrutura de módulos está alinhada aos fluxos de Auth, Catalog, Cart, Orders, Payments e Eventos.
