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
        Schema::create('reconciliation_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('reconciliation_reports')->cascadeOnDelete();
            $table->foreignId('payment_id')->constrained('payments')->restrictOnDelete();
            $table->foreignId('order_id')->constrained('orders')->restrictOnDelete();
            $table->unsignedBigInteger('expected_amount');
            $table->unsignedBigInteger('actual_amount');
            $table->boolean('is_matched')->default(false);
            $table->string('notes', 500)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reconciliation_line_items');
    }
};
