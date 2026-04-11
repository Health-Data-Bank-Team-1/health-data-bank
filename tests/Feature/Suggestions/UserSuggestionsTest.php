<?php

namespace Tests\Feature\Suggestions;

use App\Livewire\UserSuggestions;
use App\Models\Account;
use App\Models\HealthEntry;
use App\Models\Role;
use App\Models\User;
use App\Services\SuggestionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UserSuggestionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::firstOrCreate(
            ['name' => 'user', 'guard_name' => 'web'],
            ['id' => (string) Str::uuid()]
        );

        Role::firstOrCreate(
            ['name' => 'researcher', 'guard_name' => 'web'],
            ['id' => (string) Str::uuid()]
        );
    }

    private function createUserWithAccountAndRole(): User
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        $user->assignRole('user');

        return $user;
    }

    public function test_guest_cannot_access_suggestions_page(): void
    {
        $this->get(route('user-suggestions'))
            ->assertRedirect();
    }

    public function test_user_without_user_role_cannot_access_suggestions_page(): void
    {
        $user = User::factory()->create();
        $user->assignRole('researcher');

        $this->actingAs($user)
            ->get(route('user-suggestions'))
            ->assertStatus(403);
    }

    public function test_authenticated_user_with_role_can_access_suggestions_page(): void
    {
        $user = $this->createUserWithAccountAndRole();

        $this->actingAs($user)
            ->get(route('user-suggestions'))
            ->assertStatus(200);
    }

    public function test_suggestions_page_returns_ok(): void
    {
        $user = $this->createUserWithAccountAndRole();

        $response = $this->actingAs($user)->get(route('user-suggestions'));

        $response->assertStatus(200);
        $response->assertSee('Suggestions', false);
    }

    public function test_suggestions_page_displays_header(): void
    {
        $user = $this->createUserWithAccountAndRole();

        $this->actingAs($user)
            ->get(route('user-suggestions'))
            ->assertSee('Suggestions', false);
    }

    public function test_component_mount_sets_date_range(): void
    {
        $user = $this->createUserWithAccountAndRole();

        $this->mock(SuggestionService::class)
            ->shouldReceive('generateForAccount')
            ->once()
            ->andReturn([
                'from' => now()->subDays(30)->toIso8601String(),
                'to' => now()->toIso8601String(),
                'suggestions' => [],
            ]);

        Livewire::actingAs($user)
            ->test(UserSuggestions::class)
            ->assertSet('from', now()->subDays(30)->toDateString())
            ->assertSet('to', now()->toDateString());
    }

    public function test_component_displays_suggestion_cards(): void
    {
        $user = $this->createUserWithAccountAndRole();

        $this->mock(SuggestionService::class)
            ->shouldReceive('generateForAccount')
            ->once()
            ->andReturn([
                'from' => now()->subDays(30)->toIso8601String(),
                'to' => now()->toIso8601String(),
                'suggestions' => [
                    [
                        'type' => 'high_value',
                        'metric' => 'hr',
                        'severity' => 'medium',
                        'title' => 'Metric is above expected range',
                        'message' => 'Average value is above the expected range.',
                        'context' => [
                            'avg' => 92.0,
                            'threshold' => 85.0,
                            'label' => 'Heart Rate',
                            'unit' => 'bpm',
                        ],
                    ],
                ],
            ]);

        Livewire::actingAs($user)
            ->test(UserSuggestions::class)
            ->assertSee('Metric is above expected range')
            ->assertSee('Average value is above the expected range.')
            ->assertSee('Heart Rate')
            ->assertSee('Medium');
    }

    public function test_component_displays_no_suggestions_message_when_empty(): void
    {
        $user = $this->createUserWithAccountAndRole();

        $this->mock(SuggestionService::class)
            ->shouldReceive('generateForAccount')
            ->once()
            ->andReturn([
                'from' => now()->subDays(30)->toIso8601String(),
                'to' => now()->toIso8601String(),
                'suggestions' => [],
            ]);

        Livewire::actingAs($user)
            ->test(UserSuggestions::class)
            ->assertSee('No suggestions could be generated for this period.');
    }

    public function test_component_renders_high_severity_badge(): void
    {
        $user = $this->createUserWithAccountAndRole();

        $this->mock(SuggestionService::class)
            ->shouldReceive('generateForAccount')
            ->once()
            ->andReturn([
                'from' => now()->subDays(30)->toIso8601String(),
                'to' => now()->toIso8601String(),
                'suggestions' => [
                    [
                        'type' => 'high_value',
                        'metric' => 'hr',
                        'severity' => 'high',
                        'title' => 'Critical Alert',
                        'message' => 'Immediate attention needed.',
                        'context' => [],
                    ],
                ],
            ]);

        Livewire::actingAs($user)
            ->test(UserSuggestions::class)
            ->assertSee('High');
    }

    public function test_component_renders_low_severity_badge(): void
    {
        $user = $this->createUserWithAccountAndRole();

        $this->mock(SuggestionService::class)
            ->shouldReceive('generateForAccount')
            ->once()
            ->andReturn([
                'from' => now()->subDays(30)->toIso8601String(),
                'to' => now()->toIso8601String(),
                'suggestions' => [
                    [
                        'type' => 'positive_trend',
                        'metric' => 'weight',
                        'severity' => 'low',
                        'title' => 'Metric trend is improving',
                        'message' => 'Recent trend data suggests this metric may be improving.',
                        'context' => [
                            'label' => 'Weight',
                        ],
                    ],
                ],
            ]);

        Livewire::actingAs($user)
            ->test(UserSuggestions::class)
            ->assertSee('Low');
    }

    public function test_component_renders_context_values(): void
    {
        $user = $this->createUserWithAccountAndRole();

        $this->mock(SuggestionService::class)
            ->shouldReceive('generateForAccount')
            ->once()
            ->andReturn([
                'from' => now()->subDays(30)->toIso8601String(),
                'to' => now()->toIso8601String(),
                'suggestions' => [
                    [
                        'type' => 'high_value',
                        'metric' => 'hr',
                        'severity' => 'medium',
                        'title' => 'Metric is above expected range',
                        'message' => 'Average value is above the expected range.',
                        'context' => [
                            'avg' => 92,
                            'threshold' => 85,
                        ],
                    ],
                ],
            ]);

        Livewire::actingAs($user)
            ->test(UserSuggestions::class)
            ->assertSee('Avg:')
            ->assertSee('92')
            ->assertSee('Threshold:')
            ->assertSee('85');
    }

    public function test_component_renders_metric_label_when_present(): void
    {
        $user = $this->createUserWithAccountAndRole();

        $this->mock(SuggestionService::class)
            ->shouldReceive('generateForAccount')
            ->once()
            ->andReturn([
                'from' => now()->subDays(30)->toIso8601String(),
                'to' => now()->toIso8601String(),
                'suggestions' => [
                    [
                        'type' => 'insufficient_data',
                        'metric' => 'hr',
                        'severity' => 'low',
                        'title' => 'More data needed',
                        'message' => 'More data is needed for reliable insights for this metric.',
                        'context' => [
                            'count' => 2,
                            'label' => 'Heart Rate',
                        ],
                    ],
                ],
            ]);

        Livewire::actingAs($user)
            ->test(UserSuggestions::class)
            ->assertSee('Heart Rate');
    }

    public function test_component_displays_multiple_suggestions(): void
    {
        $user = $this->createUserWithAccountAndRole();

        $this->mock(SuggestionService::class)
            ->shouldReceive('generateForAccount')
            ->once()
            ->andReturn([
                'from' => now()->subDays(30)->toIso8601String(),
                'to' => now()->toIso8601String(),
                'suggestions' => [
                    [
                        'type' => 'high_value',
                        'metric' => 'hr',
                        'severity' => 'high',
                        'title' => 'High Heart Rate',
                        'message' => 'Heart rate is elevated.',
                        'context' => [],
                    ],
                    [
                        'type' => 'positive_trend',
                        'metric' => 'weight',
                        'severity' => 'low',
                        'title' => 'Weight Improving',
                        'message' => 'Weight trend is positive.',
                        'context' => [],
                    ],
                ],
            ]);

        Livewire::actingAs($user)
            ->test(UserSuggestions::class)
            ->assertSee('High Heart Rate')
            ->assertSee('Weight Improving')
            ->assertSee('High')
            ->assertSee('Low');
    }

    public function test_integration_with_real_data_returns_no_data_suggestion(): void
    {
        $user = $this->createUserWithAccountAndRole();

        Livewire::actingAs($user)
            ->test(UserSuggestions::class)
            ->assertSee('No data available');
    }

    public function test_integration_with_real_data_returns_suggestions_for_entries(): void
    {
        $user = $this->createUserWithAccountAndRole();
        $accountId = $user->account_id;

        for ($i = 0; $i < 3; $i++) {
            HealthEntry::factory()->create([
                'account_id' => $accountId,
                'timestamp' => Carbon::now()->subDays(30 - $i)->startOfDay(),
                'encrypted_values' => ['hr' => 90 + $i],
            ]);
        }

        Livewire::actingAs($user)
            ->test(UserSuggestions::class)
            ->assertSee('Heart Rate');
    }

    public function test_view_does_not_render_suggestion_section_when_result_is_empty(): void
    {
        $user = $this->createUserWithAccountAndRole();

        Livewire::actingAs($user)
            ->test(UserSuggestions::class)
            ->assertDontSee('No suggestions could be generated for this period.');
    }
}
