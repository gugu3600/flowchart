<?php

namespace App\Http\Requests\LogicDefinition;

use Illuminate\Foundation\Http\FormRequest;

class StoreLogicDefinitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'inputs' => 'nullable|array',
            'inputs.*' => 'string|max:255',
            'output' => 'nullable|string|max:255',
        ];
    }
}
