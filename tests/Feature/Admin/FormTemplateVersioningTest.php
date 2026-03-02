<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use App\Models\FormTemplate;
use App\Models\User;
use App\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class FormTemplateVersioningTest extends TestCase
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

    public function test_editing_template_creates_new_version()
    {
        $template = FormTemplate::factory()->create([
            'version' => 1,
            'schema' => ['fields' => ['bp']]
        ]);

        $template->update([
            'schema' => ['fields' => ['bp', 'hr']]
        ]);

        $this->assertEquals(2, $template->fresh()->version);

        $this->assertDatabaseHas('form_template_versions', [
            'form_template_id' => $template->id,
            'version' => 1
        ]);
    }

    public function test_admin_can_rollback_version()
    {
        $this->actingAsAdmin();

        $template = FormTemplate::factory()->create([
            'version' => 1,
            'schema' => ['fields' => ['bp']]
        ]);

        $template->update([
            'schema' => ['fields' => ['bp', 'hr']]
        ]);

        $this->postJson("/api/form-templates/{$template->id}/rollback/1")
            ->assertStatus(200);

        $this->assertEquals(
            ['fields' => ['bp']],
            $template->fresh()->schema
        );
    }

    public function test_cannot_rollback_to_future_version()
    {
        $this->actingAsAdmin();

        $template = FormTemplate::factory()->create([
            'version' => 1
        ]);

        $response = $this->postJson("/api/form-templates/{$template->id}/rollback/99");

        $response->assertStatus(422);
    }

}
