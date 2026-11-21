<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'plate_number',
        'brand',
        'model',
        'battery_capacity_kwh',
    ];

    /**
     * Penyewa pemilik kendaraan ini.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Daftar sewa untuk kendaraan ini.
     */
    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }
}
