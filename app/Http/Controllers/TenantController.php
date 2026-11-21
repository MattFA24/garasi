<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TenantController extends Controller
{
    /**
     * Menampilkan semua penyewa, yang paling baru mendaftar di atas.
     */
    public function index(): View
    {
        $tenants = User::where('role', 'tenant')->latest()->get();

        return view('admin.tenants', compact('tenants'));
    }

    /**
     * Admin menerima pendaftar setelah mengecek NIK secara manual.
     */
    public function approve(User $user): RedirectResponse
    {
        $user->update(['status' => 'approved']);

        return back()->with('success', "Penyewa {$user->name} diterima.");
    }

    /**
     * Admin menolak pendaftar.
     */
    public function reject(User $user): RedirectResponse
    {
        $user->update(['status' => 'rejected']);

        return back()->with('success', "Penyewa {$user->name} ditolak.");
    }
}
