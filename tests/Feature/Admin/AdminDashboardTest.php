<?php

namespace Tests\Feature\Admin;

use App\Models\Account;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;
use App\Livewire\Dashboards\AdminDashboard;
use Livewire\Livewire;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web'],
            ['id' => (string) Str::uuid()]
        );

        $user_account = Account::factory()->create();

        $user = User::factory()->withPersonalTeam()->create([
            'account_id' => $user_account->id,
        ]);

        $user->assignRole('admin');

        $this->user = $user;
    }

    public function test_the_component_can_render()
    {
        $this->actingAs($this->user);
        $component = Livewire::test(AdminDashboard::class);
        $component->assertStatus(200);
    }

    public function test_elements_are_present()
    {
        $this->actingAs($this->user);
        Livewire::test(AdminDashboard::class)
            ->assertSee('Audit Log')
            ->assertSee('Form Review')
            ->assertSee('Report Review')
            ->assertSee('Database Management');
    }
}
