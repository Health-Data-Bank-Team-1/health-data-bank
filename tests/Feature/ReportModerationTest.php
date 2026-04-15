<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\FormSubmission;
use App\Models\FormTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use App\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ReportModerationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $researcherUser;
    protected Account $userAccount;
    protected FormTemplate $template;
    protected FormSubmission $submission;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web'],
            ['id' => (string) Str::uuid()]
        );

        Role::firstOrCreate(
            ['name' => 'researcher', 'guard_name' => 'web'],
            ['id' => (string) Str::uuid()]
        );

        Role::firstOrCreate(
            ['name' => 'user', 'guard_name' => 'web'],
            ['id' => (string) Str::uuid()]
        );

        $adminAccount = Account::factory()->create([
            'account_type' => 'Admin',
            'status' => 'ACTIVE',
        ]);

        $this->adminUser = User::factory()->create([
            'account_id' => $adminAccount->id,
        ]);
        $this->adminUser->assignRole('admin');

        $researcherAccount = Account::factory()->create([
            'account_type' => 'Researcher',
            'status' => 'ACTIVE',
        ]);

        $this->researcherUser = User::factory()->create([
            'account_id' => $researcherAccount->id,
        ]);
        $this->researcherUser->assignRole('researcher');

        $this->userAccount = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $this->template = FormTemplate::factory()->create([
            'approval_status' => 'approved',
        ]);

        $this->submission = FormSubmission::create([
            'id' => (string) Str::uuid(),
            'account_id' => $this->userAccount->id,
            'form_template_id' => $this->template->id,
            'status' => 'FLAGGED',
            'submitted_at' => now(),
            'flag_reason' => 'Suspicious test submission',
            'flagged_by' => $this->adminUser->id,
            'flagged_at' => now(),
        ]);
    }

    /** @test */
    public function admin_can_view_flagged_reports_page(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.flagged'));

        $response->assertOk();
        $response->assertSee('Flagged Reports');
        $response->assertSee($this->template->title);
        $response->assertSee($this->userAccount->name);
    }

    /** @test */
    public function admin_can_view_single_flagged_report_review_page(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.review', $this->submission->id));

        $response->assertOk();
        $response->assertSee('Report Review');
        $response->assertSee($this->template->title);
        $response->assertSee($this->userAccount->name);
        $response->assertSee('Suspicious test submission');
    }

    /** @test */
    public function non_admin_cannot_view_flagged_reports_page(): void
    {
        $response = $this->actingAs($this->researcherUser)
            ->get(route('admin.reports.flagged'));

        $response->assertForbidden();
    }

    /** @test */
    public function unauthenticated_user_cannot_view_flagged_reports_page(): void
    {
        $response = $this->get(route('admin.reports.flagged'));

        $response->assertRedirect();
    }

    /** @test */
    public function admin_can_delete_flagged_report(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->delete(route('admin.reports.delete', $this->submission->id), [
                'deletion_reason' => 'Submission contains invalid health information.',
                'confirm_delete' => '1',
            ]);

        $response->assertRedirect(route('admin.reports.flagged'));
        $response->assertSessionHas('success', 'Submission deleted successfully.');

        $this->assertSoftDeleted('form_submissions', [
            'id' => $this->submission->id,
        ]);

        $this->assertDatabaseHas('form_submissions', [
            'id' => $this->submission->id,
            'status' => 'DELETED',
            'deleted_by' => $this->adminUser->id,
            'deletion_reason' => 'Submission contains invalid health information.',
        ]);
    }

    /** @test */
    public function delete_requires_reason(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->delete(route('admin.reports.delete', $this->submission->id), [
                'deletion_reason' => '',
                'confirm_delete' => '1',
            ]);

        $response->assertSessionHasErrors(['deletion_reason']);

        $this->assertDatabaseHas('form_submissions', [
            'id' => $this->submission->id,
            'status' => 'FLAGGED',
        ]);
    }

    /** @test */
    public function delete_requires_confirmation(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->delete(route('admin.reports.delete', $this->submission->id), [
                'deletion_reason' => 'Submission contains invalid health information.',
            ]);

        $response->assertSessionHasErrors(['confirm_delete']);

        $this->assertDatabaseHas('form_submissions', [
            'id' => $this->submission->id,
            'status' => 'FLAGGED',
        ]);
    }

    /** @test */
    public function non_admin_cannot_delete_flagged_report(): void
    {
        $response = $this->actingAs($this->researcherUser)
            ->delete(route('admin.reports.delete', $this->submission->id), [
                'deletion_reason' => 'Submission contains invalid health information.',
                'confirm_delete' => '1',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('form_submissions', [
            'id' => $this->submission->id,
            'status' => 'FLAGGED',
        ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_delete_flagged_report(): void
    {
        $response = $this->delete(route('admin.reports.delete', $this->submission->id), [
            'deletion_reason' => 'Submission contains invalid health information.',
            'confirm_delete' => '1',
        ]);

        $response->assertRedirect();
    }

    /** @test */
    public function deleted_report_no_longer_appears_in_flagged_reports_list(): void
    {
        $this->actingAs($this->adminUser)
            ->delete(route('admin.reports.delete', $this->submission->id), [
                'deletion_reason' => 'Submission contains invalid health information.',
                'confirm_delete' => '1',
            ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.flagged'));

        $response->assertOk();
        $response->assertDontSee('Suspicious test submission');
    }

    /** @test */
    public function review_page_returns_404_for_deleted_submission(): void
    {
        $this->actingAs($this->adminUser)
            ->delete(route('admin.reports.delete', $this->submission->id), [
                'deletion_reason' => 'Submission contains invalid health information.',
                'confirm_delete' => '1',
            ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.review', $this->submission->id));

        $response->assertNotFound();
    }

    /** @test */
    public function flagged_reports_page_only_shows_flagged_submissions(): void
    {
        FormSubmission::create([
            'id' => (string) Str::uuid(),
            'account_id' => $this->userAccount->id,
            'form_template_id' => $this->template->id,
            'status' => 'SUBMITTED',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.flagged'));

        $response->assertOk();
        $response->assertSee('Suspicious test submission');
        $response->assertDontSee('There are no flagged reports to review right now.');
    }
}
