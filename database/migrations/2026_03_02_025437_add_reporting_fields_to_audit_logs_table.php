<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {

            $table->string('route')->nullable()->after('action_type');
            $table->string('method', 10)->nullable()->after('route');
            $table->string('ip', 45)->nullable()->after('method');
            $table->text('user_agent')->nullable()->after('ip');
            $table->json('meta')->nullable()->after('user_agent');

            //index for quicker filtering
            $table->index(['actor_id', 'action_type']);
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['actor_id', 'action_type']);

            $table->dropColumn([
                'route',
                'method',
                'ip',
                'user_agent',
                'meta',
            ]);
        });
    }
};
