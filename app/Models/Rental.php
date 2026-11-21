<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rental extends Model
{
    protected $fillable = [
        'user_id',
        'vehicle_id',
        'garage_id',
        'rental_type',
        'days',
        'start_date',
        'end_date',
        'battery_remaining_kwh',
        'rent_cost',
        'charging_cost',
        'total_cost',
        'payment_proof_path',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * Penyewa yang membuat sewa ini.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Kendaraan yang disimpan.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Garasi yang disewa.
     */
    public function garage(): BelongsTo
    {
        return $this->belongsTo(Garage::class);
    }

    /**
     * Riwayat perpanjangan sewa ini.
     */
    public function extensions(): HasMany
    {
        return $this->hasMany(RentalExtension::class);
    }
}
