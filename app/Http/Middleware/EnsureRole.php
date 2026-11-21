<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
 * Hanya user dengan role tertentu yang boleh lanjut.
 * Dipakai di route seperti ini: middleware('role:admin')
 */
public function handle(Request $request, Closure $next, string $role): Response
{
    if ($request->user()->role !== $role) {
        abort(403, 'Kamu tidak punya akses ke halaman ini.');
    }

    return $next($request);
}
}
