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
        Schema::create('health_goals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id');
            $table->float('target_value');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['ACTIVE', 'MET', 'EXPIRED'])
                ->default('ACTIVE');
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
        Schema::dropIfExists('health_goals');
    }
};
