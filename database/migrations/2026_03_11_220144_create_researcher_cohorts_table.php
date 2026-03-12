<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('researcher_cohorts', function (Blueprint $table) {

            $table->uuid('id')->primary();

            $table->string('name');
            $table->string('purpose', 500);

            // stored filter rules
            $table->json('filters_json');

            // estimated size at creation time
            $table->unsignedInteger('estimated_size');

            // cohort definition version
            $table->unsignedInteger('version')->default(1);

            // who created the cohort
            $table->uuid('created_by')->nullable();

            $table->timestamps();

            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('researcher_cohorts');
    }
};
