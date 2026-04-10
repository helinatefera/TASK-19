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
        Schema::create('backup_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);
            $table->string('file_path', 500);
            $table->unsignedBigInteger('file_size_bytes')->nullable();
            $table->string('checksum', 128)->nullable();
            $table->boolean('is_encrypted')->default(true);
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->boolean('is_successful')->default(false);
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backup_logs');
    }
};
