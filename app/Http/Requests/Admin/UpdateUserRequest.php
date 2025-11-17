<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo('users.edit') ?? false;
    }

    /**
     * @return array<string, array<int, string|\Illuminate\Validation\Rules\Enum>>
     */
    public function rules(): array
    {
        $user = $this->route('user');
        $userId = is_object($user) && method_exists($user, 'getKey') ? $user->getKey() : $user;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($userId)],
            'role' => ['sometimes', 'required', Rule::enum(UserRole::class)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
