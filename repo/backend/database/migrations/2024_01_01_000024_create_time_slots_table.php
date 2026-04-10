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
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            $table->string('programable_type', 150);
            $table->unsignedBigInteger('programable_id');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->unsignedInteger('seat_capacity');
            $table->unsignedInteger('seats_booked')->default(0);
            $table->timestamps();

            $table->index(['programable_type', 'programable_id']);
            $table->index('starts_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_slots');
    }
};
