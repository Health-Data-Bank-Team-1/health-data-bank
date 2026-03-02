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
        // Accounts table - make required fields NOT NULL
        Schema::table('accounts', function (Blueprint $table) {
            $table->enum('account_type', ['User', 'Researcher', 'HealthcareProvider', 'Admin'])->change();
            $table->string('name')->change();
            $table->string('email')->change();
            $table->enum('status', ['ACTIVE', 'DEACTIVATED'])->change();
        });

        // Users table - make required fields NOT NULL
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->change();
            $table->string('email')->change();
            $table->string('password')->change();
        });

        // Form Templates table - make required fields NOT NULL
        Schema::table('form_templates', function (Blueprint $table) {
            $table->string('title')->nullable(false)->change();
            $table->json('schema')->nullable(false)->change();
            $table->integer('version')->nullable(false)->change();
            $table->string('approval_status')->nullable(false)->change();
        });

        // Form Submissions table - make required fields NOT NULL
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->uuid('account_id')->nullable(false)->change();
            $table->uuid('form_template_id')->nullable(false)->change();
            $table->enum('status', ['SUBMITTED', 'FLAGGED', 'APPROVED'])->nullable(false)->change();
            $table->timestamp('submitted_at')->nullable(false)->change();
        });

        // Health Entries table - make required fields NOT NULL
        Schema::table('health_entries', function (Blueprint $table) {
            $table->uuid('submission_id')->nullable(false)->change();
            $table->uuid('account_id')->nullable(false)->change();
            $table->timestamp('timestamp')->nullable(false)->change();
            $table->jsonb('encrypted_values')->nullable(false)->change();
        });

        // Form Fields table - make required fields NOT NULL
        Schema::table('form_fields', function (Blueprint $table) {
            $table->uuid('form_template_id')->nullable(false)->change();
            $table->string('label')->nullable(false)->change();
            $table->enum('field_type', ['Text', 'Number', 'Date', 'RadioButton', 'Checkbox'])->nullable(false)->change();
        });

        // Dashboards table - make required fields NOT NULL
        Schema::table('dashboards', function (Blueprint $table) {
            $table->uuid('account_id')->nullable(false)->change();
        });

        // Health Goals table - make required fields NOT NULL
        Schema::table('health_goals', function (Blueprint $table) {
            $table->uuid('account_id')->nullable(false)->change();
            $table->float('target_value')->nullable(false)->change();
            $table->date('start_date')->nullable(false)->change();
            $table->enum('status', ['ACTIVE', 'MET', 'EXPIRED'])->nullable(false)->change();
        });

        // Reports table - make required fields NOT NULL
        Schema::table('reports', function (Blueprint $table) {
            $table->enum('report_type', ['Aggregated', 'Comparative'])->nullable(false)->change();
        });

        // Audit Logs table - make required fields NOT NULL
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->string('action_type')->nullable(false)->change();
            $table->timestamp('timestamp')->nullable(false)->change();
        });

        // Teams table - make required fields NOT NULL
        Schema::table('teams', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
            $table->boolean('personal_team')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        // Revert accounts table
        Schema::table('accounts', function (Blueprint $table) {
            $table->enum('account_type', ['User', 'Researcher', 'HealthcareProvider', 'Admin'])->nullable()->change();
            $table->string('name')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->enum('status', ['ACTIVE', 'DEACTIVATED'])->nullable()->change();
        });

        // Revert users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->string('password')->nullable()->change();
        });

        // Revert form templates
        Schema::table('form_templates', function (Blueprint $table) {
            $table->string('title')->nullable()->change();
            $table->json('schema')->nullable()->change();
            $table->integer('version')->nullable()->change();
            $table->string('approval_status')->nullable()->change();
        });

        // Revert form submissions
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->uuid('account_id')->nullable()->change();
            $table->uuid('form_template_id')->nullable()->change();
            $table->enum('status', ['SUBMITTED', 'FLAGGED', 'APPROVED'])->nullable()->change();
            $table->timestamp('submitted_at')->nullable()->change();
        });

        // Revert health entries
        Schema::table('health_entries', function (Blueprint $table) {
            $table->uuid('submission_id')->nullable()->change();
            $table->uuid('account_id')->nullable()->change();
            $table->timestamp('timestamp')->nullable()->change();
            $table->jsonb('encrypted_values')->nullable()->change();
        });

        // Revert form fields
        Schema::table('form_fields', function (Blueprint $table) {
            $table->uuid('form_template_id')->nullable()->change();
            $table->string('label')->nullable()->change();
            $table->enum('field_type', ['Text', 'Number', 'Date', 'RadioButton', 'Checkbox'])->nullable()->change();
        });

        // Revert dashboards
        Schema::table('dashboards', function (Blueprint $table) {
            $table->uuid('account_id')->nullable()->change();
        });

        // Revert health goals
        Schema::table('health_goals', function (Blueprint $table) {
            $table->uuid('account_id')->nullable()->change();
            $table->float('target_value')->nullable()->change();
            $table->date('start_date')->nullable()->change();
            $table->enum('status', ['ACTIVE', 'MET', 'EXPIRED'])->nullable()->change();
        });

        // Revert reports
        Schema::table('reports', function (Blueprint $table) {
            $table->enum('report_type', ['Aggregated', 'Comparative'])->nullable()->change();
        });

        // Revert audit logs
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->string('action_type')->nullable()->change();
            $table->timestamp('timestamp')->nullable()->change();
        });

        // Revert teams
        Schema::table('teams', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->boolean('personal_team')->nullable()->change();
        });
    }
};