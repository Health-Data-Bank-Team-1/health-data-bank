<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\FormTemplate;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FormTemplateApprovalTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper: authenticate as an admin user
     */
    private function actingAsAdmin(): User
    {
        Role::firstOrCreate(['name' => 'admin']);

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
}
