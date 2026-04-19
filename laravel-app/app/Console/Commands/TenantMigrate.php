<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\TenancyService;
use Illuminate\Console\Command;

class TenantMigrate extends Command
{
    protected $signature = 'tenant:migrate
                            {--tenant= : Slug do tenant (omitir para rodar em todos)}
                            {--fresh : Dropar e recriar todas as tabelas}
                            {--seed : Rodar seeders após migrations}';

    protected $description = 'Roda migrations no(s) banco(s) de tenant(s)';

    public function __construct(private readonly TenancyService $tenancy)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $tenants = $this->option('tenant')
            ? Tenant::on('central')->where('slug', $this->option('tenant'))->get()
            : Tenant::on('central')->where('is_active', true)->get();

        if ($tenants->isEmpty()) {
            $this->warn('Nenhum tenant encontrado.');
            return self::SUCCESS;
        }

        foreach ($tenants as $tenant) {
            $this->line("→ Migrando tenant: <info>{$tenant->name}</info> ({$tenant->database})");

            $this->tenancy->initialize($tenant);

            $command = $this->option('fresh') ? 'migrate:fresh' : 'migrate';
            $options = [
                '--database' => 'tenant',
                '--path'     => config('tenancy.migration_path'),
                '--force'    => true,
            ];

            $this->call($command, $options);

            if ($this->option('seed')) {
                $this->call('db:seed', [
                    '--database' => 'tenant',
                    '--force'    => true,
                ]);
            }
        }

        $this->info('Migrations concluídas em ' . $tenants->count() . ' tenant(s).');
        return self::SUCCESS;
    }
}
