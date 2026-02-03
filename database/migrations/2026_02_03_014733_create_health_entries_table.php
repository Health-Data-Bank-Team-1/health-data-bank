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
        Schema::create('health_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('submission_id')->nullable();
            $table->uuid('account_id')->nullable();
            $table->timestamp('timestamp');
            $table->jsonb('encrypted_values');
            $table->foreign('submission_id')
                ->references('id')
                ->on('form_submissions');
            $table->foreign('account_id')
                ->references('id')
                ->on('accounts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_entries');
    }
};
