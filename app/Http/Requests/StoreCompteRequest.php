<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompteRequest extends FormRequest
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
            'type' => ['required', 'string', 'in:epargne,cheque,courant'],
            'statut' => ['sometimes', 'string', 'in:actif,bloque,ferme'],
            'solde' => ['sometimes', 'numeric', 'min:0'],
            'client_id' => ['required', 'uuid', 'exists:clients,id'],
            'numero' => ['sometimes', 'string', 'unique:comptes,numero', 'max:20'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Le type de compte est obligatoire.',
            'type.in' => 'Le type de compte doit être : epargne, cheque ou courant.',
            'statut.in' => 'Le statut doit être : actif, bloque ou ferme.',
            'solde.numeric' => 'Le solde doit être un nombre.',
            'solde.min' => 'Le solde ne peut pas être négatif.',
            'client_id.required' => 'L\'identifiant du client est obligatoire.',
            'client_id.uuid' => 'L\'identifiant du client doit être un UUID valide.',
            'client_id.exists' => 'Le client spécifié n\'existe pas.',
            'numero.unique' => 'Ce numéro de compte existe déjà.',
            'numero.max' => 'Le numéro de compte ne peut pas dépasser 20 caractères.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'client_id' => 'client',
            'numero' => 'numéro de compte',
        ];
    }
}
