<?php

namespace Tests\Feature\Provider;

use App\Livewire\Provider\ProviderReports;
use App\Models\Account;
use App\Models\HealthEntry;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ProviderReportsTest extends TestCase
{
    use RefreshDatabase;

    protected User $providerUser;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Create provider role
        Role::firstOrCreate(
            ['name' => 'provider', 'guard_name' => 'web'],
            ['id' => (string) Str::uuid()]
        );

        // Create provider account
        $providerAccount = Account::factory()->create([
            'account_type' => 'HealthcareProvider',
        ]);

        // Create provider user
        $this->providerUser = User::factory()->create([
            'account_id' => $providerAccount->id,
        ]);

        $this->providerUser->assignRole('provider');
    }

    /** @test */
    public function provider_can_access_reports_page(): void
    {
        $this->actingAs($this->providerUser)
            ->get('/provider/reports')
            ->assertStatus(200)
            ->assertSee('Reports');
    }

    /** @test */
    public function non_provider_cannot_access_reports_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/provider/reports')
            ->assertStatus(403);
    }

    /** @test */
    public function provider_can_generate_participant_report(): void
    {
        // Create participant
        $participant = Account::factory()->create([
            'account_type' => 'User',
        ]);

        // Add health data
        HealthEntry::factory()->create([
            'account_id' => $participant->id,
            'timestamp' => now()->subDay(),
            'encrypted_values' => [
                'sleep_hours' => 6,
            ],
        ]);

        Livewire::actingAs($this->providerUser)
            ->test(ProviderReports::class)
            ->set('mode', 'participants')
            ->set('participant_ids', [$participant->id])
            ->set('metrics', ['sleep_hours'])
            ->set('date_from', now()->subWeek()->toDateString())
            ->set('date_to', now()->toDateString())
            ->call('generateReport')
            ->assertSet('report.type', 'participants')
            ->assertSee('Summary');
    }

    /** @test */
    public function participant_report_requires_selection(): void
    {
        Livewire::actingAs($this->providerUser)
            ->test(ProviderReports::class)
            ->set('mode', 'participants')
            ->set('participant_ids', [])
            ->set('metrics', ['sleep_hours'])
            ->set('date_from', now()->subWeek()->toDateString())
            ->set('date_to', now()->toDateString())
            ->call('generateReport')
            ->assertHasErrors(['participant_ids']);
    }

    /** @test */
    public function group_report_blocks_small_group(): void
    {
        // Create only 2 users (below threshold of 10)
        Account::factory()->count(2)->create([
            'account_type' => 'User',
        ]);

        Livewire::actingAs($this->providerUser)
            ->test(ProviderReports::class)
            ->set('mode', 'group')
            ->set('group_a', [
                'location' => '',
                'age_min' => '',
                'age_max' => '',
                'gender' => [],
            ])
            ->set('metrics', ['sleep_hours'])
            ->set('date_from', now()->subWeek()->toDateString())
            ->set('date_to', now()->toDateString())
            ->call('generateReport')
            ->assertStatus(422);
    }

    /** @test */
    public function group_report_succeeds_with_enough_users(): void
    {
        // Create 12 users (passes k-threshold = 10)
        $accounts = Account::factory()->count(12)->create([
            'account_type' => 'User',
        ]);

        foreach ($accounts as $account) {
            HealthEntry::factory()->create([
                'account_id' => $account->id,
                'timestamp' => now()->subDays(rand(1, 5)),
                'encrypted_values' => [
                    'sleep_hours' => rand(5, 8),
                ],
            ]);
        }

        Livewire::actingAs($this->providerUser)
            ->test(ProviderReports::class)
            ->set('mode', 'group')
            ->set('group_a', [
                'location' => '',
                'age_min' => '',
                'age_max' => '',
                'gender' => [],
            ])
            ->set('metrics', ['sleep_hours'])
            ->set('date_from', now()->subWeek()->toDateString())
            ->set('date_to', now()->toDateString())
            ->call('generateReport')
            ->assertSet('report.type', 'group')
            ->assertSee('Summary');
    }

    /** @test */
    public function validation_fails_without_metrics(): void
    {
        Livewire::actingAs($this->providerUser)
            ->test(ProviderReports::class)
            ->set('mode', 'participants')
            ->set('participant_ids', [])
            ->set('metrics', [])
            ->set('date_from', now()->subWeek()->toDateString())
            ->set('date_to', now()->toDateString())
            ->call('generateReport')
            ->assertHasErrors(['metrics']);
    }
}
