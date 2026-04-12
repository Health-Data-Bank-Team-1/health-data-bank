<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            // Archiving columns
            $table->boolean('is_archived')->default(false)->after('report_type');
            $table->text('archive_reason')->nullable()->after('is_archived');
            $table->uuid('archived_by')->nullable()->after('archive_reason');
            $table->timestamp('archived_at')->nullable()->after('archived_by');

            // Soft delete columns
            $table->uuid('deleted_by')->nullable()->after('archived_at');
            $table->text('deletion_reason')->nullable()->after('deleted_by');
            $table->timestamp('deleted_at')->nullable()->after('deletion_reason');

            // Restoration columns
            $table->uuid('restored_by')->nullable()->after('deleted_at');
            $table->text('restoration_reason')->nullable()->after('restored_by');
            $table->timestamp('restored_at')->nullable()->after('restoration_reason');

            // Moderation tracking
            $table->string('moderation_status')->default('pending')->after('restored_at');
            $table->text('moderation_reason')->nullable()->after('moderation_status');
            $table->uuid('moderated_by')->nullable()->after('moderation_reason');
            $table->timestamp('moderated_at')->nullable()->after('moderated_by');

            // Approval tracking
            $table->boolean('is_approved')->default(false)->after('moderated_at');

            // Foreign keys
            $table->foreign('archived_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('restored_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('moderated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['archived_by']);
            $table->dropForeign(['deleted_by']);
            $table->dropForeign(['restored_by']);
            $table->dropForeign(['moderated_by']);

            $table->dropColumn([
                'is_archived',
                'archive_reason',
                'archived_by',
                'archived_at',
                'deleted_by',
                'deletion_reason',
                'deleted_at',
                'restored_by',
                'restoration_reason',
                'restored_at',
                'moderation_status',
                'moderation_reason',
                'moderated_by',
                'moderated_at',
                'is_approved',
            ]);
        });
    }
};