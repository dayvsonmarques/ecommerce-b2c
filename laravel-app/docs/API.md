# API Reference — E-commerce B2C

**Base URL:** `http://localhost:8000/api/v1`  
**Formato:** JSON  
**Autenticação:** Bearer Token (Laravel Sanctum)

---

## Índice

- [Autenticação](#autenticação)
- [Endereços](#endereços)
- [Catálogo](#catálogo)
- [Carrinho](#carrinho)
- [Pedidos](#pedidos)
- [Pagamentos](#pagamentos)
- [Webhooks](#webhooks)
- [Admin — Produtos](#admin--produtos)
- [Admin — Pedidos](#admin--pedidos)
- [Admin — Usuários](#admin--usuários)
- [Admin — Relatórios](#admin--relatórios)
- [Respostas de erro](#respostas-de-erro)

---

## Autenticação

### POST `/auth/register`

Cadastra um novo usuário e retorna o token de acesso.

**Acesso:** público

**Body:**
```json
{
  "name": "João Silva",
  "email": "joao@exemplo.com",
  "password": "senha12345",
  "password_confirmation": "senha12345",
  "phone": "11999998888",
  "cpf": "123.456.789-00"
}
```

| Campo | Tipo | Regras |
|-------|------|--------|
| `name` | string | obrigatório, máx. 255 |
| `email` | string | obrigatório, único |
| `password` | string | obrigatório, mín. 8 caracteres, confirmado |
| `phone` | string | opcional, máx. 20 |
| `cpf` | string | opcional, único |

**Resposta `201`:**
```json
{
  "message": "Cadastro realizado com sucesso.",
  "user": {
    "id": 1,
    "name": "João Silva",
    "email": "joao@exemplo.com",
    "phone": "11999998888",
    "is_active": true,
    "created_at": "2026-04-14T10:00:00.000000Z"
  },
  "token": "1|abc123..."
}
```

---

### POST `/auth/login`

Autentica o usuário e retorna um token de acesso.

**Acesso:** público

**Body:**
```json
{
  "email": "joao@exemplo.com",
  "password": "senha12345"
}
```

**Resposta `200`:**
```json
{
  "message": "Login realizado com sucesso.",
  "user": { "id": 1, "name": "João Silva", "email": "joao@exemplo.com" },
  "token": "2|xyz789..."
}
```

**Erros:**
- `401` — credenciais inválidas
- `403` — conta desativada

---

### POST `/auth/logout`

Revoga o token atual.

**Acesso:** autenticado

**Resposta `200`:**
```json
{ "message": "Logout realizado com sucesso." }
```

---

### GET `/auth/profile`

Retorna os dados do usuário autenticado.

**Acesso:** autenticado

**Resposta `200`:**
```json
{
  "user": {
    "id": 1,
    "name": "João Silva",
    "email": "joao@exemplo.com",
    "phone": "11999998888",
    "cpf": "123.456.789-00",
    "is_active": true,
    "email_verified_at": "2026-04-14T10:00:00.000000Z",
    "created_at": "2026-04-14T10:00:00.000000Z"
  }
}
```

---

### PUT `/auth/profile`

Atualiza nome, e-mail, telefone e/ou senha do usuário autenticado.

**Acesso:** autenticado

**Body (todos os campos opcionais):**
```json
{
  "name": "João Atualizado",
  "email": "novo@exemplo.com",
  "phone": "11888887777",
  "current_password": "senha12345",
  "password": "novaSenha123",
  "password_confirmation": "novaSenha123"
}
```

> Para alterar a senha, `current_password` é obrigatório.

**Resposta `200`:**
```json
{
  "message": "Perfil atualizado com sucesso.",
  "user": { "id": 1, "name": "João Atualizado", "email": "novo@exemplo.com" }
}
```

---

### POST `/auth/password/forgot`

Envia o e-mail de recuperação de senha.

**Acesso:** público

**Body:**
```json
{ "email": "joao@exemplo.com" }
```

**Resposta `200`:**
```json
{ "message": "E-mail de recuperação enviado. Verifique sua caixa de entrada." }
```

---

### POST `/auth/password/reset`

Redefine a senha usando o token recebido por e-mail.

**Acesso:** público

**Body:**
```json
{
  "token": "token-do-email",
  "email": "joao@exemplo.com",
  "password": "novaSenha123",
  "password_confirmation": "novaSenha123"
}
```

**Resposta `200`:**
```json
{ "message": "Senha redefinida com sucesso." }
```

---

## Endereços

### GET `/addresses`

Lista todos os endereços do usuário autenticado.

**Acesso:** autenticado

**Resposta `200`:**
```json
{
  "data": [
    {
      "id": 1,
      "type": "shipping",
      "street": "Rua das Flores",
      "number": "123",
      "complement": "Apto 4",
      "neighborhood": "Centro",
      "city": "São Paulo",
      "state": "SP",
      "postal_code": "01310-100",
      "country": "BR",
      "is_default": true
    }
  ]
}
```

---

### POST `/addresses`

Cria um novo endereço.

**Acesso:** autenticado

**Body:**
```json
{
  "type": "shipping",
  "street": "Rua das Flores",
  "number": "123",
  "complement": "Apto 4",
  "neighborhood": "Centro",
  "city": "São Paulo",
  "state": "SP",
  "postal_code": "01310-100",
  "country": "BR",
  "is_default": true
}
```

| Campo | Tipo | Regras |
|-------|------|--------|
| `type` | string | obrigatório, `billing` ou `shipping` |
| `street` | string | obrigatório, máx. 255 |
| `number` | string | obrigatório, máx. 20 |
| `complement` | string | opcional |
| `neighborhood` | string | obrigatório |
| `city` | string | obrigatório |
| `state` | string | obrigatório, exatamente 2 caracteres (UF) |
| `postal_code` | string | obrigatório, máx. 10 |
| `country` | string | opcional, 2 caracteres (padrão `BR`) |
| `is_default` | boolean | opcional |

**Resposta `201`:**
```json
{
  "message": "Endereço criado com sucesso.",
  "data": { "id": 1, "type": "shipping", "street": "Rua das Flores", "..." }
}
```

---

### PUT `/addresses/{id}`

Atualiza um endereço existente do usuário.

**Acesso:** autenticado  
**Erro:** `404` se o endereço pertencer a outro usuário

Mesmos campos do `POST /addresses` (todos opcionais na atualização).

**Resposta `200`:**
```json
{
  "message": "Endereço atualizado com sucesso.",
  "data": { "id": 1, "..." }
}
```

---

### DELETE `/addresses/{id}`

Remove um endereço do usuário.

**Acesso:** autenticado  
**Erro:** `404` se o endereço pertencer a outro usuário

**Resposta `200`:**
```json
{ "message": "Endereço removido com sucesso." }
```

---

## Catálogo

> Todos os endpoints de catálogo são **públicos** (sem autenticação).

### GET `/categories`

Lista as categorias raiz com contagem de filhos.

**Query params:** nenhum

**Resposta `200`:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Roupas",
      "slug": "roupas",
      "description": "Moda feminina e masculina",
      "image_url": null,
      "children_count": 3
    }
  ]
}
```

---

### GET `/categories/{slug}`

Retorna uma categoria com suas subcategorias.

**Resposta `200`:**
```json
{
  "data": {
    "id": 1,
    "name": "Roupas",
    "slug": "roupas",
    "children": [
      { "id": 2, "name": "Camisetas", "slug": "camisetas" }
    ]
  }
}
```

**Erro:** `404` se a categoria estiver inativa ou não existir.

---

### GET `/products`

Lista os produtos ativos com paginação e filtros.

**Query params:**

| Parâmetro | Tipo | Descrição |
|-----------|------|-----------|
| `search` | string | Busca por nome ou descrição |
| `category_id` | integer | Filtra por categoria |
| `min_price` | numeric | Preço mínimo |
| `max_price` | numeric | Preço máximo |
| `in_stock` | boolean | Apenas produtos com estoque |
| `sort` | string | Campo de ordenação (`price`, `name`, `created_at`) |
| `direction` | string | `asc` ou `desc` (padrão: `asc`) |
| `per_page` | integer | Itens por página (padrão: 15) |

**Resposta `200`:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Camiseta Básica",
      "slug": "camiseta-basica",
      "short_description": "100% algodão",
      "price": "49.90",
      "quantity": 100,
      "is_active": true,
      "category": { "id": 2, "name": "Camisetas" }
    }
  ],
  "pagination": {
    "total": 42,
    "per_page": 15,
    "current_page": 1,
    "last_page": 3
  }
}
```

---

### GET `/products/{slug}`

Retorna os detalhes completos de um produto, incluindo variações e SKUs.

**Resposta `200`:**
```json
{
  "data": {
    "id": 1,
    "name": "Camiseta Básica",
    "slug": "camiseta-basica",
    "description": "Camiseta 100% algodão...",
    "price": "49.90",
    "quantity": 100,
    "variations": [
      {
        "id": 1,
        "name": "Tamanho",
        "values": [
          { "id": 1, "value": "P", "label": "Pequeno" },
          { "id": 2, "value": "M", "label": "Médio" }
        ]
      }
    ],
    "skus": [
      {
        "id": 1,
        "sku": "CAM-P",
        "price": "49.90",
        "quantity": 50,
        "is_active": true
      }
    ]
  }
}
```

**Erro:** `404` se o produto estiver inativo ou não existir.

---

### GET `/products/{slug}/related`

Retorna até 8 produtos relacionados da mesma categoria.

**Resposta `200`:**
```json
{
  "data": [
    { "id": 2, "name": "Camiseta Polo", "price": "79.90" }
  ]
}
```

---

## Carrinho

> Todos os endpoints de carrinho requerem **autenticação**.

### GET `/cart`

Retorna o carrinho com itens, subtotal, desconto e frete estimado.

**Resposta `200`:**
```json
{
  "data": {
    "id": 1,
    "items": [
      {
        "id": 1,
        "product_id": 1,
        "product_sku_id": null,
        "quantity": 2,
        "unit_price": "49.90",
        "subtotal": "99.80",
        "product": { "id": 1, "name": "Camiseta Básica" }
      }
    ],
    "subtotal": "99.80",
    "discount": "0.00",
    "shipping": "0.00",
    "total": "99.80",
    "coupon_code": null
  }
}
```

---

### POST `/cart/items`

Adiciona um produto ao carrinho.

**Body:**
```json
{
  "product_id": 1,
  "quantity": 2,
  "product_sku_id": null
}
```

| Campo | Tipo | Regras |
|-------|------|--------|
| `product_id` | integer | obrigatório, deve existir |
| `quantity` | integer | obrigatório, mín. 1, máx. 999 |
| `product_sku_id` | integer | opcional, deve existir em `product_skus` |

**Resposta `201`:**
```json
{
  "message": "Item adicionado ao carrinho com sucesso.",
  "data": { "id": 1, "quantity": 2, "unit_price": "49.90", "subtotal": "99.80" }
}
```

**Erro `422`:** estoque insuficiente.

---

### PUT `/cart/items/{id}`

Atualiza a quantidade de um item do carrinho.

**Body:**
```json
{ "quantity": 3 }
```

**Resposta `200`:**
```json
{
  "message": "Quantidade atualizada com sucesso.",
  "data": { "id": 1, "quantity": 3, "subtotal": "149.70" }
}
```

---

### DELETE `/cart/items/{id}`

Remove um item do carrinho.

**Resposta `200`:**
```json
{ "message": "Item removido do carrinho." }
```

---

### POST `/cart/clear`

Limpa todos os itens do carrinho.

**Resposta `200`:**
```json
{ "message": "Carrinho limpo com sucesso." }
```

---

### POST `/cart/coupon`

Aplica um cupom de desconto ao carrinho.

**Body:**
```json
{ "coupon_code": "SAVE10" }
```

**Resposta `200`:**
```json
{
  "message": "Cupom aplicado com sucesso.",
  "data": {
    "coupon_code": "SAVE10",
    "discount": "9.98",
    "total": "89.82"
  }
}
```

**Erro `422`:** cupom inválido, expirado ou sem uso disponível.

---

### POST `/cart/shipping`

Estima o custo de frete para um CEP.

**Body:**
```json
{ "postal_code": "01310100" }
```

**Resposta `200`:**
```json
{
  "data": { "shipping_cost": "15.00" }
}
```

---

## Pedidos

> Requerem **autenticação**.

### GET `/orders`

Lista os pedidos do usuário autenticado.

**Query params:** `per_page` (padrão: 15)

**Resposta `200`:**
```json
{
  "data": [
    {
      "id": 1,
      "number": "ORD-20260414-00001",
      "status": "pending",
      "total": "99.80",
      "created_at": "2026-04-14T10:00:00.000000Z"
    }
  ],
  "pagination": { "total": 5, "per_page": 15, "current_page": 1, "last_page": 1 }
}
```

---

### POST `/orders`

Cria um pedido a partir do carrinho atual.

**Body:**
```json
{
  "shipping_address_id": 1,
  "payment_method": "pix",
  "notes": "Entregar após 18h"
}
```

Para cartão de crédito:
```json
{
  "shipping_address_id": 1,
  "payment_method": "credit_card",
  "card_token": "tok_visa_test",
  "installments": 3
}
```

| Campo | Tipo | Regras |
|-------|------|--------|
| `shipping_address_id` | integer | obrigatório, deve pertencer ao usuário |
| `payment_method` | string | obrigatório, `credit_card`, `pix` ou `boleto` |
| `card_token` | string | obrigatório se `payment_method = credit_card` |
| `installments` | integer | opcional, mín. 1, máx. 12 |
| `notes` | string | opcional, máx. 500 |

**Resposta `201`:**
```json
{
  "message": "Pedido criado com sucesso.",
  "data": {
    "id": 1,
    "number": "ORD-20260414-00001",
    "status": "pending",
    "subtotal": "99.80",
    "shipping": "15.00",
    "discount": "0.00",
    "total": "114.80",
    "shipping_address": {
      "street": "Rua das Flores", "number": "123", "city": "São Paulo", "state": "SP"
    },
    "items": [
      { "product_name": "Camiseta Básica", "quantity": 2, "unit_price": "49.90" }
    ]
  }
}
```

**Erros:**
- `422` — carrinho vazio, estoque insuficiente, endereço inválido

---

### GET `/orders/{id}`

Retorna os detalhes de um pedido.

**Erro:** `404` se o pedido pertencer a outro usuário.

**Resposta `200`:**
```json
{
  "data": {
    "id": 1,
    "number": "ORD-20260414-00001",
    "status": "paid",
    "total": "114.80",
    "items": [ { "..." } ],
    "payment": { "method": "pix", "status": "paid" },
    "timeline": [
      { "status": "pending", "created_at": "2026-04-14T10:00:00.000000Z" },
      { "status": "paid",    "created_at": "2026-04-14T10:05:00.000000Z" }
    ]
  }
}
```

---

### POST `/orders/{id}/cancel`

Cancela um pedido pendente.

**Body (opcional):**
```json
{ "reason": "Comprei errado" }
```

**Resposta `200`:**
```json
{
  "message": "Pedido cancelado com sucesso.",
  "data": { "id": 1, "status": "cancelled" }
}
```

**Erro `422`:** pedido já pago, enviado ou entregue não pode ser cancelado.

---

## Pagamentos

### POST `/orders/{id}/pay`

Inicia o pagamento de um pedido.

**Acesso:** autenticado

**Body:**
```json
{ "method": "pix" }
```

Para boleto:
```json
{ "method": "boleto" }
```

Para cartão:
```json
{
  "method": "credit_card",
  "card_token": "tok_visa_test",
  "installments": 2
}
```

| Campo | Tipo | Regras |
|-------|------|--------|
| `method` | string | obrigatório, `credit_card`, `pix` ou `boleto` |
| `card_token` | string | obrigatório se `method = credit_card` |
| `installments` | integer | opcional, mín. 1, máx. 12 |

**Resposta `201` — PIX:**
```json
{
  "message": "Pagamento iniciado.",
  "data": {
    "id": 1,
    "method": "pix",
    "status": "pending",
    "pix_qr_code": "data:image/png;base64,...",
    "pix_qr_code_text": "00020126...",
    "pix_expires_at": "2026-04-14T10:30:00.000000Z"
  }
}
```

**Resposta `201` — Boleto:**
```json
{
  "data": {
    "method": "boleto",
    "status": "pending",
    "boleto_url": "https://boleto.exemplo.com/...",
    "boleto_barcode": "23793.38128 60007...",
    "boleto_expires_at": "2026-04-17T23:59:59.000000Z"
  }
}
```

**Resposta `201` — Cartão:**
```json
{
  "data": {
    "method": "credit_card",
    "status": "processing",
    "installments": 2,
    "gateway_payment_id": "pay_abc123"
  }
}
```

**Erro `422`:** pagamento duplicado ou pedido já pago.

---

## Webhooks

### POST `/webhooks/payment`

Recebe eventos do gateway de pagamento (Stripe / Pagar.me). Rota pública — a validação da assinatura é feita internamente.

**Acesso:** público

**Body (Stripe):**
```json
{
  "type": "payment_intent.succeeded",
  "data": { "object": { "id": "pi_abc123" } }
}
```

**Body (Pagar.me):**
```json
{
  "event": "transaction_status_changed",
  "transaction": { "id": "txn_abc123" }
}
```

**Resposta `200`:**
```json
{ "message": "Webhook recebido." }
```

> O processamento ocorre de forma assíncrona via `ProcessPaymentWebhookJob` (fila `webhooks`, 5 tentativas).

---

## Admin — Produtos

> Requerem **autenticação** + flag `is_admin = true`.

### GET `/admin/products`

Lista todos os produtos (ativos e inativos) com paginação.

**Query params:**

| Parâmetro | Tipo | Descrição |
|-----------|------|-----------|
| `search` | string | Busca por nome |
| `category_id` | integer | Filtra por categoria |
| `is_active` | boolean | Filtra por status |
| `low_stock` | boolean | Apenas produtos com estoque ≤ alerta mínimo |
| `per_page` | integer | Padrão: 20 |

**Resposta `200`:** igual ao `GET /products` público, mas inclui produtos inativos e campo `cost_price`.

---

### POST `/admin/products`

Cria um novo produto com variações e SKUs.

**Body:**
```json
{
  "category_id": 1,
  "name": "Camiseta Básica",
  "slug": "camiseta-basica",
  "description": "Camiseta 100% algodão",
  "short_description": "Conforto e estilo",
  "price": 49.90,
  "cost_price": 15.00,
  "sku": "CAM-BASIC",
  "quantity": 100,
  "min_quantity_alert": 10,
  "is_active": true,
  "variations": [
    {
      "name": "Tamanho",
      "values": [
        { "value": "P", "label": "Pequeno" },
        { "value": "M", "label": "Médio" }
      ]
    }
  ],
  "skus": [
    {
      "sku": "CAM-P",
      "price": 49.90,
      "quantity": 50,
      "variation_values": [1],
      "is_active": true
    }
  ]
}
```

**Resposta `201`:**
```json
{
  "message": "Produto criado com sucesso.",
  "data": { "id": 1, "name": "Camiseta Básica", "..." }
}
```

---

### GET `/admin/products/{id}`

Retorna produto com variações, SKUs e preços completos.

**Resposta `200`:**
```json
{ "data": { "id": 1, "name": "Camiseta Básica", "variations": [], "skus": [], "prices": [] } }
```

---

### PUT `/admin/products/{id}`

Atualiza um produto existente. Mesmos campos do `POST`.

**Resposta `200`:**
```json
{ "message": "Produto atualizado com sucesso.", "data": { "..." } }
```

---

### DELETE `/admin/products/{id}`

Remove um produto.

**Resposta `200`:**
```json
{ "message": "Produto removido com sucesso." }
```

---

### PATCH `/admin/products/{id}/stock`

Ajusta o estoque de um produto ou SKU específico.

**Body:**
```json
{
  "delta": 50,
  "product_sku_id": null
}
```

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `delta` | integer | obrigatório, positivo (entrada) ou negativo (saída) |
| `product_sku_id` | integer | opcional, ajusta o SKU específico |

**Resposta `200`:**
```json
{ "message": "Estoque ajustado com sucesso." }
```

---

## Admin — Pedidos

### GET `/admin/orders`

Lista todos os pedidos com filtros.

**Query params:**

| Parâmetro | Tipo | Descrição |
|-----------|------|-----------|
| `status` | string | `pending`, `paid`, `processing`, `shipped`, `delivered`, `cancelled` |
| `user_id` | integer | Filtra por usuário |
| `search` | string | Busca pelo número do pedido |
| `from` | date | Data inicial (`YYYY-MM-DD`) |
| `to` | date | Data final (`YYYY-MM-DD`) |
| `per_page` | integer | Padrão: 20 |

---

### GET `/admin/orders/{id}`

Retorna pedido completo com usuário, itens, pagamento e histórico de status.

---

### PATCH `/admin/orders/{id}/status`

Atualiza o status de um pedido.

**Body:**
```json
{
  "status": "shipped",
  "tracking_code": "BR123456789BR",
  "carrier": "Correios",
  "comment": "Enviado hoje"
}
```

| `status` | Transições permitidas |
|----------|-----------------------|
| `processing` | a partir de `paid` |
| `shipped` | a partir de `processing` |
| `delivered` | a partir de `shipped` |
| `cancelled` | a partir de `pending` ou `paid` |

**Resposta `200`:**
```json
{
  "message": "Status atualizado para 'Enviado'.",
  "data": { "id": 1, "status": "shipped", "timeline": [] }
}
```

**Erro `422`:** transição de status inválida.

---

## Admin — Usuários

### GET `/admin/users`

Lista todos os usuários com paginação.

**Query params:**

| Parâmetro | Tipo | Descrição |
|-----------|------|-----------|
| `search` | string | Busca por nome, e-mail ou CPF |
| `is_active` | boolean | Filtra por status |
| `per_page` | integer | Padrão: 20 |

---

### GET `/admin/users/{id}`

Retorna usuário com endereços cadastrados.

**Resposta `200`:**
```json
{
  "data": {
    "id": 1,
    "name": "João Silva",
    "email": "joao@exemplo.com",
    "is_active": true,
    "is_admin": false,
    "addresses": [ { "..." } ]
  }
}
```

---

### PATCH `/admin/users/{id}/toggle-active`

Ativa ou desativa uma conta de usuário.

**Resposta `200`:**
```json
{
  "message": "Usuário desativado com sucesso.",
  "data": { "id": 1, "is_active": false }
}
```

---

## Admin — Relatórios

### GET `/admin/reports/summary`

Resumo de pedidos e receita em um período.

**Query params:**

| Parâmetro | Tipo | Descrição |
|-----------|------|-----------|
| `from` | date | Data inicial (padrão: início do mês atual) |
| `to` | date | Data final (padrão: hoje) |

**Resposta `200`:**
```json
{
  "data": {
    "period": { "from": "2026-04-01", "to": "2026-04-14" },
    "total_orders": 38,
    "total_revenue": 4521.30,
    "average_order_value": 122.74,
    "orders_by_status": {
      "pending":   5,
      "paid":      12,
      "shipped":   10,
      "delivered": 8,
      "cancelled": 3
    }
  }
}
```

---

## Respostas de erro

### Estrutura padrão

**Erro de validação `422`:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["Este email já está registrado."],
    "password": ["A senha deve ter no mínimo 8 caracteres."]
  }
}
```

**Erro de autenticação `401`:**
```json
{ "message": "Unauthenticated." }
```

**Erro de autorização `403`:**
```json
{ "message": "Sua conta está desativada. Entre em contato com o suporte." }
```

**Recurso não encontrado `404`:**
```json
{ "message": "Recurso não encontrado." }
```

**Erro de negócio `422`:**
```json
{ "message": "Estoque insuficiente para o produto solicitado." }
```

---

## Headers obrigatórios

```http
Content-Type: application/json
Accept: application/json
Authorization: Bearer {token}   ← apenas rotas autenticadas
```

---

## Fluxo típico de compra

```
1. POST /auth/login                      → obtém token
2. GET  /products                        → navega o catálogo
3. POST /cart/items                      → adiciona itens
4. POST /cart/coupon         (opcional)  → aplica cupom
5. POST /cart/shipping       (opcional)  → estima frete
6. GET  /addresses                       → escolhe endereço
7. POST /orders                          → cria pedido
8. POST /orders/{id}/pay                 → inicia pagamento
9. GET  /orders/{id}                     → acompanha status
```
