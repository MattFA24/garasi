<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use InvalidArgumentException;

/**
 * Semua perhitungan tanggal dan biaya sewa garasi.
 * Dipisah dari controller supaya bisa diuji dengan unit test.
 */
class RentalCalculator
{
    /**
     * Panjang 1 bulan = jumlah hari di bulan tanggal mulai (BR-02).
     */
    public function monthLength(Carbon $start): int
    {
        return $start->daysInMonth;
    }

    /**
     * Sewa harian maksimal = panjang bulan dikurangi 1 hari (BR-03).
     */
    public function maxDailyDays(Carbon $start): int
    {
        return $this->monthLength($start) - 1;
    }

    /**
     * Tanggal selesai = tanggal mulai + jumlah hari - 1 (BR-05).
     */
    public function endDate(Carbon $start, int $days): Carbon
    {
        return $start->copy()->addDays($days - 1);
    }

    /**
     * Biaya sewa. Harian = hari x harga, bulanan = 25 x harga (BR-03, BR-04).
     */
    public function rentCost(string $type, int $dailyPrice, int $days): int
    {
        if ($type === 'monthly') {
            return 25 * $dailyPrice;
        }

        return $days * $dailyPrice;
    }

    /**
     * Biaya isi listrik sampai penuh (BR-07).
     *
     * @throws InvalidArgumentException kalau sisa baterai negatif atau lebih besar dari kapasitas
     */
    public function chargingCost(float $capacity, float $remaining, int $pricePerKwh): int
    {
        if ($remaining < 0 || $remaining > $capacity) {
            throw new InvalidArgumentException('Sisa baterai harus antara 0 dan kapasitas.');
        }

        return (int) round(($capacity - $remaining) * $pricePerKwh);
    }

    /**
     * Biaya perpanjangan untuk hari tambahan (BR-10).
     * Setiap 1 bulan penuh = 25 x harga harian, sisanya dihitung harian.
     *
     * @throws InvalidArgumentException kalau tanggal selesai baru tidak setelah tanggal selesai lama
     */
    public function extensionCost(int $dailyPrice, Carbon $oldEnd, Carbon $newEnd): int
    {
        if ($newEnd->lte($oldEnd)) {
            throw new InvalidArgumentException('Tanggal selesai baru harus setelah tanggal selesai lama.');
        }

        $cost = 0;
        $cursor = $oldEnd->copy()->addDay();

        while ($cursor->lte($newEnd)) {
            $remainingDays = (int) $cursor->diffInDays($newEnd) + 1;
            $monthLength = $cursor->daysInMonth;

            if ($remainingDays >= $monthLength) {
                $cost += 25 * $dailyPrice;
                $cursor->addDays($monthLength);
            } else {
                $cost += $remainingDays * $dailyPrice;
                break;
            }
        }

        return $cost;
    }
}
