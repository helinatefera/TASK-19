<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_ip', 45)->nullable();
            $table->string('action', 100);
            $table->string('auditable_type', 150);
            $table->unsignedBigInteger('auditable_id');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('actor_id');
            $table->index(['auditable_type', 'auditable_id']);
            $table->index('action');
            $table->index('created_at');
        });

        // Add PostgreSQL rules to prevent UPDATE and DELETE on audit_logs
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('CREATE RULE audit_logs_no_update AS ON UPDATE TO audit_logs DO INSTEAD NOTHING;');
            DB::statement('CREATE RULE audit_logs_no_delete AS ON DELETE TO audit_logs DO INSTEAD NOTHING;');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('DROP RULE IF EXISTS audit_logs_no_update ON audit_logs;');
            DB::statement('DROP RULE IF EXISTS audit_logs_no_delete ON audit_logs;');
        }

        Schema::dropIfExists('audit_logs');
    }
};
