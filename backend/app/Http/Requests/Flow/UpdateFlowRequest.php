<?php

namespace App\Http\Requests\Flow;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFlowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'config' => 'nullable|array',
        ];
    }
}
