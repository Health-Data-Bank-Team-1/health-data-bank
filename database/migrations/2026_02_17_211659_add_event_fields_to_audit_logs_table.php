<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {

            // Add missing event columns
            $table->string('outcome')->nullable()->after('action_type');
            $table->string('reason_code')->nullable()->after('outcome');

            $table->string('target_type')->nullable()->after('reason_code');
            $table->string('target_id')->nullable()->after('target_type');

            $table->ipAddress('ip_address')->nullable()->after('target_id');
            $table->string('user_agent', 1023)->nullable()->after('ip_address');

            $table->json('metadata')->nullable()->after('user_agent');

            $table->index('action_type');
            $table->index('actor_id');
            $table->index(['target_type', 'target_id']);
            $table->index('timestamp');
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropColumn([
                'outcome',
                'reason_code',
                'target_type',
                'target_id',
                'ip_address',
                'user_agent',
                'metadata',

            ]);

            $table->index('action_type');
            $table->index('actor_id');
            $table->index(['target_type', 'target_id']);
            $table->index('timestamp');
        });
    }
};
