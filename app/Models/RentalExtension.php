<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalExtension extends Model
{
    protected $fillable = [
        'rental_id',
        'old_end_date',
        'new_end_date',
        'added_days',
        'cost',
        'payment_proof_path',
    ];

    protected function casts(): array
    {
        return [
            'old_end_date' => 'date',
            'new_end_date' => 'date',
        ];
    }

    /**
     * Sewa yang diperpanjang.
     */
    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }
}
