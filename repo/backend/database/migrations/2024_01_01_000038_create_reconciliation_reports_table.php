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
        Schema::create('reconciliation_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generated_by')->constrained('users')->restrictOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->unsignedBigInteger('total_cash')->default(0);
            $table->unsignedBigInteger('total_card_on_file')->default(0);
            $table->unsignedBigInteger('expected_total')->default(0);
            $table->unsignedBigInteger('actual_total')->default(0);
            $table->bigInteger('discrepancy')->default(0);
            $table->string('status', 20)->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reconciliation_reports');
    }
};
