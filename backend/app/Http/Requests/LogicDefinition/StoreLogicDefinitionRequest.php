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
            'flow_id' => 'nullable|integer|exists:flows,id',
            'description' => 'nullable|string',
            'inputs' => 'nullable|array',
            'inputs.*.name' => 'required_with:inputs|string|max:255',
            'inputs.*.type' => 'required_with:inputs|string|max:255',
            'output' => 'nullable|string|max:255',
        ];
    }
}
