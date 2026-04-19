# 🎯 E-commerce B2C — Resumo Executivo

**Atualizado em**: 19 de Abril de 2026
**Stack**: Laravel 12 + Next.js 14 + Tailwind CSS
**Arquitetura**: Monolito Laravel API headless + Multi-tenancy (banco por loja)

---

## 📈 Status do Projeto

### Progresso: ~60% (6/8 módulos com implementação substancial)

| Módulo | Status | Progress | Endpoints |
|--------|--------|----------|-----------|
| Auth | ✅ Completo | 100% | 9 endpoints |
| Catalog | ✅ Completo | 100% | 5 endpoints |
| Cart | ✅ Substancial | ~90% | 7 endpoints |
| Orders | ✅ Substancial | ~85% | 5 endpoints |
| Payments | 🔄 Parcial | ~60% | 2 endpoints |
| Notifications | 🔄 Parcial | ~70% | — |
| Stock | 🔄 Parcial | ~50% | 1 endpoint (admin) |
| Admin | ✅ Substancial | ~80% | 13 endpoints |
| **Multi-tenancy** | ✅ **Novo** | 100% | infraestrutura |

---

## 🏗️ Arquitetura Multi-tenancy

**Implementado em 19/04/2026** — cada loja tem banco de dados isolado.

```
ecommerce_central           ← banco fixo (landlord)
├── tenants                 (registro das lojas)
└── tenant_domains          (domínios customizados)

tenant_{slug}               ← banco por loja (isolado)
├── branches                (filiais da loja)
├── orders    (branch_id)   ← pedido por filial
├── carts     (branch_id)   ← carrinho por filial
├── product_skus (branch_id)← estoque por filial
└── coupons   (branch_id?)  ← cupom global ou por filial
```

**Resolução de tenant**: header `X-Tenant: {slug}` ou subdomínio `{slug}.api.dominio.com`

**Comandos Artisan**:
```bash
php artisan tenant:create "Nome da Loja" --domain=loja.com
php artisan tenant:migrate          # roda em todos os tenants
php artisan tenant:list
```

---

## 🔐 Módulo 1: Auth ✅

**Endpoints**: 9 | **Tests**: 15+ casos

- ✅ Registro com email único e CPF
- ✅ Login com token Sanctum
- ✅ Atualização de perfil + troca de senha
- ✅ Logout com revogação de token
- ✅ Recuperação de senha (forgot/reset)
- ✅ Múltiplos endereços (billing/shipping)
- ✅ Busca de CEP (ViaCEP)

```
POST   /api/v1/auth/register
POST   /api/v1/auth/login
POST   /api/v1/auth/password/forgot
POST   /api/v1/auth/password/reset
GET    /api/v1/auth/profile
PUT    /api/v1/auth/profile
POST   /api/v1/auth/logout
GET|POST|PUT|DELETE  /api/v1/addresses
```

---

## 🛍️ Módulo 2: Catalog ✅

**Endpoints**: 5 | **Tests**: 12+ casos

- ✅ Categorias hierárquicas
- ✅ Produtos com variações (tamanho, cor) e SKUs
- ✅ Preços com promoção (sale_price + datas)
- ✅ Busca textual, filtros, paginação, ordenação
- ✅ Produtos relacionados

```
GET  /api/v1/categories
GET  /api/v1/categories/{id}
GET  /api/v1/products
GET  /api/v1/products/{id}
GET  /api/v1/products/{id}/related
```

---

## 🛒 Módulo 3: Cart ✅ ~90%

**Endpoints**: 7 | **Tests**: 9 casos

- ✅ Adicionar/atualizar/remover items
- ✅ Limpar carrinho
- ✅ Aplicar cupom de desconto
- ✅ Estimativa de frete
- ⚠️ Integração real Melhor Envio pendente
- ⚠️ Reserva de estoque com TTL (Redis) pendente

```
GET|POST        /api/v1/cart
POST|PUT|DELETE /api/v1/cart/items/{id}
POST            /api/v1/cart/clear
POST            /api/v1/cart/coupon
POST            /api/v1/cart/shipping
```

---

## 📦 Módulo 4: Orders ✅ ~85%

**Endpoints**: 5 | **Tests**: 8 casos

- ✅ Criação de pedido a partir do carrinho
- ✅ Máquina de estados (OrderStatus enum)
- ✅ Timeline de status com audit trail
- ✅ Cancelamento de pedido
- ✅ Events: OrderCreated, OrderShipped, OrderDelivered, OrderCancelled
- ⚠️ NF-e pendente

```
GET|POST        /api/v1/orders
GET             /api/v1/orders/{id}
POST            /api/v1/orders/{id}/cancel
POST            /api/v1/orders/{id}/pay
```

---

## 💳 Módulo 5: Payments 🔄 ~60%

**Endpoints**: 2 | **Tests**: 6 casos

- ✅ Estrutura de Payment + PaymentService
- ✅ Webhook handler com job assíncrono
- ⚠️ Integração Stripe/Pagar.me não conectada
- ⚠️ PIX, cartão, boleto pendentes

```
POST  /api/v1/orders/{id}/pay
POST  /api/v1/webhooks/payment
```

---

## 🔔 Módulo 6: Notifications 🔄 ~70%

- ✅ 4 classes de notificação (OrderConfirmed, Shipped, Delivered, LowStock)
- ✅ 4 listeners registrados no AppServiceProvider
- ✅ Tabela de notificações (Laravel Notifications)
- ⚠️ Mailables/templates de email não configurados
- ⚠️ Push (Firebase) e SMS pendentes

---

## 📊 Módulo 7: Admin ✅ ~80%

**Endpoints**: 13

- ✅ CRUD completo de produtos (+ ajuste de estoque)
- ✅ Gestão de pedidos (listagem, detalhe, atualização de status)
- ✅ Gestão de usuários (listagem, detalhe, ativar/desativar)
- ✅ Relatório de resumo
- ⚠️ Integração com nextjs-admin-dashboard pendente

```
apiResource  /api/v1/admin/products
PATCH        /api/v1/admin/products/{id}/stock
GET|PATCH    /api/v1/admin/orders
GET|PATCH    /api/v1/admin/users
GET          /api/v1/admin/reports/summary
```

---

## 📦 Módulo 8: Stock 🔄 ~50%

- ✅ StockService implementado
- ✅ RestoreStockOnCancellation listener
- ✅ LowStockNotification
- ⚠️ Reserva por SKU com TTL Redis pendente
- ⚠️ Alertas automáticos pendentes

---

## 📊 Métricas do Código

| Métrica | Valor |
|---------|-------|
| Controllers | 10 |
| Models | 18 (+ Tenant, TenantDomain, Branch) |
| Services | 7 (+ TenancyService) |
| Repositories | 3 |
| Migrations | 17 (13 app + 2 central + 2 tenant) |
| Requests (Form) | 13 |
| Resources (JSON) | 11 |
| Events | 5 |
| Listeners | 4 |
| Notifications | 4 |
| Artisan Commands | 3 (tenant:create, tenant:migrate, tenant:list) |
| Testes (Pest) | 61+ |
| Endpoints totais | 45+ |
| Linhas de código | ~5.000+ |

---

## 🧪 Cobertura de Testes

| Suite | Testes | Status |
|-------|--------|--------|
| Auth (Register, Login, Profile, Address) | 23 | ✅ |
| Catalog | 12 | ✅ |
| Cart | 9 | ✅ |
| Orders | 8 | ✅ |
| Payments | 6 | ✅ |
| **Total** | **61+** | |

```bash
php artisan test
```

---

## 🗄️ Banco de Dados

**17 migrations** organizadas em 3 grupos:

```
database/migrations/          ← tabelas da aplicação (13)
database/migrations/central/  ← landlord: tenants, domains (2)
database/migrations/tenant/   ← por loja: branches, branch_id (2)
```

**Rodar banco central:**
```bash
php artisan migrate --path=database/migrations/central --database=central
```

---

## 🏗️ Padrões de Design

| Padrão | Implementação |
|--------|---------------|
| Service Layer | AuthService, CartService, OrderService, PaymentService, ProductService, StockService, TenancyService |
| Repository Pattern | UserRepository, AddressRepository, CartRepository |
| Resource Pattern | 11 API Resources para respostas JSON consistentes |
| Form Requests | 13 classes de validação centralizadas |
| Global Scopes | BelongsToBranch (isolamento automático por filial) |
| Event-Driven | 5 events + 4 listeners |
| Queue Jobs | ProcessPaymentWebhookJob |
| Eloquent Scopes | active(), inStock(), search() |

---

## 🌐 Frontends

| Projeto | Pasta | Framework | Status |
|---------|-------|-----------|--------|
| Admin Dashboard | `admin/` | Next.js 14 (SSR) | 🔄 Em desenvolvimento |
| Loja Pública | `storefront/` | Next.js 14 (SSG/ISR) | 🔄 Em desenvolvimento |

---

## 🚀 Servidor de Desenvolvimento

```bash
cd laravel-app
php artisan serve --port=8001

# API disponível em:
http://localhost:8001/api/v1/
```

---

## 🔄 Próximas Prioridades

1. **Integração de Pagamento** — conectar Stripe ou Pagar.me ao PaymentService
2. **Email Templates** — configurar Mailables para notificações de pedido
3. **Reserva de Estoque** — implementar TTL no Redis no CartService
4. **Integrações MySQL** — configurar banco central para multi-tenancy em produção
5. **Frontend Admin** — conectar `admin/` Next.js aos endpoints `/api/v1/admin/*`
6. **Frontend Storefront** — conectar `storefront/` aos endpoints públicos

---

## 💰 Benefícios Arquiteturais

✅ **Multi-tenancy** — banco isolado por loja, filiais via branch_id
✅ **Escalabilidade** — Service + Repository layers isolam lógica
✅ **Testabilidade** — 61+ testes com Pest
✅ **Manutenibilidade** — PSR-12, type hints 100%, strict_types
✅ **Event-Driven** — ações desacopladas via Events/Listeners
✅ **Segurança** — Sanctum, Form Requests, EnsureAdmin middleware

---

*Atualizado em 19 de Abril de 2026*
