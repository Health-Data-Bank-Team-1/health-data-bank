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
        Schema::table('reports', function (Blueprint $table) {
            // Soft delete for archival
            if (!Schema::hasColumn('reports', 'deleted_at')) {
                $table->softDeletes()->nullable();
            }
            
            // Moderation status: pending, approved, archived, deleted
            if (!Schema::hasColumn('reports', 'moderation_status')) {
                $table->string('moderation_status', 50)->default('approved');
            }
            
            // Reason for deletion/archival
            if (!Schema::hasColumn('reports', 'moderation_reason')) {
                $table->text('moderation_reason')->nullable();
            }
            
            // Who performed the moderation (UUID)
            if (!Schema::hasColumn('reports', 'moderated_by')) {
                $table->uuid('moderated_by')->nullable();
            }
            
            // When moderation occurred
            if (!Schema::hasColumn('reports', 'moderated_at')) {
                $table->timestamp('moderated_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $columns = [
                'deleted_at',
                'moderation_status',
                'moderation_reason',
                'moderated_by',
                'moderated_at',
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('reports', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};