<?php

namespace Tests\Feature\Researcher;

use App\Models\Account;
use App\Models\HealthEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnonymizationBackendTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithAccount(): array
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        return [$user, $account];
    }

    public function test_me_summary_response_does_not_expose_direct_identifiers_or_raw_payloads(): void
    {
        [$user, $account] = $this->createUserWithAccount();

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => '2026-03-01 10:00:00',
            'encrypted_values' => [
                'hr' => 72,
                'name' => 'Alice Secret',
                'email' => 'alice.secret@example.com',
                'address' => '100 Hidden Street',
                'dob' => '1999-01-01',
                'notes' => 'private note',
            ],
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => '2026-03-02 10:00:00',
            'encrypted_values' => [
                'hr' => 78,
                'name' => 'Alice Secret',
                'email' => 'alice.secret@example.com',
                'address' => '100 Hidden Street',
                'dob' => '1999-01-01',
                'notes' => 'private note',
            ],
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/me/summary?from=2026-03-01&to=2026-03-03&keys=hr');

        $response->assertOk()
            ->assertJsonStructure([
                'from',
                'to',
                'averages' => ['hr'],
                'counts' => ['hr'],
            ]);

        $data = $response->json();
        $content = $response->getContent();

        $this->assertArrayNotHasKey('account_id', $data);
        $this->assertArrayNotHasKey('encrypted_values', $data);
        $this->assertArrayNotHasKey('email', $data);
        $this->assertArrayNotHasKey('name', $data);
        $this->assertArrayNotHasKey('address', $data);
        $this->assertArrayNotHasKey('dob', $data);
        $this->assertArrayNotHasKey('notes', $data);

        $this->assertStringNotContainsString('Alice Secret', $content);
        $this->assertStringNotContainsString('alice.secret@example.com', $content);
        $this->assertStringNotContainsString('100 Hidden Street', $content);
        $this->assertStringNotContainsString('1999-01-01', $content);
        $this->assertStringNotContainsString('private note', $content);
    }

    public function test_reporting_trends_response_does_not_expose_direct_identifiers_or_raw_payloads(): void
    {
        [$user, $account] = $this->createUserWithAccount();

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => '2026-03-01 09:00:00',
            'encrypted_values' => [
                'hr' => 70,
                'email' => 'leak-check@example.com',
                'name' => 'Leak Check',
                'address' => '200 Privacy Road',
                'notes' => 'sensitive note',
            ],
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => '2026-03-01 18:00:00',
            'encrypted_values' => [
                'hr' => 90,
                'email' => 'leak-check@example.com',
                'name' => 'Leak Check',
                'address' => '200 Privacy Road',
                'notes' => 'sensitive note',
            ],
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/reporting/trends?metric=hr&from=2026-03-01&to=2026-03-01&bucket=day');

        $response->assertOk()
            ->assertJsonStructure([
                'metric',
                'bucket',
                'from',
                'to',
                'points' => [
                    [
                        'bucket_start',
                        'count',
                        'min',
                        'max',
                        'avg',
                        'latest',
                        'latest_at',
                    ],
                ],
            ]);

        $data = $response->json();
        $content = $response->getContent();

        $this->assertArrayNotHasKey('account_id', $data);
        $this->assertArrayNotHasKey('encrypted_values', $data);
        $this->assertStringNotContainsString('leak-check@example.com', $content);
        $this->assertStringNotContainsString('Leak Check', $content);
        $this->assertStringNotContainsString('200 Privacy Road', $content);
        $this->assertStringNotContainsString('sensitive note', $content);
    }
}