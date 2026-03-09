<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function dropForeignIfExists(string $table, string $column): void
    {
        $db = DB::getDatabaseName();

        $row = DB::selectOne("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
              AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ", [$db, $table, $column]);

        if ($row?->CONSTRAINT_NAME) {
            DB::statement("ALTER TABLE `$table` DROP FOREIGN KEY `{$row->CONSTRAINT_NAME}`");
        }
    }

    private function dropCompositeIndexIfExists(string $table, array $columns): void
    {
        $db = DB::getDatabaseName();
        $cols = implode(',', $columns);

        $row = DB::selectOne("
            SELECT s.INDEX_NAME
            FROM information_schema.STATISTICS s
            WHERE s.TABLE_SCHEMA = ?
              AND s.TABLE_NAME = ?
            GROUP BY s.INDEX_NAME
            HAVING GROUP_CONCAT(s.COLUMN_NAME ORDER BY s.SEQ_IN_INDEX) = ?
            LIMIT 1
        ", [$db, $table, $cols]);

        if ($row?->INDEX_NAME) {
            DB::statement("ALTER TABLE `$table` DROP INDEX `{$row->INDEX_NAME}`");
        }
    }

    public function up(): void
    {
        //teams.user_id -> uuid
        $this->dropForeignIfExists('teams', 'user_id');

        Schema::table('teams', function (Blueprint $table) {
            $table->uuid('user_id')->change();
        });

        //Re-add FK
        Schema::table('teams', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });

        //team_user.user_id -> uuid (pivot table)
        $this->dropForeignIfExists('team_user', 'user_id');

        Schema::table('team_user', function (Blueprint $table) {
            $table->uuid('user_id')->change();
        });

        Schema::table('team_user', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });

        //sanctum: personal_access_tokens.tokenable_id -> uuid
        //drop composite index on (tokenable_type, tokenable_id) if it exists
        $this->dropCompositeIndexIfExists('personal_access_tokens', ['tokenable_type', 'tokenable_id']);

        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->uuid('tokenable_id')->change();
        });

        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->index(['tokenable_type', 'tokenable_id']);
        });
    }

    public function down(): void
    {

    }
};
