<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VehicleController extends Controller
{
    /**
     * Menampilkan form tambah kendaraan dan daftar kendaraan milik penyewa yang login.
     */
    public function index(): View
    {
        $vehicles = Auth::user()->vehicles()->latest()->get();

        return view('tenant.vehicles', compact('vehicles'));
    }

    /**
     * Menyimpan kendaraan baru milik penyewa yang login.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:bicycle,motorcycle,car,electric_car'],
            'plate_number' => ['required_unless:type,bicycle', 'prohibited_if:type,bicycle', 'nullable', 'string', 'max:20'],
            'brand' => ['required', 'string', 'max:50'],
            'model' => ['required', 'string', 'max:50'],
            'battery_capacity_kwh' => ['required_if:type,electric_car', 'prohibited_unless:type,electric_car', 'nullable', 'numeric', 'min:1'],
        ], [
            'plate_number.required_unless' => 'Nomor plat wajib diisi, kecuali sepeda.',
            'plate_number.prohibited_if' => 'Sepeda tidak punya nomor plat, kosongkan isian ini.',
            'battery_capacity_kwh.required_if' => 'Kapasitas baterai wajib diisi untuk mobil listrik.',
            'battery_capacity_kwh.prohibited_unless' => 'Ini bukan mobil listrik, kapasitas baterai tidak perlu diisi.',
        ]);

        $request->user()->vehicles()->create([
            'type' => $data['type'],
            'plate_number' => $data['type'] === 'bicycle' ? null : $data['plate_number'],
            'brand' => $data['brand'],
            'model' => $data['model'],
            'battery_capacity_kwh' => $data['type'] === 'electric_car' ? $data['battery_capacity_kwh'] : null,
        ]);

        return back()->with('success', 'Kendaraan berhasil ditambahkan.');
    }
}
