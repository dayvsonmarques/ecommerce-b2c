<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;

class TenantList extends Command
{
    protected $signature = 'tenant:list';
    protected $description = 'Lista todos os tenants cadastrados';

    public function handle(): int
    {
        $tenants = Tenant::on('central')
            ->withCount('domains')
            ->orderBy('name')
            ->get();

        if ($tenants->isEmpty()) {
            $this->warn('Nenhum tenant cadastrado.');
            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Nome', 'Slug', 'Banco', 'Plano', 'Domínios', 'Ativo'],
            $tenants->map(fn($t) => [
                $t->id,
                $t->name,
                $t->slug,
                $t->database,
                $t->plan,
                $t->domains_count,
                $t->is_active ? '✓' : '✗',
            ])
        );

        return self::SUCCESS;
    }
}
