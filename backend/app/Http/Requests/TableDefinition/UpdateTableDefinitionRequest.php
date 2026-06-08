<?php

namespace App\Http\Requests\TableDefinition;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTableDefinitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'columns' => 'sometimes|array|min:1',
            'columns.*.name' => 'required_with:columns|string|max:255',
            'columns.*.type' => 'required_with:columns|string|max:255',
            'columns.*.pk' => 'nullable|boolean',
            'columns.*.fk' => 'nullable|boolean',
            'columns.*.unique' => 'nullable|boolean',
        ];
    }
}
