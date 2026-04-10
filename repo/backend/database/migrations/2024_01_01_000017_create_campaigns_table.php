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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained('users')->restrictOnDelete();
            $table->string('title', 200);
            $table->string('slug', 220)->unique();
            $table->text('description');
            $table->text('risk_disclosure');
            $table->unsignedBigInteger('target_amount');
            $table->unsignedBigInteger('pledged_amount')->default(0);
            $table->char('currency', 3)->default('USD');
            $table->string('status', 20)->default('draft');
            $table->string('visibility', 10)->default('offline');
            $table->unsignedSmallInteger('duration_days');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();

            $table->index('creator_id');
            $table->index('status');
            $table->index('ends_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
