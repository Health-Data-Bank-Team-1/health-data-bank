<?php

namespace App\Livewire;

use App\Models\FormTemplate;
use Livewire\Component;

class FormIndex extends Component
{
    public $forms;

    public function mount()
    {
        $this->forms = FormTemplate::where('approval_status', 'approved')
            ->select('title', 'slug')
            ->orderBy('title')
            ->get();    }
    public function render()
    {
        return view('livewire.form-index');
    }
}
