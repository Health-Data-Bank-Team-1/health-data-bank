<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            if (! Schema::hasColumn('form_submissions', 'flag_reason')) {
                $table->text('flag_reason')->nullable()->after('status');
            }

            if (! Schema::hasColumn('form_submissions', 'flagged_by')) {
                $table->uuid('flagged_by')->nullable()->after('flag_reason');
            }

            if (! Schema::hasColumn('form_submissions', 'flagged_at')) {
                $table->timestamp('flagged_at')->nullable()->after('flagged_by');
            }

            if (! Schema::hasColumn('form_submissions', 'deleted_by')) {
                $table->uuid('deleted_by')->nullable()->after('flagged_at');
            }

            if (! Schema::hasColumn('form_submissions', 'deletion_reason')) {
                $table->text('deletion_reason')->nullable()->after('deleted_by');
            }

            if (! Schema::hasColumn('form_submissions', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            if (Schema::hasColumn('form_submissions', 'flag_reason')) {
                $table->dropColumn('flag_reason');
            }

            if (Schema::hasColumn('form_submissions', 'flagged_by')) {
                $table->dropColumn('flagged_by');
            }

            if (Schema::hasColumn('form_submissions', 'flagged_at')) {
                $table->dropColumn('flagged_at');
            }

            if (Schema::hasColumn('form_submissions', 'deleted_by')) {
                $table->dropColumn('deleted_by');
            }

            if (Schema::hasColumn('form_submissions', 'deletion_reason')) {
                $table->dropColumn('deletion_reason');
            }

            if (Schema::hasColumn('form_submissions', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
