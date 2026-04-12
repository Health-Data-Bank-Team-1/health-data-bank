<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_feedback', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('patient_account_id');
            $table->uuid('provider_account_id');
            $table->text('feedback');
            $table->text('recommended_actions')->nullable();
            $table->timestamps();

            $table->foreign('patient_account_id')
                ->references('id')
                ->on('accounts')
                ->cascadeOnDelete();

            $table->foreign('provider_account_id')
                ->references('id')
                ->on('accounts')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_feedback');
    }
};