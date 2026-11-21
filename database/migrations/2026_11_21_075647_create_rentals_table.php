<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('vehicle_id')->constrained();
            $table->foreignId('garage_id')->constrained();
            $table->enum('rental_type', ['daily', 'monthly']);
            $table->integer('days');
            $table->date('start_date');
            $table->date('end_date');
            $table->float('battery_remaining_kwh')->nullable();
            $table->integer('rent_cost');
            $table->integer('charging_cost');
            $table->integer('total_cost');
            $table->string('payment_proof_path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
