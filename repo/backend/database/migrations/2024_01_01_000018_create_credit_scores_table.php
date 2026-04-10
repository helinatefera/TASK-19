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
        Schema::create('credit_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->integer('score')->default(1000);
            $table->unsignedInteger('no_show_count')->default(0);
            $table->unsignedInteger('chargeback_count')->default(0);
            $table->unsignedInteger('refund_count')->default(0);
            $table->unsignedInteger('violation_count')->default(0);
            $table->string('restriction_level', 10)->default('none');
            $table->timestamp('restriction_until')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_scores');
    }
};
