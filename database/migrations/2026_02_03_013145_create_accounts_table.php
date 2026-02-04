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
        Schema::create('accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('account_type', ['User', 'Researcher', 'HealthcareProvider', 'Admin']);
            $table->string('name');
            $table->string('email')->unique();
            $table->enum('status', ['ACTIVE', 'DEACTIVATED'])->default('ACTIVE');
            $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
