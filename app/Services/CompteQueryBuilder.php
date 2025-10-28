<?php

namespace App\Services;

use App\Models\Compte;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Service QueryBuilder spécialisé pour les comptes
 *
 * Gère la construction des requêtes avec filtres, recherche et tri
 * Respecte le principe de responsabilité unique
 */
class CompteQueryBuilder
{
    /**
     * Construire une requête de base pour les comptes
     */
    public function buildBaseQuery(bool $includeArchived = false): Builder
    {
        if ($includeArchived) {
            return Compte::onlyTrashed()->with(['client', 'transactions']);
        }

        return Compte::with(['client', 'transactions']);
    }

    /**
     * Appliquer les filtres à la requête
     */
    public function applyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $filter => $value) {
            switch ($filter) {
                case 'type':
                    $query->where('type', $value);
                    break;
                case 'statut':
                    $query->where('statut', $value);
                    break;
                case 'client_id':
                    $query->where('client_id', $value);
                    break;
                case 'numero':
                    $query->where('numero', $value);
                    break;
                case 'date_from':
                    $query->where('date_creation', '>=', $value);
                    break;
                case 'date_to':
                    $query->where('date_creation', '<=', $value);
                    break;
                case 'solde_min':
                    $query->whereRaw('(SELECT COALESCE(SUM(CASE WHEN type = "depot" THEN montant ELSE -montant END), 0) FROM transactions WHERE compte_id = comptes.id) >= ?', [$value]);
                    break;
                case 'solde_max':
                    $query->whereRaw('(SELECT COALESCE(SUM(CASE WHEN type = "depot" THEN montant ELSE -montant END), 0) FROM transactions WHERE compte_id = comptes.id) <= ?', [$value]);
                    break;
            }
        }

        return $query;
    }

    /**
     * Appliquer la recherche à la requête
     */
    public function applySearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('numero', 'like', "%{$search}%")
              ->orWhereHas('client', function ($clientQuery) use ($search) {
                  $clientQuery->where('nom', 'like', "%{$search}%")
                             ->orWhere('prenom', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%")
                             ->orWhere('telephone', 'like', "%{$search}%");
              });
        });
    }

    /**
     * Appliquer le tri à la requête
     */
    public function applySorting(Builder $query, string $sortField, string $sortOrder = 'desc'): Builder
    {
        switch ($sortField) {
            case 'dateCreation':
                return $query->orderBy('date_creation', $sortOrder);
            case 'solde':
                return $query->orderByRaw('(SELECT COALESCE(SUM(CASE WHEN type = "depot" THEN montant ELSE -montant END), 0) FROM transactions WHERE compte_id = comptes.id) ' . $sortOrder);
            case 'titulaire':
                return $query->join('clients', 'comptes.client_id', '=', 'clients.id')
                           ->orderByRaw('CONCAT(clients.prenom, " ", clients.nom) ' . $sortOrder)
                           ->select('comptes.*');
            case 'type':
                return $query->orderBy('type', $sortOrder);
            case 'statut':
                return $query->orderBy('statut', $sortOrder);
            case 'numero':
                return $query->orderBy('numero', $sortOrder);
            default:
                return $query->orderBy('created_at', $sortOrder);
        }
    }

    /**
     * Construire une requête complète avec tous les paramètres
     */
    public function buildFullQuery(Request $request, bool $includeArchived = false): Builder
    {
        $query = $this->buildBaseQuery($includeArchived);

        // Appliquer les filtres
        $filters = $this->extractFilters($request);
        if (!empty($filters)) {
            $query = $this->applyFilters($query, $filters);
        }

        // Appliquer la recherche
        if ($request->has('search')) {
            $query = $this->applySearch($query, $request->search);
        }

        // Appliquer le tri
        $sortField = $request->get('sort', 'dateCreation');
        $sortOrder = $request->get('order', 'desc');
        $query = $this->applySorting($query, $sortField, $sortOrder);

        return $query;
    }

    /**
     * Extraire les filtres de la requête
     */
    private function extractFilters(Request $request): array
    {
        $allowedFilters = [
            'type', 'statut', 'client_id', 'numero',
            'date_from', 'date_to', 'solde_min', 'solde_max'
        ];

        $filters = [];
        foreach ($allowedFilters as $filter) {
            if ($request->has($filter) && !empty($request->get($filter))) {
                $filters[$filter] = $request->get($filter);
            }
        }

        return $filters;
    }

    /**
     * Valider les paramètres de requête
     */
    public function validateParameters(Request $request): array
    {
        $errors = [];

        // Validation de la pagination
        if ($request->has('page') && (!is_numeric($request->page) || $request->page < 1)) {
            $errors['page'] = 'Le numéro de page doit être un entier positif';
        }

        if ($request->has('limit') && (!is_numeric($request->limit) || $request->limit < 1 || $request->limit > 100)) {
            $errors['limit'] = 'La limite doit être entre 1 et 100';
        }

        // Validation des filtres
        if ($request->has('type') && !in_array($request->type, ['epargne', 'cheque'])) {
            $errors['type'] = 'Le type doit être : epargne ou cheque';
        }

        if ($request->has('statut') && !in_array($request->statut, ['actif', 'bloque', 'ferme'])) {
            $errors['statut'] = 'Le statut doit être : actif, bloque ou ferme';
        }

        // Validation du tri
        if ($request->has('sort') && !in_array($request->sort, ['dateCreation', 'solde', 'titulaire', 'type', 'statut', 'numero'])) {
            $errors['sort'] = 'Le champ de tri n\'est pas valide';
        }

        if ($request->has('order') && !in_array($request->order, ['asc', 'desc'])) {
            $errors['order'] = 'L\'ordre doit être : asc ou desc';
        }

        return $errors;
    }
}
