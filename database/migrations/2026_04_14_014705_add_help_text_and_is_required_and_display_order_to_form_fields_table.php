<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_fields', function (Blueprint $table) {
            if (!Schema::hasColumn('form_fields', 'help_text')) {
                $table->text('help_text')->nullable()->after('label');
            }

            if (!Schema::hasColumn('form_fields', 'is_required')) {
                $table->boolean('is_required')->default(false)->after('field_type');
            }

            if (!Schema::hasColumn('form_fields', 'display_order')) {
                $table->unsignedInteger('display_order')->default(0)->after('validation_rules');
            }
        });
    }

    public function down(): void
    {
        Schema::table('form_fields', function (Blueprint $table) {
            if (Schema::hasColumn('form_fields', 'help_text')) {
                $table->dropColumn('help_text');
            }

            if (Schema::hasColumn('form_fields', 'is_required')) {
                $table->dropColumn('is_required');
            }

            if (Schema::hasColumn('form_fields', 'display_order')) {
                $table->dropColumn('display_order');
            }
        });
    }
};
