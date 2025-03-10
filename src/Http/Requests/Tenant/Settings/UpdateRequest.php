<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\Raptor\Http\Requests\Tenant\Settings;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateRequest
 * 
 * Classe de validação para requisições de atualização de registros Tenant.
 * Define regras de validação e mensagens personalizadas.
 */
class UpdateRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer esta requisição.
     * 
     * @return bool Retorna true se autorizado, false caso contrário
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Define as regras de validação aplicáveis à requisição.
     * 
     * Note que para atualização, a regra 'sometimes' é aplicada a campos que
     * podem estar ausentes na requisição, sendo validados apenas se presentes.
     * 
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255',
            'document' => 'sometimes|required|cnpj',
            'phone' => 'sometimes|required|phone',
            'description' => 'nullable|string',
            'status' => 'sometimes|required|string',
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

            // Adicione mais regras de validação conforme necessário
        ];
    }
    
    /**
     * Define mensagens personalizadas para erros de validação.
     * 
     * @return array Mensagens personalizadas
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório',
            'name.max' => 'O nome não pode ter mais de :max caracteres',
            'status.required' => 'O status é obrigatório',
            'default_address.zip_code.required' => 'O CEP é obrigatório',
            'default_address.street.required' => 'A rua é obrigatória',
            'default_address.city.required' => 'A cidade é obrigatória',
            'default_address.state.required' => 'O estado é obrigatório',
            'default_address.country.required' => 'O país é obrigatório',
            'default_address.number.required' => 'O número é obrigatório',
            'default_address.district.required' => 'O bairro é obrigatório',
            'default_address.is_default.required' => 'O campo padrão é obrigatório',
            'default_address.is_default.boolean' => 'O campo padrão deve ser verdadeiro ou falso',
            'default_address.complement.max' => 'O complemento não pode ter mais de :max caracteres',
            'default_address.zip_code.max' => 'O CEP não pode ter mais de :max caracteres',
            'default_address.street.max' => 'A rua não pode ter mais de :max caracteres',
            'default_address.city.max' => 'A cidade não pode ter mais de :max caracteres',
            'default_address.state.max' => 'O estado não pode ter mais de :max caracteres',
            'default_address.country.max' => 'O país não pode ter mais de :max caracteres',
            'default_address.number.max' => 'O número não pode ter mais de :max caracteres',
            'default_address.district.max' => 'O bairro não pode ter mais de :max caracteres',
            'default_address.complement.string' => 'O complemento deve ser uma string',
            'default_address.zip_code.string' => 'O CEP deve ser uma string',
            'default_address.street.string' => 'A rua deve ser uma string',
            'default_address.city.string' => 'A cidade deve ser uma string',
            'default_address.state.string' => 'O estado deve ser uma string',
            'default_address.country.string' => 'O país deve ser uma string',
            'default_address.number.string' => 'O número deve ser uma string',
            'default_address.district.string' => 'O bairro deve ser uma string', 
            // Adicione mais mensagens personalizadas conforme necessário
        ];
    }
    
    /**
     * Opcionalmente, você pode preparar os dados antes da validação
     * sobrecarregando o método prepareForValidation() aqui
     * 
     * protected function prepareForValidation(): void
     * {
     *     $this->merge([
     *         'field' => transform_field($this->field),
     *     ]);
     * }
     */
}
