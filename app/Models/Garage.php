<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Garage extends Model
{
    protected $fillable = [
        'code',
        'type',
        'daily_price',
        'price_per_kwh',
        'status',
    ];

    /**
     * Daftar sewa di garasi ini.
     */
    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    /**
     * Membuat kode garasi dari tipe dan nomor urut.
     * Contoh: makeCode('car', 10) menghasilkan "C010".
     */
    public static function makeCode(string $type, int $number): string
    {
        $letters = ['bike' => 'S', 'car' => 'C', 'electric' => 'E'];

        return $letters[$type].str_pad((string) $number, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Cek garasi kosong di rentang tanggal ini (BR-08).
     * Dua rentang tanggal bertabrakan kalau mulaiA <= selesaiB dan mulaiB <= selesaiA.
     */
    public function isFreeBetween(Carbon $start, Carbon $end): bool
    {
        return ! $this->rentals()
            ->where('start_date', '<=', $end->toDateString())
            ->where('end_date', '>=', $start->toDateString())
            ->exists();
    }
}
