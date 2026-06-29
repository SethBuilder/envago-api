<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class BlockedIpMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();

        $blocked = Cache::remember("blocked_ip:{$ip}", 300, fn() =>
            DB::table('blocked_ips')->where('ip', $ip)->exists()
        );

        if ($blocked) {
            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}
