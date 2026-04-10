<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_templates', function (Blueprint $table) {
            $table->string('locale', 10)->default('en')->after('key');
            $table->dropUnique(['key']);
            $table->unique(['key', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::table('notification_templates', function (Blueprint $table) {
            $table->dropUnique(['key', 'locale']);
            $table->unique(['key']);
            $table->dropColumn('locale');
        });
    }
};
