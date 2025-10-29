<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends FormRequest
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
            'titulaire' => 'sometimes|string|max:255',
            'informationsClient' => 'sometimes|array',
            'informationsClient.telephone' => [
                'sometimes',
                'nullable',
                'string',
                Rule::unique('clients', 'telephone')->ignore($this->route('compte')->client_id ?? null, 'id'),
                new \App\Rules\SenegalesePhoneRule()
            ],
            'informationsClient.email' => [
                'sometimes',
                'nullable',
                'email',
                Rule::unique('clients', 'email')->ignore($this->route('compte')->client_id ?? null, 'id')
            ],
            'informationsClient.password' => 'sometimes|nullable|string|min:8',
            'informationsClient.nci' => [
                'sometimes',
                'nullable',
                'string',
                Rule::unique('clients', 'nci')->ignore($this->route('compte')->client_id ?? null, 'id'),
                new \App\Rules\SenegaleseNCIRule()
            ],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Vérifier qu'au moins un champ est fourni
            $hasTitulaire = $this->filled('titulaire');
            $hasClientInfo = $this->filled('informationsClient') &&
                           collect($this->input('informationsClient', []))->filter()->isNotEmpty();

            if (!$hasTitulaire && !$hasClientInfo) {
                $validator->errors()->add('general', 'Au moins un champ doit être fourni pour la mise à jour.');
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'titulaire.string' => 'Le nom du titulaire doit être une chaîne de caractères.',
            'titulaire.max' => 'Le nom du titulaire ne peut pas dépasser 255 caractères.',
            'informationsClient.telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'informationsClient.email.email' => 'L\'adresse email n\'est pas valide.',
            'informationsClient.email.unique' => 'Cette adresse email est déjà utilisée.',
            'informationsClient.password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'informationsClient.nci.unique' => 'Ce numéro NCI est déjà utilisé.',
            'general' => 'Au moins un champ doit être fourni pour la mise à jour.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'titulaire' => 'nom du titulaire',
            'informationsClient.telephone' => 'numéro de téléphone',
            'informationsClient.email' => 'adresse email',
            'informationsClient.password' => 'mot de passe',
            'informationsClient.nci' => 'numéro NCI',
        ];
    }
}