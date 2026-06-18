<?php

namespace App\Http\Requests\TableDefinition;

use Illuminate\Foundation\Http\FormRequest;

class StoreTableDefinitionRequest extends FormRequest
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
            'columns' => 'required|array|min:1',
            'columns.*.name' => 'required|string|max:255',
            'columns.*.type' => 'required|string|max:255',
            'columns.*.pk' => 'nullable|boolean',
            'columns.*.fk' => 'nullable|boolean',
            'columns.*.unique' => 'nullable|boolean',
        ];
    }
}
