<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Use a reasonably sized string; keep nullable for backwards compatibility
            $table->string('dedupe_key', 191)->nullable()->after('type');

            // Helpful indexes for common queries
            $table->index(['account_id', 'status']);
            $table->index(['account_id', 'type', 'created_at']);

            // Enforce dedupe across the whole table when dedupe_key is present.
            $table->unique(['account_id', 'dedupe_key']);
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropUnique(['account_id', 'dedupe_key']);
            $table->dropIndex(['account_id', 'status']);
            $table->dropIndex(['account_id', 'type', 'created_at']);
            $table->dropColumn('dedupe_key');
        });
    }
};