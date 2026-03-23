<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('health_entries', function (Blueprint $table) {
            $table->index('timestamp');
            $table->index(['account_id', 'timestamp']);
        });
    }

    public function down(): void
    {
        Schema::table('health_entries', function (Blueprint $table) {
            $table->dropIndex(['timestamp']);
            $table->dropIndex(['account_id', 'timestamp']);
        });
    }
};
