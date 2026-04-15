<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\FormTemplate;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Str;

class AdminFormTemplateIndexTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web'],
            ['id' => (string) Str::uuid()]
        );

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin, 'sanctum');

        return $admin;
    }

    public function test_non_admin_cannot_access_admin_forms_index()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $this->getJson('/api/admin/forms')
            ->assertStatus(403);
    }

    public function test_admin_can_list_forms()
    {
        $this->actingAsAdmin();

        FormTemplate::factory()->count(3)->create([
            'approval_status' => 'pending',
            'title' => 'Vitals',
        ]);

        $res = $this->getJson('/api/admin/forms');

        $res->assertStatus(200);

        //minimum guaranteed shape
        $res->assertJsonStructure([
            'data',
        ]);

        $this->assertIsArray($res->json('data'));
        $this->assertCount(3, $res->json('data'));
    }


    public function test_admin_can_filter_by_approval_status()
    {
        $this->actingAsAdmin();

        $pending = FormTemplate::factory()->count(2)->create([
            'approval_status' => 'pending',
            'title' => 'Pending Form',
        ]);

        $approved = FormTemplate::factory()->count(1)->create([
            'approval_status' => 'approved',
            'title' => 'Approved Form',
        ]);

        $res = $this->getJson('/api/admin/forms?approval_status=pending')
            ->assertStatus(200);

        //ensure returned records all match filter
        $data = $res->json('data');
        $this->assertNotEmpty($data);

        foreach ($data as $row) {
            $this->assertEquals('pending', $row['approval_status']);
        }

        //ensure approved one is not in results
        $ids = array_column($data, 'id');
        $this->assertFalse(in_array($approved->first()->id, $ids));
    }

    public function test_admin_can_search_by_title()
    {
        $this->actingAsAdmin();

        $match = FormTemplate::factory()->create([
            'title' => 'Vitals Form',
            'approval_status' => 'pending',
        ]);

        $other = FormTemplate::factory()->create([
            'title' => 'Diabetes Daily Log',
            'approval_status' => 'pending',
        ]);

        $res = $this->getJson('/api/admin/forms?search=vitals')
            ->assertStatus(200);

        $data = $res->json('data');
        $ids = array_column($data, 'id');

        $this->assertTrue(in_array($match->id, $ids));
        $this->assertFalse(in_array($other->id, $ids));
    }

    public function test_admin_can_filter_and_search_together()
    {
        $this->actingAsAdmin();

        $shouldMatch = FormTemplate::factory()->create([
            'title' => 'Vitals Form',
            'approval_status' => 'pending',
        ]);

        // same search term but different status should not match
        $wrongStatus = FormTemplate::factory()->create([
            'title' => 'Vitals Something',
            'approval_status' => 'approved',
        ]);

        //same status but different title should not match
        $wrongTitle = FormTemplate::factory()->create([
            'title' => 'Diabetes Daily Log',
            'approval_status' => 'pending',
        ]);

        $res = $this->getJson('/api/admin/forms?approval_status=pending&search=vitals')
            ->assertStatus(200);

        $data = $res->json('data');
        $ids = array_column($data, 'id');

        $this->assertTrue(in_array($shouldMatch->id, $ids));
        $this->assertFalse(in_array($wrongStatus->id, $ids));
        $this->assertFalse(in_array($wrongTitle->id, $ids));
    }
}
