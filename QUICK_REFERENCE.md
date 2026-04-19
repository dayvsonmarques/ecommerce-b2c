# 🚀 E-commerce B2C - Quick Reference

## 📋 Começar Rápido

### Setup Inicial
```bash
# Backend
cd laravel-api
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve

# Tests
php artisan test

# Linting
./vendor/bin/pint
```

---

## 🔌 API Endpoints

### Auth (Públicos)
```bash
# Registrar usuário
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "João Silva",
    "email": "joao@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "11999999999",
    "cpf": "123.456.789-00"
  }'

# Login
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "joao@example.com",
    "password": "password123"
  }'

# Response
{
  "message": "Login realizado com sucesso.",
  "user": {
    "id": 1,
    "name": "João Silva",
    "email": "joao@example.com",
    "phone": "11999999999",
    "email_verified_at": null,
    "created_at": "2026-04-12T10:00:00.000000Z"
  },
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

### Auth (Protegidos - requer token)
```bash
# Perfil
curl -X GET http://localhost:8000/api/v1/auth/profile \
  -H "Authorization: Bearer {token}"

# Atualizar perfil
curl -X PUT http://localhost:8000/api/v1/auth/profile \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "João Silva Atualizado",
    "phone": "11988888888"
  }'

# Logout
curl -X POST http://localhost:8000/api/v1/auth/logout \
  -H "Authorization: Bearer {token}"
```

### Addresses (Protegidos)
```bash
# Listar endereços
curl -X GET http://localhost:8000/api/v1/addresses \
  -H "Authorization: Bearer {token}"

# Criar endereço
curl -X POST http://localhost:8000/api/v1/addresses \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "billing",
    "street": "Rua Principal",
    "number": "123",
    "neighborhood": "Centro",
    "city": "São Paulo",
    "state": "SP",
    "postal_code": "01234-567",
    "is_default": true
  }'

# Atualizar
curl -X PUT http://localhost:8000/api/v1/addresses/1 \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{ "street": "Rua Atualizada" }'

# Deletar
curl -X DELETE http://localhost:8000/api/v1/addresses/1 \
  -H "Authorization: Bearer {token}"
```

### Catalog (Públicos)
```bash
# Listar categorias
curl http://localhost:8000/api/v1/categories

# Detalhes categoria
curl http://localhost:8000/api/v1/categories/1

# Listar produtos
curl "http://localhost:8000/api/v1/products"
curl "http://localhost:8000/api/v1/products?category_id=1"
curl "http://localhost:8000/api/v1/products?search=camiseta"
curl "http://localhost:8000/api/v1/products?min_price=100&max_price=500"
curl "http://localhost:8000/api/v1/products?sort=price&direction=asc"
curl "http://localhost:8000/api/v1/products?per_page=30&page=2"

# Detalhes produto
curl http://localhost:8000/api/v1/products/1

# Produtos relacionados
curl "http://localhost:8000/api/v1/products/1/related?limit=5"
```

---

## 📝 Estrutura de Respostas

### Sucesso (200/201)
```json
{
  "data": { ... },
  "message": "Operação realizada com sucesso",
  "token": "eyJhbGc..." (quando aplicável)
}
```

### Erro (4xx/5xx)
```json
{
  "message": "Descrição do erro",
  "errors": {
    "email": ["O email já está registrado"]
  }
}
```

### Lista com Paginação
```json
{
  "data": [ ... ],
  "pagination": {
    "total": 100,
    "per_page": 15,
    "current_page": 1,
    "last_page": 7
  }
}
```

---

## 🗂️ Estrutura de Models

### User
```php
User
├── id
├── name
├── email (unique)
├── password
├── phone
├── cpf (unique)
├── is_active: bool
├── email_verified_at: datetime
├── timestamps
└── relationships
    ├── addresses() HasMany
    ├── orders() HasMany
    ├── cart() HasOne
    └── wishlist() HasMany
```

### Product
```php
Product
├── id
├── category_id (FK)
├── name
├── slug (unique)
├── description
├── short_description
├── price: decimal
├── cost_price: decimal
├── sku (unique)
├── quantity: int
├── min_quantity_alert: int
├── is_active: bool
├── timestamps
└── relationships
    ├── category() BelongsTo
    ├── variations() HasMany
    ├── skus() HasMany
    ├── prices() MorphMany
    └── images() HasMany (Spatie Media)
```

### ProductSku
```php
ProductSku
├── id
├── product_id (FK)
├── sku (unique)
├── price: decimal (nullable - usa price do product)
├── quantity: int
├── variation_values: json { variation_id: value_id }
├── is_active: bool
├── timestamps
└── relationships
    ├── product() BelongsTo
    └── prices() MorphMany
```

---

## 🧪 Executar Testes

```bash
# Todos os testes
php artisan test

# Testes específicos
php artisan test tests/Feature/Auth/RegisterTest.php
php artisan test tests/Feature/Auth/LoginTest.php
php artisan test tests/Feature/Catalog/CatalogTest.php

# Com coverage
php artisan test --coverage

# Apenas falhas
php artisan test --failure-first
```

---

## 📊 Factories & Seeders

### Criar dados de teste
```php
// No tinker ou seeder
use App\Models\User;
use App\Models\Category;
use App\Models\Product;

// 10 usuários
User::factory(10)->create();

// 5 categorias com 10 produtos cada
Category::factory(5)->create()->each(function ($category) {
    Product::factory(10)->create(['category_id' => $category->id]);
});
```

---

## 🔑 Autenticação com Tokens

### Obter token (Login)
```javascript
const response = await fetch('/api/v1/auth/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    email: 'user@example.com',
    password: 'password123'
  })
});

const { token } = await response.json();
localStorage.setItem('auth_token', token);
```

### Usar token em requisições
```javascript
const token = localStorage.getItem('auth_token');

fetch('/api/v1/auth/profile', {
  headers: { 
    'Authorization': `Bearer ${token}`
  }
});
```

### Logout (revoga token)
```javascript
await fetch('/api/v1/auth/logout', {
  method: 'POST',
  headers: { 
    'Authorization': `Bearer ${token}`
  }
});

localStorage.removeItem('auth_token');
```

---

## 📈 Filtros & Query Parameters

### Products
| Param | Tipo | Exemplo |
|-------|------|---------|
| `category_id` | int | `?category_id=1` |
| `search` | string | `?search=camiseta` |
| `min_price` | decimal | `?min_price=100` |
| `max_price` | decimal | `?max_price=500` |
| `sort` | string | `?sort=price` (default: created_at) |
| `direction` | string | `?direction=asc` (default: desc) |
| `per_page` | int | `?per_page=30` (default: 15) |
| `page` | int | `?page=2` |

### Exemplo completo
```bash
curl "http://localhost:8000/api/v1/products?search=camiseta&category_id=1&min_price=50&max_price=200&sort=price&direction=asc&per_page=20&page=1"
```

---

## 🛠️ Desenvolvimento

### Adicionar novo endpoint

1. **Controller**
```php
// app/Http/Controllers/Api/V1/ProductController.php
public function index(Request $request): JsonResponse
{
    // ...logic
    return response()->json(['data' => $data], 200);
}
```

2. **Route**
```php
// routes/api.php
Route::get('products', [ProductController::class, 'index']);
```

3. **Test**
```php
// tests/Feature/ProductTest.php
public function test_can_list_products()
{
    $response = $this->getJson('api/v1/products');
    $response->assertStatus(200);
}
```

### Adicionar novo Model
```php
php artisan make:model ModelName -m  // com migration
```

### Adicionar novo Service
```php
// app/Services/MyService.php
class MyService {
    public function myMethod() {}
}
```

---

## 📚 Documentação Completa

- [README.md](laravel-api/README.md) - Setup
- [ARCHITECTURE.md](ARCHITECTURE.md) - Visão geral
- [STRUCTURE.md](laravel-api/STRUCTURE.md) - Estrutura
- [AUTH_MODULE.md](laravel-api/docs/AUTH_MODULE.md) - Auth
- [CATALOG_MODULE.md](laravel-api/docs/CATALOG_MODULE.md) - Catalog
- [EXECUTIVE_SUMMARY.md](EXECUTIVE_SUMMARY.md) - Resumo

---

## 🔗 Links Úteis

- [Laravel 11 Docs](https://laravel.com/docs/11.x)
- [Laravel Sanctum](https://laravel.com/docs/11.x/sanctum)
- [Pest PHP](https://pestphp.com)
- [Eloquent ORM](https://laravel.com/docs/11.x/eloquent)

---

## ❓ Troubleshooting

### "SQLSTATE[HY000]: General error: 1030"
```bash
php artisan migrate:fresh
```

### Token inválido
- Verifique se está enviando com `Authorization: Bearer {token}`
- Logout e faça login novamente

### Erro de validação
```json
{
  "message": "The given data was invalid",
  "errors": {
    "email": ["The email has already been taken"]
  }
}
```

---

**Última atualização**: 12 de Abril de 2026
