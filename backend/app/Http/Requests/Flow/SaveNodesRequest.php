<?php

namespace App\Http\Requests\Flow;

use Illuminate\Foundation\Http\FormRequest;

class SaveNodesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nodes' => 'required|array',
            'nodes.*.id' => 'nullable|string',
            'nodes.*.type' => 'required|string',
            'nodes.*.label' => 'required|string',
            'nodes.*.position_x' => 'required|numeric',
            'nodes.*.position_y' => 'required|numeric',
            'nodes.*.data' => 'nullable|array',
            'nodes.*.config' => 'nullable|array',
        ];
    }
}
