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
        Schema::create('form_template_versions', function (Blueprint $table) {
            $table->id();

            $table->uuid('form_template_id');
            $table->foreign('form_template_id')
                ->references('id')
                ->on('form_templates')
                ->cascadeOnDelete();

            $table->integer('version');
            $table->string('title');
            $table->json('schema');
            $table->string('status'); // draft / approved / rejected

            $table->uuid('created_by')->nullable();
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_template_versions');
    }
};
