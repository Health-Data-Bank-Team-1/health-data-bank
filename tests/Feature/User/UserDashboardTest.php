<?php

namespace Tests\Feature\User;

use App\Models\Account;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;
use App\Livewire\Dashboards\UserDashboard;
use App\Livewire\FormIndex;
use Livewire\Livewire;

class UserDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::firstOrCreate(
            ['name' => 'user', 'guard_name' => 'web'],
            ['id' => (string) Str::uuid()]
        );

        $user_account = Account::factory()->create();

        $user = User::factory()->withPersonalTeam()->create([
            'account_id' => $user_account->id,
        ]);

        $user->assignRole('user');

        $this->user = $user;
    }

    public function test_the_component_can_render()
    {
        $this->actingAs($this->user);
        $component = Livewire::test(UserDashboard::class);
        $component->assertStatus(200);
    }

    public function test_elements_are_present()
    {
        $this->actingAs($this->user);
        Livewire::test(UserDashboard::class)
            ->assertSee('Forms')
            ->assertSee('My Progress');
    }
}
