<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //if created_at exists already, only add updated_at.
        //if neither exists, add both.
        $hasCreated = Schema::hasColumn('form_templates', 'created_at');
        $hasUpdated = Schema::hasColumn('form_templates', 'updated_at');

        Schema::table('form_templates', function (Blueprint $table) use ($hasCreated, $hasUpdated) {
            if (!$hasCreated && !$hasUpdated) {
                $table->timestamps();
                return;
            }

            if (!$hasUpdated) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('form_templates', function (Blueprint $table) {
            if (Schema::hasColumn('form_templates', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
        });
    }
};
