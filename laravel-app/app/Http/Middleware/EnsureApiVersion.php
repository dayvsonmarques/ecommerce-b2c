<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiVersion
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->attributes->set('api_version', 'v1');

        return $next($request)
            ->header('X-API-Version', 'v1')
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('X-Frame-Options', 'DENY')
            ->header('X-XSS-Protection', '1; mode=block');
    }
}
