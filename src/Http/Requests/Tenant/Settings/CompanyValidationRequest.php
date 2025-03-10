<?php

namespace Callcocam\Raptor\Http\Requests\Tenant\Settings;

use Illuminate\Foundation\Http\FormRequest;

class CompanyValidationRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'cnpj' => 'required|cnpj', // Using our custom CNPJ validation
            'cpf' => 'sometimes|cpf',  // Using our custom CPF validation
            // Other validation rules
        ];
    }
    
    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome da empresa é obrigatório',
            'cnpj.required' => 'O CNPJ é obrigatório',
            // The custom message for cnpj.cnpj is defined in the validator
        ];
    }
}
