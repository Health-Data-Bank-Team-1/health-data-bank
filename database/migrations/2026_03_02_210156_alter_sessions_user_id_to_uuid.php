<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        //drop index if it exists
        try {
            DB::statement("DROP INDEX sessions_user_id_index ON sessions");
        } catch (\Throwable $e) {
            //ignore
        }

        //convert to UUID storage
        DB::statement("ALTER TABLE sessions MODIFY user_id CHAR(36) NULL");

        //re-add index
        DB::statement("CREATE INDEX sessions_user_id_index ON sessions (user_id)");
    }

    public function down(): void
    {
        try {
            DB::statement("DROP INDEX sessions_user_id_index ON sessions");
        } catch (\Throwable $e) {
            //ignore
        }

        DB::statement("ALTER TABLE sessions MODIFY user_id BIGINT UNSIGNED NULL");
        DB::statement("CREATE INDEX sessions_user_id_index ON sessions (user_id)");
    }
};
