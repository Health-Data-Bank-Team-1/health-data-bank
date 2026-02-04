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
        Schema::create('two_factor_methods', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('account_id')->nullable();
            $table->enum('method_type', ['SMS', 'TOTP', 'Email']);

            $table->string('secret_key');
            $table->boolean('enabled')->default(false);

            $table->foreign('account_id')
                ->references('id')
                ->on('accounts')
                ->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('two_factor_methods');
    }
};
