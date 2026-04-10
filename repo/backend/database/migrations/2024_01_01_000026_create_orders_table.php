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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained('campaigns')->restrictOnDelete();
            $table->foreignId('venue_program_id')->nullable()->constrained('venue_programs');
            $table->foreignId('reward_tier_id')->nullable()->constrained('reward_tiers')->nullOnDelete();
            $table->foreignId('time_slot_id')->nullable()->constrained('time_slots')->nullOnDelete();
            $table->string('request_key', 64)->unique();
            $table->string('confirmation_number', 20)->unique()->nullable();
            $table->string('order_type', 20);
            $table->unsignedInteger('seat_quantity')->default(1);
            $table->unsignedBigInteger('amount');
            $table->char('currency', 3)->default('USD');
            $table->string('status', 20)->default('pending');
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason', 500)->nullable();
            $table->date('refund_deadline')->nullable();
            $table->boolean('attended')->nullable();
            $table->boolean('has_pending_refund')->default(false);
            $table->boolean('has_pending_after_sales')->default(false);
            $table->timestamps();

            $table->index('user_id');
            $table->index('campaign_id');
            $table->index('status');
            $table->index('request_key');
            $table->index('refund_deadline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
