<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\FormField;

class ValidFormFieldValue implements ValidationRule
{
    private FormField $field;
    private array $fieldRules;

    /**
     * Create a new rule instance.
     *
     * @param FormField $field The form field to validate against
     */
    public function __construct(FormField $field)
    {
        $this->field = $field;
        $this->fieldRules = $field->validation_rules ?? [];
    }

    /**
     * Run the validation rule.
     *
     * @param string $attribute
     * @param mixed $value
     * @param Closure $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Validate the value against the field's validation rules
        $validator = \Illuminate\Support\Facades\Validator::make(
            [$attribute => $value],
            [$attribute => $this->fieldRules]
        );

        if ($validator->fails()) {
            $fail($validator->errors()->first($attribute));
        }
    }
}