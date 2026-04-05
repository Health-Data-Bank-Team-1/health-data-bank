<?php

namespace Tests\Feature;

use App\Livewire\FormRenderer;
use App\Models\FormField;
use App\Models\FormTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FormSubmissionTest extends TestCase
{
    use RefreshDatabase;

    private function formWithRequiredField(): FormTemplate
    {
        $template = FormTemplate::factory()->create([
            'title' => 'Vitals',
            'approval_status' => 'approved',
        ]);

        $template->fields()->create([
            'label' => 'Heart Rate',
            'field_type' => 'Text',
            'validation_rules' => ['required', 'string', 'max:255'],
        ]);

        return $template->load('fields');
    }

    public function test_validation_fails_when_required_field_empty(): void
    {
        $user = User::factory()->create();
        $form = $this->formWithRequiredField();
        $field = $form->fields->first();

        Livewire::actingAs($user)
            ->test(FormRenderer::class, ['form' => $form])
            ->set('entries.' . $field->id, null)
            ->call('submit')
            ->assertHasErrors('entries.' . $field->id);
    }

    public function test_valid_submission_redirects_with_success(): void
    {
        $user = User::factory()->create();
        $form = $this->formWithRequiredField();
        $field = $form->fields->first();

        Livewire::actingAs($user)
            ->test(FormRenderer::class, ['form' => $form])
            ->set('entries.' . $field->id, '72')
            ->call('submit')
            ->assertRedirect(route('user-form-select', [], false))
            ->assertSessionHas('flash.banner', 'Form submitted successfully!');
    }

    public function test_guest_cannot_access_form_page(): void
    {
        $form = $this->formWithRequiredField();

        $this->get(route('forms.show', $form))
            ->assertRedirect();
    }
}
