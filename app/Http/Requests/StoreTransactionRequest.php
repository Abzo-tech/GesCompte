<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
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
            'type' => ['required', 'string', 'in:depot,retrait,virement,paiement'],
            'montant' => ['required', 'numeric', 'min:0.01'],
            'description' => ['sometimes', 'string', 'max:255'],
            'beneficiaire' => ['sometimes', 'string', 'max:100'],
            'date_transaction' => ['sometimes', 'date'],
            'compte_id' => ['required', 'uuid', 'exists:comptes,id'],
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
            'type.required' => 'Le type de transaction est obligatoire.',
            'type.in' => 'Le type de transaction doit être : depot, retrait, virement ou paiement.',
            'montant.required' => 'Le montant est obligatoire.',
            'montant.numeric' => 'Le montant doit être un nombre.',
            'montant.min' => 'Le montant doit être supérieur à 0.',
            'description.max' => 'La description ne peut pas dépasser 255 caractères.',
            'beneficiaire.max' => 'Le nom du bénéficiaire ne peut pas dépasser 100 caractères.',
            'date_transaction.date' => 'La date de transaction n\'est pas valide.',
            'compte_id.required' => 'L\'identifiant du compte est obligatoire.',
            'compte_id.uuid' => 'L\'identifiant du compte doit être un UUID valide.',
            'compte_id.exists' => 'Le compte spécifié n\'existe pas.',
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
            'compte_id' => 'compte',
            'date_transaction' => 'date de transaction',
        ];
    }
}
