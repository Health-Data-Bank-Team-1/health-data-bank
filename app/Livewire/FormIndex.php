<?php

namespace App\Livewire;

use App\Models\FormTemplate;
use Livewire\Component;

class FormIndex extends Component
{
    public $forms;

    public function mount()
    {
        $this->forms = FormTemplate::query()
        ->select(['title', 'slug','id'])->get();
    }
    public function render()
    {
        return view('livewire.form-index');
    }
}
