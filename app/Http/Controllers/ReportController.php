<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Laporan sewa per bulan, per hari, per tipe garasi, dan per penyewa (FR-08).
     * Pendapatan = total sewa + biaya perpanjangan, dicatat menurut tanggal mulai sewa.
     */
    public function index(Request $request): View
    {
        $data = $request->validate([
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        $year = (int) ($data['year'] ?? today()->year);
        $month = (int) ($data['month'] ?? today()->month);

        // Semua sewa yang mulai di tahun ini, sekalian jumlah biaya perpanjangannya
        $rentals = Rental::with(['tenant', 'garage'])
            ->withSum('extensions', 'cost')
            ->whereYear('start_date', $year)
            ->get();

        $perMonth = [];
        for ($m = 1; $m <= 12; $m++) {
            $perMonth[$m] = $this->summarize($rentals->filter(fn (Rental $rental) => $rental->start_date->month === $m));
        }

        $perDay = [];
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $perDay[$d] = $this->summarize($rentals->filter(
                fn (Rental $rental) => $rental->start_date->month === $month && $rental->start_date->day === $d
            ));
        }

        $perType = [];
        foreach (['bike', 'car', 'electric'] as $type) {
            $perType[$type] = $this->summarize($rentals->filter(fn (Rental $rental) => $rental->garage->type === $type));
        }

        $perTenant = $rentals->groupBy('user_id')
            ->map(fn (Collection $items) => ['name' => $items->first()->tenant->name] + $this->summarize($items))
            ->sortByDesc('revenue');

        return view('admin.reports', [
            'year' => $year,
            'month' => $month,
            'total' => $this->summarize($rentals),
            'perMonth' => $perMonth,
            'perDay' => $perDay,
            'perType' => $perType,
            'perTenant' => $perTenant,
        ]);
    }

    /**
     * Jumlah sewa dan total pendapatan dari sekumpulan sewa.
     */
    private function summarize(Collection $rentals): array
    {
        return [
            'count' => $rentals->count(),
            'revenue' => (int) $rentals->sum(fn (Rental $rental) => $rental->total_cost + ($rental->extensions_sum_cost ?? 0)),
        ];
    }
}
