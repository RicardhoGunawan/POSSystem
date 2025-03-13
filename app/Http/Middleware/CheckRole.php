<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if ($role === 'admin' && !auth()->user()->isAdmin()) {
            return redirect()->route('pos.index')->with('error', 'Akses ditolak. Anda bukan admin.');
        }

        if ($role === 'cashier' && !auth()->user()->isCashier() && !auth()->user()->isAdmin()) {
            return redirect()->route('login')->with('error', 'Akses ditolak.');
        }

        return $next($request);
    }
}
