<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
