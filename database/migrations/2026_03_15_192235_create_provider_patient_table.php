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
        Schema::create('provider_patient', function (Blueprint $table) {
            $table->uuid('provider_id');
            $table->uuid('patient_id');
            $table->foreign('provider_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('patient_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['provider_id', 'patient_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_patient');
    }
};
