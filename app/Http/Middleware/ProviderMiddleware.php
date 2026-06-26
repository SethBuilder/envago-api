<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ProviderMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (! auth()->check() || ! auth()->user()->isProvider()) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
