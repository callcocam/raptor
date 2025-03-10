<?php

namespace Callcocam\Raptor\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $this->route('user'),
            'password' => 'nullable|string|min:8|confirmed',
            'status' => 'required|string',
            'address' => 'nullable|string|max:255',
            'default_address' => 'sometimes|required|array',
            'default_address.id' => 'sometimes|nullable|string',
            'default_address.zip_code' => 'sometimes|required|string|max:10',
            'default_address.street' => 'sometimes|required|string|max:255',
            'default_address.city' => 'sometimes|required|string|max:255',
            'default_address.state' => 'sometimes|required|string|max:255', 
            'default_address.country' => 'sometimes|required|string|max:255',
            'default_address.number' => 'sometimes|required|string|max:10',
            'default_address.complement' => 'sometimes|nullable|string|max:255',
            'default_address.district' => 'sometimes|required|string|max:255',
            'default_address.is_default' => 'sometimes|required|boolean',
        ];
    }
}
