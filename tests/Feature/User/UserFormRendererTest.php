<?php

namespace Tests\Feature\User;

use App\Models\Account;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;
use App\Livewire\FormRenderer;
use Livewire\Livewire;
use App\Models\FormField;
use App\Models\FormTemplate;
use Carbon\Carbon;

class UserFormRendererTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $form;
    protected $text_field;
    protected $radio_field;
    protected $checkbox_field;
    protected $date_field;
    protected $number_field;
    protected $email_field;

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


        $form = FormTemplate::create([
            'title' => 'Test Form',
            'version' => 1,
            'description' => 'test form',
        ]);

        $this->text_field = FormField::create([
            'form_template_id' => $form->id,
            'label' => 'Full Name',
            'field_type' => 'Text',
            'validation_rules' => ['required', 'string', 'max:255'],
        ]);

        $this->email_field = FormField::create([
            'form_template_id' => $form->id,
            'label' => 'Email Address',
            'field_type' => 'Text',
            'validation_rules' => ['required', 'email'],
        ]);

        $this->radio_field = FormField::create([
            'form_template_id' => $form->id,
            'label' => 'Radio Button',
            'field_type' => 'RadioButton',
            'validation_rules' => ['required'],
            'options' => ['yes', 'maybe', 'no'],
        ]);

        $this->checkbox_field = FormField::create([
            'form_template_id' => $form->id,
            'label' => 'Preferences',
            'field_type' => 'Checkbox',
            'validation_rules' => ['required'],
            'options' => ['Option A', 'Option B', 'Option C'],
        ]);

        $this->date_field = FormField::create([
            'form_template_id' => $form->id,
            'label' => 'Appointment Date',
            'field_type' => 'Date',
            'validation_rules' => ['required'],
        ]);

        $this->number_field = FormField::create([
            'form_template_id' => $form->id,
            'label' => 'Age',
            'field_type' => 'Number',
            'validation_rules' => ['required', 'integer', 'min:0'],
        ]);

        $this->form = $form;
    }

    public function test_the_component_can_render()
    {
        $this->actingAs($this->user);
        $component = Livewire::test(FormRenderer::class, ['form' => $this->form]);
        $component->assertStatus(200);
    }

    public function test_title_is_present()
    {
        $this->actingAs($this->user);
        Livewire::test(FormRenderer::class, ['form' => $this->form])
            ->assertSee('Test Form');
    }

    public function test_checkbox_multiple_inputs()
    {
        $this->actingAs($this->user);
        Livewire::test(FormRenderer::class, ['form' => $this->form])
            ->set("entries.{$this->checkbox_field->id}", ['Option A', 'Option B'])
            ->assertSet("entries.{$this->checkbox_field->id}", ['Option A', 'Option B']);
    }


    public function test_entries_are_updated()
    {
        $this->actingAs($this->user);
        Livewire::test(FormRenderer::class, ['form' => $this->form])
            ->set("entries.{$this->text_field->id}", 'test text')
            ->assertSet("entries.{$this->text_field->id}", 'test text');
    }

    public function test_render_different_field_types()
    {
        $this->actingAs($this->user);
        Livewire::test(FormRenderer::class, ['form' => $this->form])
            ->assertSee('Full Name')
            ->assertSee('Email Address')
            ->assertSee('Radio Button')
            ->assertSee('yes')
            ->assertSee('no')
            ->assertSee('maybe')
            ->assertSee('Preferences')
            ->assertSee('Option A')
            ->assertSee('Option B')
            ->assertSee('Option C')
            ->assertSee('Appointment Date')
            ->assertSee('Age');
    }

    public function test_validation_errors()
    {
        $this->actingAs($this->user);

        Livewire::test(FormRenderer::class, ['form' => $this->form])
            ->set("entries.{$this->text_field->id}", 999)
            ->call('submit')
            ->assertHasErrors(["entries.{$this->text_field->id}"]);

        Livewire::test(FormRenderer::class, ['form' => $this->form])
            ->set("entries.{$this->radio_field->id}", '')
            ->call('submit')
            ->assertHasErrors(["entries.{$this->radio_field->id}"]);

        Livewire::test(FormRenderer::class, ['form' => $this->form])
            ->set("entries.{$this->checkbox_field->id}", '')
            ->call('submit')
            ->assertHasErrors(["entries.{$this->checkbox_field->id}"]);

        Livewire::test(FormRenderer::class, ['form' => $this->form])
            ->set("entries.{$this->date_field->id}", '')
            ->call('submit')
            ->assertHasErrors(["entries.{$this->date_field->id}"]);

        Livewire::test(FormRenderer::class, ['form' => $this->form])
            ->set("entries.{$this->number_field->id}", 'text')
            ->call('submit')
            ->assertHasErrors(["entries.{$this->number_field->id}"]);
    }

    public function test_valid_submission()
    {
        $this->actingAs($this->user);

        Livewire::test(FormRenderer::class, ['form' => $this->form])
            ->set("entries.{$this->text_field->id}", 'Test Name')
            ->set("entries.{$this->email_field->id}", 'testemail@test.com')
            ->set("entries.{$this->radio_field->id}", 'yes')
            ->set("entries.{$this->checkbox_field->id}", ['Option A', 'Option C'])
            ->set("entries.{$this->date_field->id}", Carbon::today())
            ->set("entries.{$this->number_field->id}", 999)
            ->call('submit')
            ->assertHasNoErrors();
    }
}
