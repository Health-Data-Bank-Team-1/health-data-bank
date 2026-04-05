<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->index('submitted_at');
            $table->index(['account_id', 'submitted_at']);
            $table->index(['account_id', 'form_template_id', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->dropIndex(['submitted_at']);
            $table->dropIndex(['account_id', 'submitted_at']);
            $table->dropIndex(['account_id', 'form_template_id', 'submitted_at']);
        });
    }
};
