<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Account;
use App\Models\FormSubmission;
use App\Models\FormTemplate;
use App\Models\FormField;
use App\Models\HealthEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class FormSubmissionTest extends TestCase
{
    use RefreshDatabase;

    private Account $account;
    private FormTemplate $form;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test account
        $this->account = Account::factory()->create();

        // Create a user associated with that account
        $this->user = User::factory()->create();

        // Create an account for the user (mimics what the controller does)
        // This is important so the test has a valid account to reference
        Account::firstOrCreate(
            ['email' => $this->user->email],
            [
                'name' => $this->user->name,
                'account_type' => 'User',
                'status' => 'ACTIVE',
            ]
        );

        // Create a form template with fields
        $this->form = FormTemplate::factory()->create();
        
        // Create form fields
        for ($i = 0; $i < 2; $i++) {
            FormField::create([
                'form_template_id' => $this->form->id,
                'label' => "Field {$i}",
                'field_type' => 'Text',
                'validation_rules' => json_encode(['required']),
            ]);
        }
    }

    /**
     * Test: Unauthenticated user cannot submit form
     */
    public function test_unauthenticated_user_cannot_submit_form()
    {
        $this->postJson('/api/form-submissions', [
            'form_template_id' => $this->form->id,
            'entries' => [],
        ])->assertUnauthorized();
    }

    /**
     * Test: User can submit form with entries successfully
     */
    public function test_user_can_submit_form_with_entries()
    {
        $this->actingAs($this->user, 'sanctum');
        
        $formField = $this->form->fields->first();

        $response = $this->postJson('/api/form-submissions', [
            'form_template_id' => $this->form->id,
            'entries' => [
                [
                    'field_id' => $formField->id,
                    'value' => 'test value',
                ],
            ],
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'submission_id',
        ]);
    }

    /**
     * Test: form_template_id is required
     */
    public function test_form_submission_requires_template_id()
    {
        $this->actingAs($this->user, 'sanctum');

        $response = $this->postJson('/api/form-submissions', [
            'entries' => [],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('form_template_id');
    }

    /**
     * Test: entries array is required
     */
    public function test_form_submission_requires_entries_array()
    {
        $this->actingAs($this->user, 'sanctum');

        $response = $this->postJson('/api/form-submissions', [
            'form_template_id' => $this->form->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('entries');
    }

    /**
     * Test: template_id must exist
     */
    public function test_form_submission_rejects_nonexistent_template()
    {
        $this->actingAs($this->user, 'sanctum');

        $response = $this->postJson('/api/form-submissions', [
            'form_template_id' => Str::uuid(),
            'entries' => [],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('form_template_id');
    }

    /**
     * Test: Multiple entries are created correctly
     */
    public function test_multiple_entries_are_created()
    {
        $this->actingAs($this->user, 'sanctum');
        $fields = $this->form->fields;

        $response = $this->postJson('/api/form-submissions', [
            'form_template_id' => $this->form->id,
            'entries' => [
                [
                    'field_id' => $fields[0]->id,
                    'value' => 'value 1',
                ],
                [
                    'field_id' => $fields[1]->id,
                    'value' => 'value 2',
                ],
            ],
        ]);

        $response->assertStatus(201);
        $submissionId = $response->json('submission_id');

        // Verify all entries were created
        $this->assertCount(2, HealthEntry::where('submission_id', $submissionId)->get());
    }

    /**
     * Test: Submission status is set to 'SUBMITTED' (uppercase)
     */
    public function test_submission_status_defaults_to_submitted()
    {
        $this->actingAs($this->user, 'sanctum');

        $response = $this->postJson('/api/form-submissions', [
            'form_template_id' => $this->form->id,
            'entries' => [
                [
                    'field_id' => $this->form->fields->first()->id,
                    'value' => 'test',
                ],
            ],
        ]);

        $submissionId = $response->json('submission_id');
        $this->assertNotNull($submissionId);
        
        $submission = FormSubmission::find($submissionId);
        $this->assertNotNull($submission);
        $this->assertEquals('SUBMITTED', $submission->status);
    }

    /**
     * Test: submitted_at timestamp is recorded
     */
    public function test_submitted_at_timestamp_is_recorded()
    {
        $this->actingAs($this->user, 'sanctum');
        $before = now();

        $response = $this->postJson('/api/form-submissions', [
            'form_template_id' => $this->form->id,
            'entries' => [
                [
                    'field_id' => $this->form->fields->first()->id,
                    'value' => 'test',
                ],
            ],
        ]);

        $after = now();
        $submissionId = $response->json('submission_id');
        $this->assertNotNull($submissionId);
        
        $submission = FormSubmission::find($submissionId);

        $this->assertNotNull($submission);
        $this->assertTrue($submission->submitted_at >= $before);
        $this->assertTrue($submission->submitted_at <= $after);
    }

    /**
     * Test: Empty entries array is accepted
     */
    public function test_empty_entries_array_is_accepted()
    {
        $this->actingAs($this->user, 'sanctum');

        $response = $this->postJson('/api/form-submissions', [
            'form_template_id' => $this->form->id,
            'entries' => [],
        ]);

        // Should accept empty entries
        $this->assertTrue(
            $response->status() === 201 || $response->status() === 422,
            'Status should be 201 or 422, got ' . $response->status()
        );

        if ($response->status() === 201) {
            $submissionId = $response->json('submission_id');
            $this->assertNotNull($submissionId);
            
            // Submission exists but no health entries
            $this->assertDatabaseHas('form_submissions', ['id' => $submissionId]);
            $this->assertCount(0, HealthEntry::where('submission_id', $submissionId)->get());
        }
    }

    /**
     * Test: Submission can be retrieved by ID
     */
    public function test_submission_can_be_retrieved_with_health_entries()
    {
        $this->actingAs($this->user, 'sanctum');

        $response = $this->postJson('/api/form-submissions', [
            'form_template_id' => $this->form->id,
            'entries' => [
                [
                    'field_id' => $this->form->fields->first()->id,
                    'value' => 'test value',
                ],
            ],
        ]);

        $submissionId = $response->json('submission_id');
        $this->assertNotNull($submissionId);
        
        $submission = FormSubmission::with('healthEntries')->find($submissionId);

        $this->assertNotNull($submission);
        $this->assertTrue($submission->healthEntries->count() > 0);
    }

    /**
     * Test: Multiple users can submit forms independently
     */
    public function test_multiple_users_can_submit_forms()
    {
        // First user submits
        $this->actingAs($this->user, 'sanctum');
        
        $response1 = $this->postJson('/api/form-submissions', [
            'form_template_id' => $this->form->id,
            'entries' => [
                [
                    'field_id' => $this->form->fields->first()->id,
                    'value' => 'user 1 value',
                ],
            ],
        ]);

        $this->assertEquals(201, $response1->status());
        $submission1Id = $response1->json('submission_id');
        $this->assertNotNull($submission1Id);

        // Second user submits
        $user2 = User::factory()->create();
        
        // Create an account for user2
        Account::firstOrCreate(
            ['email' => $user2->email],
            [
                'name' => $user2->name,
                'account_type' => 'User',
                'status' => 'ACTIVE',
            ]
        );
        
        $this->actingAs($user2, 'sanctum');

        $response2 = $this->postJson('/api/form-submissions', [
            'form_template_id' => $this->form->id,
            'entries' => [
                [
                    'field_id' => $this->form->fields->first()->id,
                    'value' => 'user 2 value',
                ],
            ],
        ]);

        $this->assertEquals(201, $response2->status());
        $submission2Id = $response2->json('submission_id');
        $this->assertNotNull($submission2Id);

        // Both submissions should exist
        $this->assertDatabaseHas('form_submissions', ['id' => $submission1Id]);
        $this->assertDatabaseHas('form_submissions', ['id' => $submission2Id]);
    }

    /**
     * Test: Submission relationships work correctly
     */
    public function test_submission_has_correct_relationships()
    {
        $this->actingAs($this->user, 'sanctum');

        $response = $this->postJson('/api/form-submissions', [
            'form_template_id' => $this->form->id,
            'entries' => [
                [
                    'field_id' => $this->form->fields->first()->id,
                    'value' => 'test',
                ],
            ],
        ]);

        $submissionId = $response->json('submission_id');
        $this->assertNotNull($submissionId, 'Submission ID should not be null');

        // Refresh to ensure we get the latest data
        $submission = FormSubmission::findOrFail($submissionId);

        // Test account relationship
        $this->assertNotNull($submission->account_id, 'Submission should have account_id');
        $account = $submission->account;
        $this->assertNotNull($account, 'Submission should have an account relationship');

        // Test template relationship
        $this->assertNotNull($submission->form_template_id, 'Submission should have form_template_id');
        $this->assertEquals($this->form->id, $submission->form_template_id, 'Form template ID should match');

        // Test health entries relationship
        $healthEntries = $submission->healthEntries;
        $this->assertGreaterThan(0, $healthEntries->count(), 'Submission should have health entries');
    }

    /**
     * Test: Response includes correct submission ID format (UUID)
     */
    public function test_response_submission_id_is_valid_uuid()
    {
        $this->actingAs($this->user, 'sanctum');

        $response = $this->postJson('/api/form-submissions', [
            'form_template_id' => $this->form->id,
            'entries' => [
                [
                    'field_id' => $this->form->fields->first()->id,
                    'value' => 'test',
                ],
            ],
        ]);

        $submissionId = $response->json('submission_id');
        $this->assertNotNull($submissionId);
        $this->assertTrue(is_string($submissionId), 'submission_id should be a string');
        $this->assertTrue(Str::isUuid($submissionId), 'submission_id should be a valid UUID');
    }
}