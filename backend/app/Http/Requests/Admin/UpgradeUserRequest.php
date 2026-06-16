<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UpgradeUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tier' => ['required', 'string', Rule::in(
                Role::where('guard_name', 'api')->where('name', '!=', 'super-admin')->pluck('name')
            )],
        ];
    }
}
