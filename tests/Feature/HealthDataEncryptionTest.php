<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Account;
use App\Models\FormSubmission;
use App\Models\FormTemplate;
use App\Models\FormField;
use App\Models\HealthEntry;
use App\Models\User;
use App\Services\HealthDataEncryptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HealthDataEncryptionTest extends TestCase
{
    use RefreshDatabase;

    private HealthDataEncryptionService $encryptionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->encryptionService = app(HealthDataEncryptionService::class);
    }

    /**
     * Test that the encryption service encrypts data
     */
    public function test_health_data_encryption_service_encrypts_data()
    {
        $healthData = ['bp' => 120, 'hr' => 75];
        $encrypted = $this->encryptionService->encrypt($healthData);

        // Should return an array with 'data' key
        $this->assertIsArray($encrypted);
        $this->assertArrayHasKey('data', $encrypted);
        $this->assertIsString($encrypted['data']);
        
        // Should not contain plaintext
        $this->assertStringNotContainsString('120', $encrypted['data']);
        $this->assertStringNotContainsString('75', $encrypted['data']);
        
        // Should be decryptable
        $decrypted = $this->encryptionService->decrypt($encrypted);
        $this->assertEquals($healthData, $decrypted);
    }

    /**
     * Test that encrypted data is unreadable in database
     */
    public function test_encrypted_data_is_unreadable_in_database()
    {
        $account = Account::factory()->create();
        $submission = FormSubmission::factory()->create(['account_id' => $account->id]);

        $healthData = ['bp' => 140, 'hr' => 85, 'temperature' => 98.6];
        $encryptedData = $this->encryptionService->encrypt($healthData);

        $entry = HealthEntry::create([
            'submission_id' => $submission->id,
            'account_id' => $account->id,
            'timestamp' => now(),
            'encrypted_values' => $encryptedData,
        ]);

        // Fetch raw from database (bypass cast) - will be JSON string
        $rawEntry = HealthEntry::selectRaw('encrypted_values')->find($entry->id);
        $stored = $rawEntry->getAttributes()['encrypted_values'];

        // Should be stored as JSON string
        $this->assertIsString($stored);
        
        // Parse the JSON to check structure
        $parsed = json_decode($stored, true);
        $this->assertIsArray($parsed);
        $this->assertArrayHasKey('data', $parsed);
        
        // Should not contain plaintext values
        $this->assertStringNotContainsString('140', $parsed['data']);
        $this->assertStringNotContainsString('85', $parsed['data']);
    }

    /**
     * Test that HealthEntry model automatically decrypts data
     */
    public function test_health_entry_model_automatically_decrypts_data()
    {
        $account = Account::factory()->create();
        $submission = FormSubmission::factory()->create(['account_id' => $account->id]);

        $healthData = ['systolic' => 130, 'diastolic' => 80];
        $encryptedData = $this->encryptionService->encrypt($healthData);

        $entry = HealthEntry::create([
            'submission_id' => $submission->id,
            'account_id' => $account->id,
            'timestamp' => now(),
            'encrypted_values' => $encryptedData,
        ]);

        // Retrieve and check automatic decryption
        $retrieved = HealthEntry::find($entry->id);
        
        // Cast should decrypt automatically
        $this->assertIsArray($retrieved->encrypted_values);
        $this->assertEquals($healthData, $retrieved->encrypted_values);
    }

    /**
     * Test batch decryption works efficiently
     */
    public function test_batch_decryption_works_efficiently()
    {
        $account = Account::factory()->create();
        $submission = FormSubmission::factory()->create(['account_id' => $account->id]);

        $entries = [];
        for ($i = 0; $i < 3; $i++) {
            $data = ['reading' => $i * 10, 'unit' => 'bpm'];
            $encrypted = $this->encryptionService->encrypt($data);
            $entries[] = HealthEntry::create([
                'submission_id' => $submission->id,
                'account_id' => $account->id,
                'timestamp' => now(),
                'encrypted_values' => $encrypted,
            ])->toArray();
        }

        $decrypted = $this->encryptionService->batchDecrypt($entries);
        
        // All entries should be decrypted
        for ($i = 0; $i < 3; $i++) {
            $this->assertIsArray($decrypted[$i]['encrypted_values']);
            $this->assertEquals($i * 10, $decrypted[$i]['encrypted_values']['reading']);
        }
    }

    /**
     * Test decryption fails gracefully with corrupted data
     */
    public function test_decryption_fails_gracefully_with_corrupted_data()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Failed to decrypt health data/');

        $corruptedData = ['data' => 'not_encrypted_data_at_all'];
        $this->encryptionService->decrypt($corruptedData);
    }

    /**
     * Test form submission encrypts health entries
     */
    public function test_form_submission_encrypts_health_entries()
    {
        $account = Account::factory()->create();
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $template = FormTemplate::factory()->create();
        $field = FormField::create([
            'form_template_id' => $template->id,
            'label' => 'Test Field',
            'field_type' => 'Text',
        ]);

        $response = $this->postJson('/api/form-submissions', [
            'form_template_id' => $template->id,
            'entries' => [
                [
                    'field_id' => $field->id,
                    'value' => 'sensitive health data',
                ],
            ],
        ]);

        $response->assertStatus(201);
        $submissionId = $response->json('submission_id');

        // Check that health entry was created with encrypted data
        $entry = HealthEntry::where('submission_id', $submissionId)->first();
        $this->assertNotNull($entry);
        
        // The encrypted_values should be a string in the database
        $rawEntry = HealthEntry::selectRaw('encrypted_values')->find($entry->id);
        $stored = $rawEntry->getAttributes()['encrypted_values'];
        $this->assertIsString($stored);
        
        // But should decrypt through the cast when accessing the property
        $this->assertIsArray($entry->encrypted_values);
        $this->assertArrayHasKey('field_id', $entry->encrypted_values);
    }
}