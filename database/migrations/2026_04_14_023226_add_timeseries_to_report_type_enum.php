<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE reports MODIFY report_type ENUM('Aggregated','Comparative','Timeseries')");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE reports MODIFY report_type ENUM('Aggregated','Comparative')");
    }
};
