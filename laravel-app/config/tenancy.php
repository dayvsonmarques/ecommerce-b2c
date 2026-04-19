<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Central Database Connection
    |--------------------------------------------------------------------------
    | Conexão usada para ler/gravar registros de Tenant. Nunca é trocada.
    */
    'central_connection' => env('TENANCY_CENTRAL_CONNECTION', 'central'),

    /*
    |--------------------------------------------------------------------------
    | Tenant Database Connection
    |--------------------------------------------------------------------------
    | Conexão cujo `database` é trocado dinamicamente pelo TenancyService
    | a cada request após a resolução do tenant.
    */
    'tenant_connection' => env('TENANCY_TENANT_CONNECTION', 'tenant'),

    /*
    |--------------------------------------------------------------------------
    | Tenant Resolution
    |--------------------------------------------------------------------------
    | header  → X-Tenant: {slug}           (APIs e desenvolvimento)
    | subdomain → {slug}.api.example.com   (produção)
    |
    | A ordem define a prioridade: primeiro header, depois subdomínio.
    */
    'resolution' => [
        'header'    => env('TENANCY_HEADER', 'X-Tenant'),
        'subdomain' => env('TENANCY_SUBDOMAIN_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant Database Prefix
    |--------------------------------------------------------------------------
    | Prefixo usado ao criar bancos de dados para novos tenants.
    | Ex: tenant_{slug} → tenant_loja_abc
    */
    'database_prefix' => env('TENANCY_DB_PREFIX', 'tenant_'),

    /*
    |--------------------------------------------------------------------------
    | Tenant Migrations Path
    |--------------------------------------------------------------------------
    | Caminho das migrations que rodam dentro do banco de cada tenant.
    */
    'migration_path' => database_path('migrations/tenant'),

    /*
    |--------------------------------------------------------------------------
    | Central Migrations Path
    |--------------------------------------------------------------------------
    | Caminho das migrations do banco central (tenants, domains).
    */
    'central_migration_path' => database_path('migrations/central'),

];
