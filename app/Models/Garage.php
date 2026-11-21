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
}
