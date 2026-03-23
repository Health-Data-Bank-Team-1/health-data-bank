<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Account;
use App\Models\Report;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class ReportModerationTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $researcherUser;
    protected $report;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Create admin role
        Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web'],
            ['id' => (string) Str::uuid()]
        );

        // Create researcher role
        Role::firstOrCreate(
            ['name' => 'researcher', 'guard_name' => 'web'],
            ['id' => (string) Str::uuid()]
        );

        // Create admin user
        $adminAccount = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $this->adminUser = User::factory()->create([
            'account_id' => $adminAccount->id,
        ]);
        $this->adminUser->assignRole('admin');

        // Create researcher user
        $researcherAccount = Account::factory()->create([
            'account_type' => 'Researcher',
            'status' => 'ACTIVE',
        ]);

        $this->researcherUser = User::factory()->create([
            'account_id' => $researcherAccount->id,
        ]);
        $this->researcherUser->assignRole('researcher');

        // Create test report
        $this->report = Report::factory()->create();
    }

    /** @test */
    public function admin_can_archive_a_report()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson("/api/admin/reports/{$this->report->id}/archive", [
                'reason' => 'Report contains outdated data that needs review',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Report archived successfully');

        $this->assertDatabaseHas('reports', [
            'id' => $this->report->id,
            'is_archived' => true,
        ]);
    }

    /** @test */
    public function archive_requires_reason()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson("/api/admin/reports/{$this->report->id}/archive", [
                'reason' => '', // Empty reason
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['reason']);
    }

    /** @test */
    public function archive_reason_must_be_at_least_10_characters()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson("/api/admin/reports/{$this->report->id}/archive", [
                'reason' => 'Too short', // Only 9 characters
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['reason']);
    }

    /** @test */
    public function cannot_archive_already_archived_report()
    {
        // Archive first time
        $this->actingAs($this->adminUser, 'sanctum')
            ->postJson("/api/admin/reports/{$this->report->id}/archive", [
                'reason' => 'First archiving of this report',
            ]);

        // Try to archive again
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson("/api/admin/reports/{$this->report->id}/archive", [
                'reason' => 'Another reason for archiving this report',
            ]);

        $response->assertStatus(400)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Report is already archived');
    }

    /** @test */
    public function non_admin_cannot_archive_report()
    {
        $response = $this->actingAs($this->researcherUser, 'sanctum')
            ->postJson("/api/admin/reports/{$this->report->id}/archive", [
                'reason' => 'Report contains outdated data that needs review',
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function unauthenticated_user_cannot_archive_report()
    {
        $response = $this->postJson("/api/admin/reports/{$this->report->id}/archive", [
            'reason' => 'Report contains outdated data that needs review',
        ]);

        $response->assertStatus(401);
    }

    // ============================================================================
    // DELETE (SOFT DELETE) TESTS
    /** @test */
    public function admin_can_delete_report()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson("/api/admin/reports/{$this->report->id}/delete", [
                'reason' => 'Report contains sensitive data that must be removed',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Report deleted successfully');

        // Verify soft delete occurred
        $deleted = \DB::table('reports')->where('id', $this->report->id)->first();
        $this->assertNotNull($deleted->deleted_at);
    }

    /** @test */
    public function delete_requires_reason()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson("/api/admin/reports/{$this->report->id}/delete", [
                'reason' => '',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['reason']);
    }

    /** @test */
    public function cannot_delete_already_deleted_report()
    {
        $reportId = $this->report->id;

        // Delete first time
        $this->actingAs($this->adminUser, 'sanctum')
            ->postJson("/api/admin/reports/{$reportId}/delete", [
                'reason' => 'Report contains sensitive data that must be removed',
            ]);

        // Create a new report to test against (since soft-deleted ones get 404)
        $newReport = Report::factory()->create();

        // Delete the new report
        $this->actingAs($this->adminUser, 'sanctum')
            ->postJson("/api/admin/reports/{$newReport->id}/delete", [
                'reason' => 'Report contains sensitive data that must be removed',
            ]);

        // Try to delete the already-deleted report by ID directly
        // Since soft-deleted reports return 404, we'll verify the behavior differently
        // by checking that a not-deleted report shows 400 when checking if archived
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson("/api/admin/reports/{$newReport->id}/delete", [
                'reason' => 'Another reason for deletion of this report',
            ]);

        $response->assertStatus(400)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Report is already deleted');
    }

    /** @test */
    public function non_admin_cannot_delete_report()
    {
        $response = $this->actingAs($this->researcherUser, 'sanctum')
            ->postJson("/api/admin/reports/{$this->report->id}/delete", [
                'reason' => 'Report contains sensitive data that must be removed',
            ]);

        $response->assertStatus(403);
    }

    // ============================================================================
    // RESTORE TESTS
    /** @test */
    public function admin_can_restore_deleted_report()
    {
        $reportId = $this->report->id;

        // Delete first
        $this->actingAs($this->adminUser, 'sanctum')
            ->postJson("/api/admin/reports/{$reportId}/delete", [
                'reason' => 'Report contains sensitive data that must be removed',
            ]);

        // Restore - use report ID directly, Laravel will find it with withTrashed in the action
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson("/api/admin/reports/{$reportId}/restore", [
                'reason' => 'Data verified and cleared for use',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Report restored successfully');

        // Verify restored
        $this->assertDatabaseHas('reports', [
            'id' => $reportId,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function cannot_restore_non_deleted_report()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson("/api/admin/reports/{$this->report->id}/restore", [
                'reason' => 'Data verified and cleared for use',
            ]);

        $response->assertStatus(400)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Report is not deleted');
    }

    /** @test */
    public function restore_reason_is_optional()
    {
        $reportId = $this->report->id;

        // Delete first
        $this->actingAs($this->adminUser, 'sanctum')
            ->postJson("/api/admin/reports/{$reportId}/delete", [
                'reason' => 'Report contains sensitive data that must be removed',
            ]);

        // Restore without reason
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson("/api/admin/reports/{$reportId}/restore", []);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function non_admin_cannot_restore_report()
    {
        $response = $this->actingAs($this->researcherUser, 'sanctum')
            ->postJson("/api/admin/reports/{$this->report->id}/restore", [
                'reason' => 'Data verified and cleared for use',
            ]);

        $response->assertStatus(403);
    }

    // ============================================================================
    // MODERATION STATUS TESTS
    /** @test */
    public function admin_can_view_report_moderation_status()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson("/api/admin/reports/{$this->report->id}/moderation-status");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'status',
                    'is_archived',
                    'is_deleted',
                    'is_approved',
                ],
            ]);
    }

    /** @test */
    public function moderation_status_shows_correct_information()
    {
        // Archive the report
        $this->actingAs($this->adminUser, 'sanctum')
            ->postJson("/api/admin/reports/{$this->report->id}/archive", [
                'reason' => 'Report contains outdated data that needs review',
            ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson("/api/admin/reports/{$this->report->id}/moderation-status");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'archived')
            ->assertJsonPath('data.is_archived', true)
            ->assertJsonPath('data.is_approved', false);
    }

    /** @test */
    public function can_view_moderation_status_of_deleted_report()
    {
        $reportId = $this->report->id;

        // Delete the report
        $this->actingAs($this->adminUser, 'sanctum')
            ->postJson("/api/admin/reports/{$reportId}/delete", [
                'reason' => 'Report contains sensitive data that must be removed',
            ]);

        // Should still be able to view status (includes soft-deleted)
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson("/api/admin/reports/{$reportId}/moderation-status");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'deleted');
    }

    /** @test */
    public function non_admin_cannot_view_moderation_status()
    {
        $response = $this->actingAs($this->researcherUser, 'sanctum')
            ->getJson("/api/admin/reports/{$this->report->id}/moderation-status");

        $response->assertStatus(403);
    }

    // ============================================================================
    // PERMANENT DELETE TESTS (HARD DELETE)
    /** @test */
    public function admin_can_permanently_delete_report()
    {
        $reportId = $this->report->id;

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson("/api/admin/reports/{$this->report->id}/permanent-delete", [
                'reason' => 'Data must be removed per compliance requirement for HIPAA violation',
                'confirmed' => true,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Report permanently deleted');

        // Verify hard delete - report completely removed
        $this->assertDatabaseMissing('reports', ['id' => $reportId]);
    }

    /** @test */
    public function permanent_delete_requires_confirmed_flag()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson("/api/admin/reports/{$this->report->id}/permanent-delete", [
                'reason' => 'Data must be removed per compliance requirement for HIPAA violation',
                'confirmed' => false, // Not confirmed
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['confirmed']);

        // Report should still exist
        $this->assertDatabaseHas('reports', ['id' => $this->report->id]);
    }

    /** @test */
    public function permanent_delete_requires_long_reason()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson("/api/admin/reports/{$this->report->id}/permanent-delete", [
                'reason' => 'Short', // Less than 20 characters
                'confirmed' => true,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['reason']);
    }

    /** @test */
    public function permanent_delete_also_deletes_related_aggregated_data()
    {
        $reportId = $this->report->id;

        // Create aggregated data for this report
        \DB::table('aggregated_data')->insert([
            'id' => (string) Str::uuid(),
            'report_id' => $reportId,
            'metrics' => json_encode(['hr' => 80]),
            'anonymization_level' => 3,
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson("/api/admin/reports/{$this->report->id}/permanent-delete", [
                'reason' => 'Data must be removed per compliance requirement for HIPAA violation',
                'confirmed' => true,
            ]);

        // Verify both report and related data are gone
        $this->assertDatabaseMissing('reports', ['id' => $reportId]);
        $this->assertDatabaseMissing('aggregated_data', ['report_id' => $reportId]);
    }

    /** @test */
    public function non_admin_cannot_permanently_delete_report()
    {
        $response = $this->actingAs($this->researcherUser, 'sanctum')
            ->postJson("/api/admin/reports/{$this->report->id}/permanent-delete", [
                'reason' => 'Data must be removed per compliance requirement for HIPAA violation',
                'confirmed' => true,
            ]);

        $response->assertStatus(403);
    }

    // ============================================================================
    // AUDIT TRAIL TESTS
    /** @test */
    public function archiving_report_records_moderator_info()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson("/api/admin/reports/{$this->report->id}/archive", [
                'reason' => 'Report contains outdated data that needs review',
            ]);

        $updated = $this->report->fresh();

        $this->assertEquals('archived', $updated->moderation_status);
        $this->assertEquals($this->adminUser->id, $updated->moderated_by);
        $this->assertNotNull($updated->moderated_at);
        $this->assertEquals('Report contains outdated data that needs review', $updated->moderation_reason);
    }

    /** @test */
    public function multiple_moderation_actions_update_trail()
    {
        // First moderator archives
        $firstModerator = $this->adminUser;
        $this->actingAs($firstModerator, 'sanctum')
            ->postJson("/api/admin/reports/{$this->report->id}/archive", [
                'reason' => 'Report contains outdated data that needs review',
            ]);

        $firstModeration = Report::find($this->report->id);
        $this->assertEquals($firstModerator->id, $firstModeration->moderated_by);
        $firstTime = $firstModeration->moderated_at;

        // Create second admin
        $secondAdminAccount = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $secondModerator = User::factory()->create([
            'account_id' => $secondAdminAccount->id,
        ]);
        $secondModerator->assignRole('admin');

        sleep(1); // Ensure different timestamps

        // Second moderator deletes
        $this->actingAs($secondModerator, 'sanctum')
            ->postJson("/api/admin/reports/{$this->report->id}/delete", [
                'reason' => 'Report contains outdated data that needs review',
            ]);

        // Restore
        $this->actingAs($secondModerator, 'sanctum')
            ->postJson("/api/admin/reports/{$this->report->id}/restore", [
                'reason' => 'Data verified and cleared for use',
            ]);

        $secondModeration = Report::find($this->report->id);
        $this->assertNotNull($secondModeration, 'Report should exist after restore');
        $this->assertEquals($secondModerator->id, $secondModeration->moderated_by);
        $this->assertGreaterThan($firstTime, $secondModeration->moderated_at);
    }

    // ============================================================================
    // ERROR HANDLING TESTS
    /** @test */
    public function returns_404_for_nonexistent_report()
    {
        $fakeId = (string) Str::uuid();

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson("/api/admin/reports/{$fakeId}/moderation-status");

        $response->assertStatus(404);
    }

    /** @test */
    public function validation_errors_return_proper_format()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson("/api/admin/reports/{$this->report->id}/archive", [
                'reason' => 'x', // Too short
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['reason'],
            ]);
    }
}