<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        '*webhook*',
        '*bank-email*',
        '*callback*',
        '*discount-codes*',
        'api/*',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle($request, \Closure $next)
    {
        if ($request->is('api/*') || $request->is('*webhook*') || $request->is('*bank-email*') || $request->is('*callback*')) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }
}
