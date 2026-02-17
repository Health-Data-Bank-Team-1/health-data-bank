<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_templates', function (Blueprint $table) {
            $table->integer('version')->default(1)->change();
            $table->string('approval_status')->default('draft')->change();
        });
    }

    public function down(): void
    {
        Schema::table('form_templates', function (Blueprint $table) {
            $table->integer('version')->default(null)->change();
            $table->string('approval_status')->default(null)->change();
        });
    }
};

