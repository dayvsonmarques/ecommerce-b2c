# 📱 E-commerce B2C - Arquitetura de Projeto

## 🎯 Visão Geral

Monolito Laravel como API headless consumido por Next.js com estrutura profissional, padrões de design e testes.

```
┌─────────────────────────────────────────────────────────────┐
│                    CLIENT LAYER                              │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  ┌────────────────────────┐    ┌────────────────────────┐  │
│  │  Next.js Admin Panel   │    │  Next.js Public Store  │  │
│  │  (SSR/SSG/ISR)         │    │  (SSG/ISR)             │  │
│  │                        │    │                        │  │
│  │  Zustand (state)       │    │  Zustand (cart)        │  │
│  │  React Query (server)  │    │  React Query (server)  │  │
│  │                        │    │                        │  │
│  │  nextjs-admin-dashboard   solid-nextjs-main        │  │
│  └────────────────────────┘    └────────────────────────┘  │
│                                                               │
└─────────────────────────────────────────────────────────────┘
                         ↕️ HTTP/REST
┌─────────────────────────────────────────────────────────────┐
│              API GATEWAY & MIDDLEWARE                        │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  API v1 Routes (CORS, Rate Limit, Auth)             │   │
│  │  /api/v1/{auth,products,cart,orders,...}            │   │
│  └──────────────────────────────────────────────────────┘   │
│                                                               │
└─────────────────────────────────────────────────────────────┘
                         ↕️
┌─────────────────────────────────────────────────────────────┐
│                    LARAVEL 11 API                            │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │   LAYER 1    │  │   LAYER 2    │  │   LAYER 3    │      │
│  │ Controllers  │→ │  Services    │→ │ Repositories │      │
│  │              │  │              │  │              │      │
│  │ • Auth       │  │ • AuthService │ │ • UserRepo  │      │
│  │ • Products   │  │ • CartService │ │ • CartRepo  │      │
│  │ • Cart       │  │ • OrderServ  │  │ • ProductRep│      │
│  │ • Orders     │  │ • PaymentServ │ │ • OrderRepo│      │
│  │ • Payments   │  │ • StockServ  │  │             │      │
│  │ • Addresses  │  │ • ShippingServ│ │             │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
│                                                               │
│  ┌─────────────────────────────────────────────────────┐   │
│  │           ELOQUENT MODELS & ORM                      │   │
│  │  • User, UserAddress                               │   │
│  │  • Product, Category, ProductVariation, ProductSku │   │
│  │  • Cart, CartItem                                  │   │
│  │  • Order, OrderItem                                │   │
│  │  • Payment, Invoice                                │   │
│  │  • Notification, Stock, ShippingRate               │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                               │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │  Events      │  │  Listeners   │  │  Jobs/Queue  │      │
│  ├──────────────┤  ├──────────────┤  ├──────────────┤      │
│  │ OrderCreated │→ │ SendEmail    │→ │ ProcessPay  │      │
│  │ OrderPaid    │  │ UpdateStock  │  │ SendSMS     │      │
│  │ OrderShipped │  │ UpdateCart   │  │ GenerateInv │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
│                                                               │
└─────────────────────────────────────────────────────────────┘
                         ↕️
┌─────────────────────────────────────────────────────────────┐
│                  DATA LAYER & CACHE                          │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  ┌─────────────────┐    ┌──────────────┐  ┌────────────┐  │
│  │   MySQL DB      │    │   Redis      │  │ Filesystem │  │
│  ├─────────────────┤    ├──────────────┤  ├────────────┤  │
│  │ • users         │    │ • Sessions   │  │ • Logs     │  │
│  │ • products      │    │ • Cart TTL   │  │ • Cache    │  │
│  │ • orders        │    │ • Queue Jobs │  │            │  │
│  │ • payments      │    │ • Caching    │  │            │  │
│  │ • notifications │    │ • Locks      │  │            │  │
│  └─────────────────┘    └──────────────┘  └────────────┘  │
│                                                               │
│  ┌─────────────────────────────┐                            │
│  │  External Services (Webhooks)                            │
│  ├─────────────────────────────┤                            │
│  │ • Stripe (Payments)         │                            │
│  │ • AWS S3 (Media)            │                            │
│  │ • Meilisearch (Search)      │                            │
│  │ • Melhor Envio (Shipping)   │                            │
│  │ • SendGrid (Email)          │                            │
│  └─────────────────────────────┘                            │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

---

## 📦 Módulos Implementados

### ✅ **Módulo 1: Auth (Autenticação)**

**Responsabilidades**:
- Registro de usuários
- Login/Logout com tokens Sanctum
- Gestão de perfil
- Múltiplos endereços (billing/shipping)
- Busca de CEP via ViaCEP

**Estrutura de Arquivos**:
```
Auth Module:
├── migrations/
│   ├── create_users_table
│   └── create_user_addresses_table
├── Models/
│   ├── User (HasMany addresses)
│   └── UserAddress (BelongsTo user)
├── Controllers/
│   ├── AuthController (register, login, logout, profile, updateProfile)
│   └── AddressController (CRUD addresses)
├── Requests/
│   ├── RegisterRequest
│   ├── LoginRequest
│   ├── UpdateProfileRequest
│   ├── StoreAddressRequest
│   └── UpdateAddressRequest
├── Resources/
│   ├── UserResource
│   └── UserAddressResource
├── Services/
│   └── AuthService (business logic)
├── Repositories/
│   ├── UserRepository
│   └── AddressRepository
└── Tests/
    ├── RegisterTest
    ├── LoginTest
    ├── ProfileTest
    └── AddressTest
```

**Base de Dados**:
```sql
users
├── id, name, email, password
├── phone, cpf, is_active
├── email_verified_at
└── timestamps

user_addresses
├── id, user_id
├── type (billing/shipping)
├── street, number, complement, neighborhood
├── city, state, postal_code, country
├── is_default
└── timestamps
```

**Endpoints**:
```
POST   /api/v1/auth/register
POST   /api/v1/auth/login
GET    /api/v1/auth/profile (🔒 auth:sanctum)
PUT    /api/v1/auth/profile (🔒 auth:sanctum)
POST   /api/v1/auth/logout (🔒 auth:sanctum)
GET    /api/v1/addresses (🔒 auth:sanctum)
POST   /api/v1/addresses (🔒 auth:sanctum)
PUT    /api/v1/addresses/{id} (🔒 auth:sanctum)
DELETE /api/v1/addresses/{id} (🔒 auth:sanctum)
```

---

### ✅ **Módulo 2: Catalog (Catálogo)**

**Responsabilidades**:
- Gestão de categorias em árvore
- Produtos com múltiplas variações
- SKUs com combinações de variações
- Preços com promoção
- Busca e filtros avançados

**Estrutura de Arquivos**:
```
Catalog Module:
├── migrations/
│   └── create_catalog_tables (6 tabelas)
├── Models/
│   ├── Category (HasMany children, HasMany products)
│   ├── Product (BelongsTo category, HasMany variations, HasMany skus)
│   ├── ProductVariation (HasMany values)
│   ├── ProductVariationValue
│   ├── ProductSku (MorphMany prices)
│   └── ProductPrice (morphs to product/productsku)
├── Controllers/
│   ├── CategoryController (index, show)
│   └── ProductController (index with filters, show, related)
├── Resources/
│   ├── CategoryResource
│   ├── ProductResource
│   ├── ProductVariationResource
│   ├── ProductVariationValueResource
│   └── ProductSkuResource
└── Tests/
    └── CatalogTest (filtros, busca, paginação)
```

**Base de Dados**:
```sql
categories (hierarchical)
├── id, name, slug, description
├── parent_id, _lft, _rgt, _depth
├── image_url, is_active
└── timestamps

products
├── id, category_id, name, slug
├── description, short_description
├── price, cost_price, sku, quantity
├── min_quantity_alert, is_active
└── timestamps

product_variations
├── id, product_id, name (Size, Color)
└── timestamps

product_variation_values
├── id, product_variation_id, value, label
└── timestamps

product_skus
├── id, product_id, sku, price, quantity
├── variation_values (JSON), is_active
└── timestamps

product_prices (polymorphic)
├── id, priceable_id, priceable_type
├── regular_price, sale_price
├── sale_starts_at, sale_ends_at
└── timestamps
```

**Endpoints**:
```
GET  /api/v1/categories
GET  /api/v1/categories/{id}
GET  /api/v1/products?category_id=1&search=term&min_price=10&max_price=100&sort=price&direction=asc&per_page=15
GET  /api/v1/products/{id}
GET  /api/v1/products/{id}/related?limit=5
```

**Recursos**:
- Busca textual (nome, descrição)
- Filtro por categoria e preço
- Paginação customizável
- Ordenação (criação, preço, nome)
- Variações (tamanho, cor, etc)
- SKUs com preços específicos
- Preços promocionais com data

---

## 🔄 Módulos Próximos

### 3️⃣ **Cart Module** (Em desenvolvimento)
- [ ] Migração (carts, cart_items)
- [ ] CartService (add, remove, update quantity)
- [ ] Reserva de estoque com Redis TTL
- [ ] Cálculo de frete automático
- [ ] Sistema de cupons e descontos
- [ ] Endpoints: GET, POST, PUT, DELETE /api/v1/cart/{items}

### 4️⃣ **Order Module** (Roadmap)
- [ ] Máquina de estados (pending → paid → processing → shipped → delivered)
- [ ] Events + Listeners
- [ ] Timeline e histórico
- [ ] Geração de nota fiscal

### 5️⃣ **Payment Module** (Roadmap)
- [ ] Integração Stripe/Pagar.me
- [ ] Webhook handlers
- [ ] Queue jobs
- [ ] PIX, cartão, boleto

### 6️⃣ **Stock Module** (Roadmap)
- [ ] Controle por SKU
- [ ] Reservas temporárias
- [ ] Alertas de estoque baixo

### 7️⃣ **Notification Module** (Roadmap)
- [ ] Email (Mailables + Queue)
- [ ] Database notifications
- [ ] Push (Firebase)

### 8️⃣ **Admin Module** (Roadmap)
- [ ] Endpoints com role:admin
- [ ] CRUD operacional
- [ ] Relatórios

---

## 🏗️ Padrões & Boas Práticas

✅ **Architecture**:
- Service Layer (business logic isolado)
- Repository Pattern (abstração de dados)
- Resource Pattern (API responses formatadas)
- Event-Driven (quando necessário)

✅ **Code Quality**:
- PSR-12 compliant
- Type hints everywhere
- Declare strict_types
- Proper method signatures

✅ **Database**:
- Migrations versionadas
- Relacionamentos Eloquent
- Scopes para queries reutilizáveis
- Índices apropriados

✅ **Testing**:
- Pest PHP framework
- Feature tests por módulo
- Factory pattern para dados

✅ **Security**:
- Laravel Sanctum (SPA tokens)
- Form requests validation
- Eloquent scopes
- Middleware authentication

---

## 📁 Estrutura Completa do Projeto

```
ecommerce-b2c/
│
├── laravel-api/                    ← API Headless (Laravel 11)
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/Api/V1/
│   │   │   │   ├── AuthController ✅
│   │   │   │   ├── AddressController ✅
│   │   │   │   ├── CategoryController ✅
│   │   │   │   ├── ProductController ✅
│   │   │   │   ├── CartController 🔄
│   │   │   │   ├── OrderController 🔄
│   │   │   │   ├── PaymentController 🔄
│   │   │   │   └── AdminController 🔄
│   │   │   ├── Requests/
│   │   │   ├── Resources/
│   │   │   └── Middleware/
│   │   ├── Models/ (8 modelos)
│   │   ├── Services/
│   │   ├── Repositories/
│   │   ├── Events/ 🔄
│   │   ├── Listeners/ 🔄
│   │   ├── Jobs/ 🔄
│   │   ├── Notifications/ 🔄
│   │   └── Policies/ 🔄
│   ├── database/
│   │   ├── migrations/ (3 migrações)
│   │   └── factories/ (4 factories)
│   ├── routes/
│   │   └── api.php ✅
│   ├── tests/
│   │   ├── Feature/
│   │   │   ├── Auth/ ✅
│   │   │   ├── Catalog/ ✅
│   │   │   ├── Cart/ 🔄
│   │   │   └── Order/ 🔄
│   │   └── Unit/ 🔄
│   ├── docs/
│   │   ├── AUTH_MODULE.md ✅
│   │   ├── CATALOG_MODULE.md ✅
│   │   └── API.md 🔄
│   ├── composer.json ✅
│   ├── .env.example ✅
│   ├── README.md ✅
│   └── STRUCTURE.md ✅
│
├── nextjs-frontend/                ← Frontend (Next.js 14+)
│   ├── nextjs-admin-dashboard-main/  (Admin Panel)
│   │   ├── src/app/
│   │   │   ├── (admin)/
│   │   │   │   ├── dashboard/
│   │   │   │   ├── products/
│   │   │   │   ├── orders/
│   │   │   │   └── settings/
│   │   │   └── auth/
│   │   ├── lib/
│   │   │   └── api-client.ts
│   │   └── tsconfig.json
│   │
│   └── solid-nextjs-main/           (Public Store)
│       ├── app/
│       │   ├── (shop)/
│       │   │   ├── page.tsx
│       │   │   ├── [slug]/
│       │   │   ├── search/
│       │   │   └── products/[id]/
│       │   ├── (checkout)/
│       │   │   ├── cart/
│       │   │   ├── address/
│       │   │   └── payment/
│       │   ├── (account)/
│       │   │   ├── profile/
│       │   │   ├── orders/
│       │   │   └── wishlist/
│       │   └── auth/
│       ├── lib/
│       │   └── api-client.ts
│       ├── hooks/
│       │   ├── useCart.ts
│       │   ├── useAuth.ts
│       │   └── useProducts.ts
│       ├── components/
│       │   ├── ProductCard/
│       │   ├── CartDrawer/
│       │   └── Header/
│       ├── store/
│       │   └── cartStore.ts (Zustand)
│       └── tsconfig.json
│
└── README.md                        ← Docs geral do projeto
```

---

## 🚀 Como Usar

### Backend (Laravel API)

```bash
cd laravel-api

# Setup
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate

# Run
php artisan serve

# Tests
php artisan test

# Lint
./vendor/bin/pint
```

### Frontend (Next.js)

```bash
# Admin
cd nextjs-frontend/nextjs-admin-dashboard-main
npm install
npm run dev  # http://localhost:3000/admin

# Store
cd nextjs-frontend/solid-nextjs-main
npm install
npm run dev  # http://localhost:3000
```

---

## 📊 Stack Technology

| Layer | Technology |
|-------|-----------|
| **Backend** | Laravel 11 |
| **Auth** | Laravel Sanctum |
| **Database** | MySQL 8.0+ |
| **Cache/Queue** | Redis + Horizon |
| **Search** | Meilisearch |
| **Storage** | AWS S3 (Spatie Media) |
| **Tests** | Pest PHP |
| **Frontend (Admin)** | Next.js 14 (nextjs-admin-dashboard-main) |
| **Frontend (Store)** | Next.js 14 (solid-nextjs-main) |
| **State Mgmt** | Zustand |
| **Server State** | React Query |
| **Styling** | Tailwind CSS |

---

## ✨ Features Completas

✅ Autenticação com tokens (Sanctum)
✅ Catálogo com categorias hierarchicas
✅ Produtos com variações e SKUs
✅ Busca e filtros avançados
✅ Paginação otimizada
✅ Múltiplos endereços de entrega
✅ Testes com Pest

🔄 Em progresso:
- Carrinho com Redis TTL
- Máquina de estados de pedidos
- Integração de pagamentos
- Notificações
- Admin panel

---

**Desenvolvido com ❤️ usando Laravel 11, Next.js 14 e Padrões de Design Modernos**
