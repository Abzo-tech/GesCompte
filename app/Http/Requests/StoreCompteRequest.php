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
            'soldeInitial' => ['sometimes', 'numeric', 'min:10000'],
            'devise' => ['required', 'string', 'max:4'],
            'client' => ['required', 'array'],
            'client.id' => ['sometimes', 'uuid', 'exists:clients,id'],
            'client.titulaire' => ['required_without:client.id', 'string', 'max:255'],
            'client.nci' => ['required_without:client.id', 'string', new \App\Rules\SenegaleseNCIRule],
            'client.email' => ['required_without:client.id', 'email', 'unique:clients,email'],
            'client.telephone' => ['required_without:client.id', new \App\Rules\SenegalesePhoneRule, 'unique:clients,telephone'],
            'client.adresse' => ['required_without:client.id', 'string', 'max:500'],
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
            'soldeInitial.numeric' => 'Le solde initial doit être un nombre.',
            'soldeInitial.min' => 'Le solde initial doit être d\'au moins 10 000 FCFA.',
            'devise.required' => 'La devise est obligatoire.',
            'devise.max' => 'La devise ne peut pas dépasser 4 caractères.',
            'client.required' => 'Les informations du client sont obligatoires.',
            'client.id.uuid' => 'L\'identifiant du client doit être un UUID valide.',
            'client.id.exists' => 'Le client spécifié n\'existe pas.',
            'client.titulaire.required_without' => 'Le nom du titulaire est obligatoire si aucun client existant n\'est spécifié.',
            'client.nci.required_without' => 'Le numéro NCI est obligatoire si aucun client existant n\'est spécifié.',
            'client.email.required_without' => 'L\'email est obligatoire si aucun client existant n\'est spécifié.',
            'client.email.email' => 'L\'email doit être une adresse email valide.',
            'client.email.unique' => 'Cet email est déjà utilisé.',
            'client.telephone.required_without' => 'Le numéro de téléphone est obligatoire si aucun client existant n\'est spécifié.',
            'client.telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'client.adresse.required_without' => 'L\'adresse est obligatoire si aucun client existant n\'est spécifié.',
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
            'type' => 'type de compte',
            'soldeInitial' => 'solde initial',
            'client.id' => 'identifiant client',
            'client.titulaire' => 'nom du titulaire',
            'client.nci' => 'numéro NCI',
            'client.email' => 'email',
            'client.telephone' => 'numéro de téléphone',
            'client.adresse' => 'adresse',
        ];
    }
}
