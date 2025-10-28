<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request de validation pour la liste des comptes
 */
class CompteListRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // L'authentification sera gérée par les middlewares
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
            'page' => 'sometimes|integer|min:1',
            'limit' => 'sometimes|integer|min:1|max:100',
            'type' => ['sometimes', 'string', Rule::in(['epargne', 'cheque'])],
            'statut' => ['sometimes', 'string', Rule::in(['actif', 'bloque', 'ferme'])],
            'search' => 'sometimes|string|min:1|max:100',
            'sort' => ['sometimes', 'string', Rule::in(['dateCreation', 'solde', 'titulaire', 'type', 'statut'])],
            'order' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
            'client_id' => 'sometimes|uuid|exists:clients,id',
            'numero' => 'sometimes|string|min:1|max:50',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'page.integer' => 'Le numéro de page doit être un entier positif',
            'page.min' => 'Le numéro de page doit être au minimum 1',
            'limit.integer' => 'La limite doit être un entier positif',
            'limit.min' => 'La limite doit être au minimum 1',
            'limit.max' => 'La limite ne peut pas dépasser 100 éléments',
            'type.in' => 'Le type doit être : epargne ou cheque',
            'statut.in' => 'Le statut doit être : actif, bloque ou ferme',
            'search.min' => 'La recherche doit contenir au moins 1 caractère',
            'search.max' => 'La recherche ne peut pas dépasser 100 caractères',
            'sort.in' => 'Le tri peut être fait par : dateCreation, solde, titulaire, type, statut',
            'order.in' => 'L\'ordre doit être : asc ou desc',
            'client_id.uuid' => 'L\'identifiant client n\'est pas valide',
            'client_id.exists' => 'Le client spécifié n\'existe pas',
            'numero.min' => 'Le numéro de compte doit contenir au moins 1 caractère',
            'numero.max' => 'Le numéro de compte ne peut pas dépasser 50 caractères',
        ];
    }

    /**
     * Get the validated data from the request.
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated();

        // Valeurs par défaut
        $validated['page'] = $validated['page'] ?? 1;
        $validated['limit'] = $validated['limit'] ?? 10;
        $validated['order'] = $validated['order'] ?? 'desc';
        $validated['sort'] = $validated['sort'] ?? 'dateCreation';

        return $validated;
    }

    /**
     * Vérifier si la requête contient des filtres
     */
    public function hasFilters(): bool
    {
        return $this->hasAny(['type', 'statut', 'search', 'client_id', 'numero']);
    }

    /**
     * Obtenir les filtres appliqués
     */
    public function getFilters(): array
    {
        return array_filter([
            'type' => $this->input('type'),
            'statut' => $this->input('statut'),
            'search' => $this->input('search'),
            'client_id' => $this->input('client_id'),
            'numero' => $this->input('numero'),
        ], function ($value) {
            return !empty($value);
        });
    }
}
