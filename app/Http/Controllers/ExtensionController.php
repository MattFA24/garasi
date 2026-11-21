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

class ExtensionController extends Controller
{
    /**
     * RentalCalculator diberikan otomatis oleh Laravel (dependency injection).
     */
    public function __construct(private RentalCalculator $calculator) {}

    /**
     * Daftar sewa yang aktif hari ini. Kalau admin sudah memilih sewa
     * dan mengisi tanggal selesai baru, tampilkan kekurangan biayanya.
     */
    public function index(Request $request): View
    {
        $rentals = Rental::with(['tenant', 'vehicle', 'garage'])
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->orderBy('end_date')
            ->get();

        $selected = null;
        $preview = null;

        if ($request->filled('rental_id')) {
            $selected = Rental::with(['tenant', 'vehicle', 'garage'])->findOrFail($request->rental_id);
        }

        if ($selected && $request->filled('new_end_date')) {
            $request->validate(
                ['new_end_date' => ['required', 'date', 'after:'.$selected->end_date->toDateString()]],
                ['new_end_date.after' => 'Tanggal selesai baru harus setelah tanggal selesai lama.'],
            );

            $preview = $this->preview($selected, Carbon::parse($request->new_end_date));
        }

        return view('admin.extensions', compact('rentals', 'selected', 'preview'));
    }

    /**
     * Menyimpan perpanjangan. Admin hanya mengubah tanggal selesai (BR-09).
     */
    public function store(Request $request, Rental $rental): RedirectResponse
    {
        $data = $request->validate([
            'new_end_date' => ['required', 'date', 'after:'.$rental->end_date->toDateString()],
            'payment_proof' => ['required', 'image', 'max:2048'],
        ], [
            'new_end_date.after' => 'Tanggal selesai baru harus setelah tanggal selesai lama.',
        ]);

        $newEnd = Carbon::parse($data['new_end_date']);

        DB::transaction(function () use ($request, $rental, $newEnd) {
            // Kunci baris garasi supaya hari tambahan tidak bentrok dengan sewa baru (BR-08)
            Garage::whereKey($rental->garage_id)->lockForUpdate()->firstOrFail();

            $preview = $this->preview($rental, $newEnd);

            if (! $preview['is_free']) {
                throw ValidationException::withMessages([
                    'new_end_date' => 'Garasi sudah disewa orang lain di hari tambahan itu.',
                ]);
            }

            $rental->extensions()->create([
                'old_end_date' => $rental->end_date,
                'new_end_date' => $newEnd,
                'added_days' => $preview['added_days'],
                'cost' => $preview['cost'],
                'payment_proof_path' => $request->file('payment_proof')->store('payment-proofs', 'public'),
            ]);

            $rental->update(['end_date' => $newEnd]);
        });

        return redirect()->route('extensions.index')->with(
            'success',
            "Sewa garasi {$rental->garage->code} diperpanjang sampai ".$newEnd->format('d-m-Y').'.'
        );
    }

    /**
     * Hitung hari tambahan, kekurangan biaya, dan cek garasi kosong di hari tambahan.
     */
    private function preview(Rental $rental, Carbon $newEnd): array
    {
        $from = $rental->end_date->copy()->addDay();

        return [
            'from' => $from,
            'new_end' => $newEnd,
            'added_days' => (int) $rental->end_date->diffInDays($newEnd),
            'cost' => $this->calculator->extensionCost($rental->garage->daily_price, $rental->end_date, $newEnd),
            'is_free' => $rental->garage->isFreeBetween($from, $newEnd),
        ];
    }
}
