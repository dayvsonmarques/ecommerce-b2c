<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Services\TenancyService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class TenantCreate extends Command
{
    protected $signature = 'tenant:create
                            {name : Nome da loja}
                            {--slug= : Slug único (gerado automaticamente se omitido)}
                            {--domain= : Domínio customizado opcional}
                            {--plan=basic : Plano: basic|pro|enterprise}';

    protected $description = 'Cria um novo tenant: banco de dados + migrations + filial sede';

    public function __construct(private readonly TenancyService $tenancy)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $name = $this->argument('name');
        $slug = $this->option('slug') ?: Str::slug($name);
        $plan = $this->option('plan');

        if (Tenant::on('central')->where('slug', $slug)->exists()) {
            $this->error("Já existe um tenant com o slug '{$slug}'.");
            return self::FAILURE;
        }

        $database = config('tenancy.database_prefix') . $slug;

        $this->info("Criando tenant '{$name}' (slug: {$slug})...");

        $tenant = Tenant::on('central')->create([
            'name'     => $name,
            'slug'     => $slug,
            'database' => $database,
            'plan'     => $plan,
        ]);

        // Cria o banco de dados
        $this->line("  → Criando banco de dados: {$database}");
        $this->tenancy->createDatabase($tenant);

        // Roda migrations do tenant
        $this->line('  → Rodando migrations...');
        $this->tenancy->runMigrations($tenant);

        // Cria a filial sede automaticamente
        $this->line('  → Criando filial sede (HQ)...');
        \App\Models\Branch::on('tenant')->create([
            'name'             => $name . ' — Sede',
            'code'             => strtoupper(Str::substr($slug, 0, 4)) . '01',
            'is_headquarters'  => true,
            'is_active'        => true,
        ]);

        // Domínio customizado opcional
        if ($domain = $this->option('domain')) {
            $this->line("  → Mapeando domínio: {$domain}");
            TenantDomain::on('central')->create([
                'tenant_id' => $tenant->id,
                'domain'    => $domain,
            ]);
        }

        $this->newLine();
        $this->info("✓ Tenant criado com sucesso!");
        $this->table(
            ['Campo', 'Valor'],
            [
                ['ID',       $tenant->id],
                ['Nome',     $tenant->name],
                ['Slug',     $tenant->slug],
                ['Banco',    $tenant->database],
                ['Plano',    $tenant->plan],
                ['Header',   "X-Tenant: {$tenant->slug}"],
            ]
        );

        return self::SUCCESS;
    }
}
