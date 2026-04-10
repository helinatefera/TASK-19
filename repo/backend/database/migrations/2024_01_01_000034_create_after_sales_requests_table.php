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
        Schema::create('after_sales_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('type', 20);
            $table->text('reason');
            $table->text('staff_notes')->nullable();
            $table->string('status', 20)->default('submitted');
            $table->string('attachment_path', 500)->nullable();
            $table->string('attachment_mime', 100)->nullable();
            $table->string('attachment_checksum', 128)->nullable();
            $table->unsignedInteger('attachment_size')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('after_sales_requests');
    }
};
