<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add NOT NULL constraints to required fields
     */
    public function up(): void
    {
        // Form Templates table - only modify if columns exist
        if (Schema::hasTable('form_templates')) {
            Schema::table('form_templates', function (Blueprint $table) {
                // Only modify columns that actually exist
                if (Schema::hasColumn('form_templates', 'title')) {
                    $table->string('title')->nullable(false)->change();
                }
                if (Schema::hasColumn('form_templates', 'schema')) {
                    $table->json('schema')->nullable(false)->change();
                }
                if (Schema::hasColumn('form_templates', 'version')) {
                    $table->integer('version')->nullable(false)->change();
                }
                if (Schema::hasColumn('form_templates', 'approval_status')) {
                    $table->string('approval_status')->nullable(false)->change();
                }
            });
        }

        // Form Submissions table - make required fields NOT NULL
        if (Schema::hasTable('form_submissions')) {
            Schema::table('form_submissions', function (Blueprint $table) {
                if (Schema::hasColumn('form_submissions', 'account_id')) {
                    $table->uuid('account_id')->nullable(false)->change();
                }
                if (Schema::hasColumn('form_submissions', 'form_template_id')) {
                    $table->uuid('form_template_id')->nullable(false)->change();
                }
                if (Schema::hasColumn('form_submissions', 'status')) {
                    $table->enum('status', ['SUBMITTED', 'FLAGGED', 'APPROVED'])->nullable(false)->change();
                }
                if (Schema::hasColumn('form_submissions', 'submitted_at')) {
                    $table->timestamp('submitted_at')->nullable(false)->change();
                }
            });
        }

        // Health Entries table - make required fields NOT NULL
        if (Schema::hasTable('health_entries')) {
            Schema::table('health_entries', function (Blueprint $table) {
                if (Schema::hasColumn('health_entries', 'submission_id')) {
                    $table->uuid('submission_id')->nullable(false)->change();
                }
                if (Schema::hasColumn('health_entries', 'account_id')) {
                    $table->uuid('account_id')->nullable(false)->change();
                }
                if (Schema::hasColumn('health_entries', 'timestamp')) {
                    $table->timestamp('timestamp')->nullable(false)->change();
                }
                if (Schema::hasColumn('health_entries', 'encrypted_values')) {
                    $table->jsonb('encrypted_values')->nullable(false)->change();
                }
            });
        }

        // Form Fields table - make required fields NOT NULL
        if (Schema::hasTable('form_fields')) {
            Schema::table('form_fields', function (Blueprint $table) {
                if (Schema::hasColumn('form_fields', 'form_template_id')) {
                    $table->uuid('form_template_id')->nullable(false)->change();
                }
                if (Schema::hasColumn('form_fields', 'label')) {
                    $table->string('label')->nullable(false)->change();
                }
                if (Schema::hasColumn('form_fields', 'field_type')) {
                    $table->enum('field_type', ['Text', 'Number', 'Date', 'RadioButton', 'Checkbox'])->nullable(false)->change();
                }
            });
        }
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        // Revert form templates
        if (Schema::hasTable('form_templates')) {
            Schema::table('form_templates', function (Blueprint $table) {
                if (Schema::hasColumn('form_templates', 'title')) {
                    $table->string('title')->nullable()->change();
                }
                if (Schema::hasColumn('form_templates', 'schema')) {
                    $table->json('schema')->nullable()->change();
                }
                if (Schema::hasColumn('form_templates', 'version')) {
                    $table->integer('version')->nullable()->change();
                }
                if (Schema::hasColumn('form_templates', 'approval_status')) {
                    $table->string('approval_status')->nullable()->change();
                }
            });
        }

        // Revert form submissions
        if (Schema::hasTable('form_submissions')) {
            Schema::table('form_submissions', function (Blueprint $table) {
                if (Schema::hasColumn('form_submissions', 'account_id')) {
                    $table->uuid('account_id')->nullable()->change();
                }
                if (Schema::hasColumn('form_submissions', 'form_template_id')) {
                    $table->uuid('form_template_id')->nullable()->change();
                }
                if (Schema::hasColumn('form_submissions', 'status')) {
                    $table->enum('status', ['SUBMITTED', 'FLAGGED', 'APPROVED'])->nullable()->change();
                }
                if (Schema::hasColumn('form_submissions', 'submitted_at')) {
                    $table->timestamp('submitted_at')->nullable()->change();
                }
            });
        }

        // Revert health entries
        if (Schema::hasTable('health_entries')) {
            Schema::table('health_entries', function (Blueprint $table) {
                if (Schema::hasColumn('health_entries', 'submission_id')) {
                    $table->uuid('submission_id')->nullable()->change();
                }
                if (Schema::hasColumn('health_entries', 'account_id')) {
                    $table->uuid('account_id')->nullable()->change();
                }
                if (Schema::hasColumn('health_entries', 'timestamp')) {
                    $table->timestamp('timestamp')->nullable()->change();
                }
                if (Schema::hasColumn('health_entries', 'encrypted_values')) {
                    $table->jsonb('encrypted_values')->nullable()->change();
                }
            });
        }

        // Revert form fields
        if (Schema::hasTable('form_fields')) {
            Schema::table('form_fields', function (Blueprint $table) {
                if (Schema::hasColumn('form_fields', 'form_template_id')) {
                    $table->uuid('form_template_id')->nullable()->change();
                }
                if (Schema::hasColumn('form_fields', 'label')) {
                    $table->string('label')->nullable()->change();
                }
                if (Schema::hasColumn('form_fields', 'field_type')) {
                    $table->enum('field_type', ['Text', 'Number', 'Date', 'RadioButton', 'Checkbox'])->nullable()->change();
                }
            });
        }
    }
};