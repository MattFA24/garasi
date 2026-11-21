<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApproved
{
    /**
 * Penyewa harus sudah disetujui admin sebelum boleh lanjut.
 */
public function handle(Request $request, Closure $next): Response
{
    if ($request->user()->status !== 'approved') {
        abort(403, 'Akun kamu belum disetujui admin.');
    }

    return $next($request);
}
}
