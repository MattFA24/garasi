<?php

namespace App\Http\Controllers;

use App\Models\Garage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GarageController extends Controller
{
    /**
     * Menampilkan form buat garasi dan daftar semua garasi.
     */
    public function index(): View
    {
        $garages = Garage::orderBy('code')->get();

        return view('admin.garages', compact('garages'));
    }

    /**
     * Membuat beberapa garasi sekaligus dengan kode otomatis.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:bike,car,electric'],
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'daily_price' => ['required', 'integer', 'min:1'],
            'price_per_kwh' => ['required_if:type,electric', 'nullable', 'integer', 'min:1'],
        ]);

        // Nomor terakhir untuk tipe ini. Aman karena tidak ada fitur hapus garasi.
        $last = Garage::where('type', $data['type'])->count();

        if ($last + $data['quantity'] > 999) {
            return back()
                ->withErrors(['quantity' => 'Nomor garasi untuk tipe ini maksimal 999.'])
                ->withInput();
        }

        for ($i = 1; $i <= $data['quantity']; $i++) {
            Garage::create([
                'code' => Garage::makeCode($data['type'], $last + $i),
                'type' => $data['type'],
                'daily_price' => $data['daily_price'],
                'price_per_kwh' => $data['type'] === 'electric' ? $data['price_per_kwh'] : null,
            ]);
        }

        return back()->with('success', "{$data['quantity']} garasi berhasil dibuat.");
    }
}
