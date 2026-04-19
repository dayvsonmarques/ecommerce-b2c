<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Branch;
use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Services\TenancyService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancy
{
    public function __construct(private readonly TenancyService $tenancy) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->resolveTenant($request);

        if ($tenant === null) {
            return response()->json(['message' => 'Tenant não identificado.'], 400);
        }

        if (! $tenant->is_active) {
            return response()->json(['message' => 'Tenant inativo ou suspenso.'], 403);
        }

        $this->tenancy->initialize($tenant);

        $this->resolveBranch($request);

        return $next($request);
    }

    private function resolveTenant(Request $request): ?Tenant
    {
        $headerName = config('tenancy.resolution.header', 'X-Tenant');

        // 1. Header X-Tenant: {slug}
        if ($slug = $request->header($headerName)) {
            return Tenant::on('central')
                ->where('slug', $slug)
                ->first();
        }

        // 2. Subdomínio: {slug}.api.example.com
        if (config('tenancy.resolution.subdomain')) {
            $host = $request->getHost();
            $parts = explode('.', $host);

            if (count($parts) >= 3) {
                $slug = $parts[0];
                return Tenant::on('central')
                    ->where('slug', $slug)
                    ->first();
            }

            // 3. Domínio customizado mapeado na tabela tenant_domains
            $domain = TenantDomain::on('central')
                ->where('domain', $host)
                ->with('tenant')
                ->first();

            return $domain?->tenant;
        }

        return null;
    }

    /**
     * Resolve a filial a partir do header X-Branch-ID (opcional).
     * Se não informado, o contexto fica sem filial ativa (tenant-wide).
     */
    private function resolveBranch(Request $request): void
    {
        $branchId = $request->header('X-Branch-ID');

        if ($branchId === null) {
            return;
        }

        $branch = Branch::on('tenant')->find((int) $branchId);

        if ($branch && $branch->is_active) {
            $this->tenancy->setBranch($branch);
        }
    }
}
