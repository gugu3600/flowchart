<?php

namespace App\Http\Requests\Flow;

use Illuminate\Foundation\Http\FormRequest;

class ValidateConnectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source_id' => 'required|string',
            'target_id' => 'required|string',
            'source_type' => 'required|string',
            'target_type' => 'required|string',
        ];
    }
}
