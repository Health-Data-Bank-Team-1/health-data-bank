<?php

namespace Tests\Feature\Database;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

class MigrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function accounts_table_has_required_columns()
    {
        $this->assertTrue(Schema::hasTable('accounts'));
        $this->assertTrue(Schema::hasColumn('accounts', 'id'));
        $this->assertTrue(Schema::hasColumn('accounts', 'account_type'));
        $this->assertTrue(Schema::hasColumn('accounts', 'name'));
        $this->assertTrue(Schema::hasColumn('accounts', 'email'));
        $this->assertTrue(Schema::hasColumn('accounts', 'status'));
        $this->assertTrue(Schema::hasColumn('accounts', 'created_at'));
        $this->assertTrue(Schema::hasColumn('accounts', 'updated_at'));
    }

    /** @test */
    public function dashboards_table_has_foreign_key_to_accounts()
    {
        $this->assertTrue(Schema::hasTable('dashboards'));
        $this->assertTrue(Schema::hasColumn('dashboards', 'account_id'));
    }

    /** @test */
    public function users_table_has_uuid_primary_key()
    {
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasColumn('users', 'id'));
    }

    /** @test */
    public function health_entries_table_exists_with_required_columns()
    {
        $this->assertTrue(Schema::hasTable('health_entries'));
        $this->assertTrue(Schema::hasColumn('health_entries', 'id'));
        $this->assertTrue(Schema::hasColumn('health_entries', 'account_id'));
        $this->assertTrue(Schema::hasColumn('health_entries', 'submission_id'));
        $this->assertTrue(Schema::hasColumn('health_entries', 'encrypted_values'));
    }

    /** @test */
    public function reports_table_exists_with_researcher_foreign_key()
    {
        $this->assertTrue(Schema::hasTable('reports'));
        $this->assertTrue(Schema::hasColumn('reports', 'researcher_id'));
        $this->assertTrue(Schema::hasColumn('reports', 'report_type'));
    }
}