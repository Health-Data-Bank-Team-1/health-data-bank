<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use App\Models\FormTemplate;
use App\Models\Role;
use App\Models\User;
use Tests\TestCase;

class FormTemplateApprovalTest extends TestCase
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

    public function test_admin_can_approve_a_pending_form()
    {
        $this->actingAsAdmin();

        $form = FormTemplate::factory()->create([
            'approval_status' => 'pending'
        ]);

        $response = $this->postJson("/api/admin/forms/{$form->id}/approve");

        $response->assertStatus(200);

        $this->assertDatabaseHas('form_templates', [
            'id' => $form->id,
            'approval_status' => 'approved'
        ]);
    }

    public function test_admin_can_reject_a_pending_form()
    {
        $this->actingAsAdmin();

        $form = FormTemplate::factory()->create([
            'approval_status' => 'pending'
        ]);

        $response = $this->postJson("/api/admin/forms/{$form->id}/reject", [
            'reason' => 'Invalid structure'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('form_templates', [
            'id' => $form->id,
            'approval_status' => 'rejected',
            'rejection_reason' => 'Invalid structure'
        ]);
    }

    public function test_cannot_approve_a_draft_form()
    {
        $this->actingAsAdmin();

        $form = FormTemplate::factory()->create([
            'approval_status' => 'draft'
        ]);

        $response = $this->postJson("/api/admin/forms/{$form->id}/approve");

        $response->assertStatus(422);
    }

    public function test_cannot_approve_already_approved_form()
    {
        $this->actingAsAdmin();

        $form = FormTemplate::factory()->create([
            'approval_status' => 'approved'
        ]);

        $response = $this->postJson("/api/admin/forms/{$form->id}/approve");

        $response->assertStatus(422);
    }

    public function test_reject_requires_reason()
    {
        $this->actingAsAdmin();

        $form = FormTemplate::factory()->create([
            'approval_status' => 'pending'
        ]);

        $response = $this->postJson("/api/admin/forms/{$form->id}/reject");

        $response->assertStatus(422);
    }

    public function test_workflow_errors_return_standard_json()
    {
        $this->actingAsAdmin();

        $form = FormTemplate::factory()->create([
            'approval_status' => 'approved'
        ]);

        $response = $this->postJson("/api/admin/forms/{$form->id}/approve");

        $response->assertJsonStructure([
            'error',
            'message',
            'status'
        ]);
    }
}
