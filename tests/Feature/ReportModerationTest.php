<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Report;
use App\Models\User;
use App\Models\Account;
use App\Models\AggregatedData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class ReportModerationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $adminUser;
    protected User $researcherUser;
    protected Account $researcherAccount;
    protected Report $report;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user with is_admin flag
        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com',
            'is_admin' => true,
        ]);

        // Create researcher account and user
        $this->researcherAccount = Account::factory()->create();
        $this->researcherUser = User::factory()->create([
            'email' => 'researcher@test.com',
            'account_id' => $this->researcherAccount->id,
            'is_admin' => false,
        ]);

        // Create a report
        $this->report = Report::factory()->create([
            'researcher_id' => $this->researcherAccount->id,
            'moderation_status' => 'approved',
        ]);
    }

    // ============================================================================
    // ARCHIVE REPORT TESTS
    // ============================================================================

    /** @test */
    public function admin_can_archive_a_report()
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/admin/reports/{$this->report->id}/archive", [
                'reason' => 'Report contains outdated data that needs review',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Report archived successfully');

        $this->assertDatabaseHas('reports', [
            'id' => $this->report->id,
            'moderation_status' => 'archived',
            'moderation_reason' => 'Report contains outdated data that needs review',
            'moderated_by' => $this->adminUser->id,
        ]);

        // Verify soft delete was NOT applied (archived != deleted)
        $this->assertNull($this->report->fresh()->deleted_at);
    }

    /** @test */
    public function archive_requires_reason()
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/admin/reports/{$this->report->id}/archive", [
                'reason' => '', // Empty reason
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['reason']);
    }

    /** @test */
    public function archive_reason_must_be_at_least_10_characters()
    {
        $response = $this->actingAs($this->adminUser)
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
        $this->actingAs($this->adminUser)
            ->postJson("/api/admin/reports/{$this->report->id}/archive", [
                'reason' => 'Report contains outdated data that needs review',
            ]);

        // Try to archive again
        $response = $this->actingAs($this->adminUser)
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
        $response = $this->actingAs($this->researcherUser)
            ->postJson("/api/admin/reports/{$this->report->id}/archive", [
                'reason' => 'Report contains outdated data that needs review',
            ]);

        $response->assertStatus(403); // Forbidden
    }

    /** @test */
    public function unauthenticated_user_cannot_archive_report()
    {
        $response = $this->postJson("/api/admin/reports/{$this->report->id}/archive", [
            'reason' => 'Report contains outdated data that needs review',
        ]);

        $response->assertStatus(401); // Unauthorized
    }

    // ============================================================================
    // DELETE REPORT TESTS (SOFT DELETE)
    // ============================================================================

    /** @test */
    public function admin_can_delete_report()
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/admin/reports/{$this->report->id}/delete", [
                'reason' => 'Report contains sensitive data that must be removed',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Report deleted successfully');

        // Verify soft delete
        $this->assertDatabaseHas('reports', [
            'id' => $this->report->id,
            'moderation_status' => 'deleted',
        ]);

        // Soft deleted reports are still in database but marked with deleted_at
        $this->assertNotNull($this->report->fresh()->deleted_at);
    }

    /** @test */
    public function delete_requires_reason()
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/admin/reports/{$this->report->id}/delete", [
                'reason' => '',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['reason']);
    }

    /** @test */
    public function cannot_delete_already_deleted_report()
    {
        // Delete first time
        $this->actingAs($this->adminUser)
            ->postJson("/api/admin/reports/{$this->report->id}/delete", [
                'reason' => 'Report contains sensitive data that must be removed',
            ]);

        // Try to delete again - use withTrashed to access soft-deleted report
        $deletedReport = Report::withTrashed()->find($this->report->id);
        
        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/admin/reports/{$deletedReport->id}/delete", [
                'reason' => 'Another reason for deletion of this report',
            ]);

        $response->assertStatus(400)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Report is already deleted');
    }

    /** @test */
    public function non_admin_cannot_delete_report()
    {
        $response = $this->actingAs($this->researcherUser)
            ->postJson("/api/admin/reports/{$this->report->id}/delete", [
                'reason' => 'Report contains sensitive data that must be removed',
            ]);

        $response->assertStatus(403);
    }

    // ============================================================================
    // RESTORE REPORT TESTS
    // ============================================================================

    /** @test */
    public function admin_can_restore_deleted_report()
    {
        // Delete report first
        $this->actingAs($this->adminUser)
            ->postJson("/api/admin/reports/{$this->report->id}/delete", [
                'reason' => 'Report contains sensitive data that must be removed',
            ]);

        // Now restore it
        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/admin/reports/{$this->report->id}/restore", [
                'reason' => 'Data verified and cleared for use',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Report restored successfully');

        // Verify restored
        $restored = $this->report->fresh();
        $this->assertNull($restored->deleted_at);
        $this->assertEquals('approved', $restored->moderation_status);
    }

    /** @test */
    public function cannot_restore_non_deleted_report()
    {
        $response = $this->actingAs($this->adminUser)
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
        // Delete report first
        $this->actingAs($this->adminUser)
            ->postJson("/api/admin/reports/{$this->report->id}/delete", [
                'reason' => 'Report contains sensitive data that must be removed',
            ]);

        // Restore without reason
        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/admin/reports/{$this->report->id}/restore", []);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function non_admin_cannot_restore_report()
    {
        // Delete first
        $this->actingAs($this->adminUser)
            ->postJson("/api/admin/reports/{$this->report->id}/delete", [
                'reason' => 'Report contains sensitive data that must be removed',
            ]);

        // Try to restore as non-admin
        $response = $this->actingAs($this->researcherUser)
            ->postJson("/api/admin/reports/{$this->report->id}/restore", [
                'reason' => 'Data verified and cleared for use',
            ]);

        $response->assertStatus(403);
    }

    // ============================================================================
    // GET MODERATION STATUS TESTS
    // ============================================================================

    /** @test */
    public function admin_can_view_report_moderation_status()
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson("/api/admin/reports/{$this->report->id}/moderation-status");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'status',
                    'is_archived',
                    'is_approved',
                    'reason',
                    'moderated_by',
                    'moderated_at',
                    'deleted_at',
                ]
            ]);
    }

    /** @test */
    public function moderation_status_shows_correct_information()
    {
        // Archive the report
        $this->actingAs($this->adminUser)
            ->postJson("/api/admin/reports/{$this->report->id}/archive", [
                'reason' => 'Report contains outdated data that needs review',
            ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson("/api/admin/reports/{$this->report->id}/moderation-status");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'archived')
            ->assertJsonPath('data.is_archived', true)
            ->assertJsonPath('data.is_approved', false);
    }

    /** @test */
    public function can_view_moderation_status_of_deleted_report()
    {
        // Delete the report
        $this->actingAs($this->adminUser)
            ->postJson("/api/admin/reports/{$this->report->id}/delete", [
                'reason' => 'Report contains sensitive data that must be removed',
            ]);

        // Should still be able to view status (includes soft-deleted)
        $response = $this->actingAs($this->adminUser)
            ->getJson("/api/admin/reports/{$this->report->id}/moderation-status");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'deleted');
    }

    /** @test */
    public function non_admin_cannot_view_moderation_status()
    {
        $response = $this->actingAs($this->researcherUser)
            ->getJson("/api/admin/reports/{$this->report->id}/moderation-status");

        $response->assertStatus(403);
    }

    // ============================================================================
    // PERMANENT DELETE TESTS (HARD DELETE)
    // ============================================================================

    /** @test */
    public function admin_can_permanently_delete_report()
    {
        $reportId = $this->report->id;

        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/admin/reports/{$reportId}/permanent-delete", [
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
        $response = $this->actingAs($this->adminUser)
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
        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/admin/reports/{$this->report->id}/permanent-delete", [
                'reason' => 'Too short reason here', // Less than 20 characters
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
        AggregatedData::factory()->count(3)->create([
            'report_id' => $reportId,
        ]);

        $this->assertDatabaseHas('aggregated_data', ['report_id' => $reportId]);

        // Permanently delete the report
        $this->actingAs($this->adminUser)
            ->postJson("/api/admin/reports/{$reportId}/permanent-delete", [
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
        $response = $this->actingAs($this->researcherUser)
            ->postJson("/api/admin/reports/{$this->report->id}/permanent-delete", [
                'reason' => 'Data must be removed per compliance requirement for HIPAA violation',
                'confirmed' => true,
            ]);

        $response->assertStatus(403);
    }

    // ============================================================================
    // AUDIT TRAIL AND LOGGING TESTS
    // ============================================================================

    /** @test */
    public function archiving_report_records_moderator_info()
    {
        $this->actingAs($this->adminUser)
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
        $firstModerator = $this->adminUser;

        // First moderation: archive
        $this->actingAs($firstModerator)
            ->postJson("/api/admin/reports/{$this->report->id}/archive", [
                'reason' => 'Report contains outdated data that needs review',
            ]);

        $firstModeration = $this->report->fresh();
        $this->assertEquals($firstModerator->id, $firstModeration->moderated_by);
        $firstTime = $firstModeration->moderated_at;

        // Create second admin and restore
        $secondModerator = User::factory()->create([
            'is_admin' => true,
        ]);

        $this->actingAs($secondModerator)
            ->postJson("/api/admin/reports/{$this->report->id}/restore", [
                'reason' => 'Data verified and cleared for use',
            ]);

        $secondModeration = $this->report->fresh();
        $this->assertEquals($secondModerator->id, $secondModeration->moderated_by);
        $this->assertGreaterThan($firstTime, $secondModeration->moderated_at);
    }

    // ============================================================================
    // ERROR HANDLING TESTS
    // ============================================================================

    /** @test */
    public function returns_404_for_nonexistent_report()
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/admin/reports/nonexistent-id/archive', [
                'reason' => 'Report contains outdated data that needs review',
            ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function validation_errors_return_proper_format()
    {
        $response = $this->actingAs($this->adminUser)
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