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
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id')->nullable();
            $table->uuid('form_template_id')->nullable();
            $table->enum('status', ['SUBMITTED', 'FLAGGED', 'APPROVED'])
                ->default('SUBMITTED');
            $table->timestamp('submitted_at')->useCurrent();
            $table->foreign('account_id')
                ->references('id')
                ->on('accounts');
            $table->foreign('form_template_id')
                ->references('id')
                ->on('form_templates');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};
