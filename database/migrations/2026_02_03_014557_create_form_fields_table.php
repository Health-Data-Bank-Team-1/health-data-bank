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
        Schema::create('form_fields', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('form_template_id');

            $table->string('label');

            $table->enum('field_type', ['Text', 'Number', 'Date', 'Boolean', 'Dropdown']);

            $table->jsonb('validation_rules')->nullable();

            $table->foreign('form_template_id')
                ->references('id')
                ->on('form_templates')
                ->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
