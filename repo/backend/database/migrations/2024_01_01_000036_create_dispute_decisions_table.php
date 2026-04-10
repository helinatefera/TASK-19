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
        Schema::create('dispute_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispute_id')->constrained()->restrictOnDelete();
            $table->foreignId('decided_by')->constrained('users')->restrictOnDelete();
            $table->string('decision', 50);
            $table->text('reasoning');
            $table->text('action_taken')->nullable();
            $table->string('checksum', 64);
            $table->timestamp('created_at')->nullable();
        });

        // Database-level immutability: deny UPDATE and DELETE on dispute_decisions.
        // This is the ultimate guard — even raw SQL or DB::table() cannot bypass it.
        DB::unprepared('
            CREATE OR REPLACE FUNCTION deny_dispute_decision_mutation()
            RETURNS TRIGGER AS $$
            BEGIN
                RAISE EXCEPTION \'dispute_decisions records are immutable and cannot be modified or deleted\';
                RETURN NULL;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER trg_dispute_decisions_no_update
                BEFORE UPDATE ON dispute_decisions
                FOR EACH ROW EXECUTE FUNCTION deny_dispute_decision_mutation();

            CREATE TRIGGER trg_dispute_decisions_no_delete
                BEFORE DELETE ON dispute_decisions
                FOR EACH ROW EXECUTE FUNCTION deny_dispute_decision_mutation();
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispute_decisions');
    }
};
