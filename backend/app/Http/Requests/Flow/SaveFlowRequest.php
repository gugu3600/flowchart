<?php

namespace App\Http\Requests\Flow;

use Illuminate\Foundation\Http\FormRequest;

class SaveFlowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nodes' => 'nullable|array',
            'nodes.*.id' => 'nullable|string',
            'nodes.*.type' => 'required_with:nodes|string',
            'nodes.*.label' => 'required_with:nodes|string',
            'nodes.*.position_x' => 'required_with:nodes|numeric',
            'nodes.*.position_y' => 'required_with:nodes|numeric',
            'nodes.*.data' => 'nullable|array',
            'nodes.*.config' => 'nullable|array',
            'edges' => 'nullable|array',
            'edges.*.source' => 'required_with:edges|string',
            'edges.*.target' => 'required_with:edges|string',
            'edges.*.label' => 'nullable|string',
            'edges.*.animated' => 'nullable|boolean',
            'edges.*.config' => 'nullable|array',
        ];
    }
}
