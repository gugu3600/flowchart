<?php

namespace App\Http\Requests\Flow;

use Illuminate\Foundation\Http\FormRequest;

class SaveEdgesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'edges' => 'required|array',
            'edges.*.source_node_id' => 'required|integer|exists:flow_nodes,id',
            'edges.*.target_node_id' => 'required|integer|exists:flow_nodes,id',
            'edges.*.label' => 'nullable|string',
            'edges.*.config' => 'nullable|array',
        ];
    }
}
