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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('reviewee_id')->constrained('users')->restrictOnDelete();
            $table->string('side', 20);
            $table->unsignedTinyInteger('overall_rating');
            $table->text('body')->nullable();
            $table->string('public_alias', 50);
            $table->timestamp('visible_after');
            $table->boolean('is_visible')->default(false);
            $table->timestamps();

            $table->unique(['order_id', 'side']);
            $table->index('reviewer_id');
            $table->index('reviewee_id');
            $table->index(['is_visible', 'visible_after']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
