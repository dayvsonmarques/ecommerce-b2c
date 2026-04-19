<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Branch;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class TenancyService
{
    private ?Tenant $currentTenant = null;
    private ?Branch $currentBranch = null;

    /**
     * Inicializa o tenant: troca o banco de dados da conexão 'tenant'
     * e registra o tenant no container da aplicação.
     */
    public function initialize(Tenant $tenant): void
    {
        $this->currentTenant = $tenant;

        config(['database.connections.tenant.database' => $tenant->database]);

        DB::purge('tenant');
        DB::reconnect('tenant');

        app()->instance('currentTenant', $tenant);
        app()->instance('currentBranch', null);
    }

    /**
     * Define a filial ativa para o contexto do request.
     * Quando definida, o trait BelongsToBranch aplica escopo automático.
     */
    public function setBranch(Branch $branch): void
    {
        $this->currentBranch = $branch;
        app()->instance('currentBranch', $branch);
    }

    public function currentTenant(): ?Tenant
    {
        return $this->currentTenant;
    }

    public function currentBranch(): ?Branch
    {
        return $this->currentBranch;
    }

    /**
     * Cria o banco de dados para um novo tenant.
     * Deve ser chamado apenas pelo comando tenant:create.
     */
    public function createDatabase(Tenant $tenant): void
    {
        $db = $tenant->database;

        DB::statement("CREATE DATABASE IF NOT EXISTS `{$db}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }

    /**
     * Roda as migrations do diretório migrations/tenant no banco do tenant.
     */
    public function runMigrations(Tenant $tenant): void
    {
        $this->initialize($tenant);

        \Illuminate\Support\Facades\Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path'     => config('tenancy.migration_path'),
            '--force'    => true,
        ]);
    }

    public function terminate(): void
    {
        $this->currentTenant = null;
        $this->currentBranch = null;

        app()->instance('currentTenant', null);
        app()->instance('currentBranch', null);
    }
}
