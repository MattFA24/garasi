<?php

namespace App\Http\Controllers;

use App\Models\Garage;
use App\Models\Rental;
use App\Services\RentalCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RentalController extends Controller
{
    /**
     * RentalCalculator diberikan otomatis oleh Laravel (dependency injection).
     */
    public function __construct(private RentalCalculator $calculator) {}

    /**
     * Form sewa garasi baru. Kalau penyewa sudah menekan "Cari Garasi",
     * tampilkan garasi yang tersedia beserta biayanya.
     */
    public function create(Request $request): View
    {
        $search = null;

        if ($request->filled('vehicle_id')) {
            $search = $this->prepare($request);
            $search['options'] = [];

            $garages = Garage::whereIn('type', $search['vehicle']->allowedGarageTypes())
                ->orderBy('code')
                ->get();

            foreach ($garages as $garage) {
                if ($garage->isFreeBetween($search['start'], $search['end'])) {
                    $search['options'][] = ['garage' => $garage] + $this->cost($garage, $search);
                }
            }
        }

        return view('tenant.rent', [
            'vehicles' => $request->user()->vehicles()->get(),
            'search' => $search,
        ]);
    }

    /**
     * Menyimpan sewa garasi baru. Baris garasi dikunci supaya
     * satu garasi tidak bisa disewa dua orang di tanggal yang sama (BR-08).
     */
    public function store(Request $request): RedirectResponse
    {
        $search = $this->prepare($request);

        $request->validate([
            'garage_id' => ['required', 'integer'],
            'payment_proof' => ['required', 'image', 'max:2048'],
        ]);

        $rental = DB::transaction(function () use ($request, $search) {
            // SELECT ... FOR UPDATE: baris garasi dikunci sampai transaksi selesai
            $garage = Garage::whereKey($request->garage_id)->lockForUpdate()->firstOrFail();

            if (! in_array($garage->type, $search['vehicle']->allowedGarageTypes())) {
                throw ValidationException::withMessages(['garage_id' => 'Garasi tidak sesuai dengan jenis kendaraan.']);
            }

            if (! $garage->isFreeBetween($search['start'], $search['end'])) {
                throw ValidationException::withMessages(['garage_id' => 'Garasi sudah disewa di tanggal itu. Silakan pilih garasi lain.']);
            }

            // Biaya dihitung ulang di server, tidak memakai angka dari browser
            $cost = $this->cost($garage, $search);

            $rental = Rental::create([
                'user_id' => $request->user()->id,
                'vehicle_id' => $search['vehicle']->id,
                'garage_id' => $garage->id,
                'rental_type' => $search['rental_type'],
                'days' => $search['days'],
                'start_date' => $search['start'],
                'end_date' => $search['end'],
                'battery_remaining_kwh' => $search['battery_remaining_kwh'],
                'rent_cost' => $cost['rent'],
                'charging_cost' => $cost['charging'],
                'total_cost' => $cost['total'],
                'payment_proof_path' => $request->file('payment_proof')->store('payment-proofs', 'public'),
            ]);

            $garage->update(['status' => 'used']);

            return $rental;
        });

        return redirect()->route('rent.create')->with(
            'success',
            "Sewa garasi {$rental->garage->code} berhasil disimpan. "
                .$rental->start_date->format('d-m-Y').' sampai '.$rental->end_date->format('d-m-Y')
                .', total Rp '.number_format($rental->total_cost, 0, ',', '.').'.'
        );
    }

    /**
     * Validasi input sewa, lalu hitung jumlah hari dan tanggal selesai.
     * Dipakai saat mencari garasi dan saat menyimpan.
     */
    private function prepare(Request $request): array
    {
        $data = $request->validate([
            'vehicle_id' => ['required', 'integer'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'rental_type' => ['required', 'in:daily,monthly'],
            'days' => ['required_if:rental_type,daily', 'nullable', 'integer', 'min:1'],
            'battery_remaining_kwh' => ['nullable', 'numeric', 'min:0'],
        ]);

        $start = Carbon::parse($data['start_date']);

        // Batas sewa harian tergantung bulan tanggal mulai (BR-03)
        $maxDays = $this->calculator->maxDailyDays($start);

        if ($data['rental_type'] === 'daily' && $data['days'] > $maxDays) {
            throw ValidationException::withMessages([
                'days' => "Sewa harian di bulan ini maksimal {$maxDays} hari. Pilih sewa bulanan, atau sewa dua kali.",
            ]);
        }

        // Hanya kendaraan milik penyewa yang login. Kalau bukan miliknya, 404.
        $vehicle = $request->user()->vehicles()->findOrFail($data['vehicle_id']);

        $remaining = $data['battery_remaining_kwh'] ?? null;

        // Kendaraan biasa tidak boleh mengisi sisa baterai
        if ($vehicle->type !== 'electric_car' && $remaining !== null) {
            throw ValidationException::withMessages([
                'battery_remaining_kwh' => 'Kendaraan ini bukan mobil listrik, sisa baterai tidak perlu diisi.',
            ]);
        }

        // Mobil listrik wajib mengisi sisa baterai, maksimal sebesar kapasitasnya
        if ($vehicle->type === 'electric_car' && ($remaining === null || $remaining > $vehicle->battery_capacity_kwh)) {
            throw ValidationException::withMessages([
                'battery_remaining_kwh' => "Isi sisa baterai antara 0 sampai {$vehicle->battery_capacity_kwh} kWh.",
            ]);
        }

        $days = $data['rental_type'] === 'monthly'
            ? $this->calculator->monthLength($start)
            : (int) $data['days'];

        return [
            'vehicle' => $vehicle,
            'start' => $start,
            'rental_type' => $data['rental_type'],
            'days' => $days,
            'end' => $this->calculator->endDate($start, $days),
            'battery_remaining_kwh' => $remaining,
        ];
    }

    /**
     * Biaya sewa, biaya listrik, dan total untuk satu garasi.
     * Listrik hanya dihitung kalau parkir di garasi mobil listrik (BR-07).
     */
    private function cost(Garage $garage, array $search): array
    {
        $rent = $this->calculator->rentCost($search['rental_type'], $garage->daily_price, $search['days']);

        $charging = 0;

        if ($garage->type === 'electric') {
            $charging = $this->calculator->chargingCost(
                $search['vehicle']->battery_capacity_kwh,
                $search['battery_remaining_kwh'],
                $garage->price_per_kwh,
            );
        }

        return ['rent' => $rent, 'charging' => $charging, 'total' => $rent + $charging];
    }
}
