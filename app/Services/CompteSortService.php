<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Service de tri spécialisé pour les comptes
 *
 * Gère tous les types de tri possibles sur les comptes
 */
class CompteSortService
{
    /**
     * Champs de tri disponibles avec leur configuration
     */
    private const SORTABLE_FIELDS = [
        'id' => ['column' => 'id', 'type' => 'integer'],
        'numero' => ['column' => 'numero', 'type' => 'string'],
        'type' => ['column' => 'type', 'type' => 'string'],
        'statut' => ['column' => 'statut', 'type' => 'string'],
        'dateCreation' => ['column' => 'date_creation', 'type' => 'date'],
        'dateModification' => ['column' => 'updated_at', 'type' => 'date'],
        'solde' => ['column' => null, 'type' => 'calculated', 'method' => 'calculateSolde'],
        'titulaire' => ['column' => null, 'type' => 'calculated', 'method' => 'getTitulaire'],
        'dernierDepot' => ['column' => null, 'type' => 'calculated', 'method' => 'getLastDepot'],
        'dernierRetrait' => ['column' => null, 'type' => 'calculated', 'method' => 'getLastRetrait'],
        'nombreTransactions' => ['column' => null, 'type' => 'calculated', 'method' => 'getTransactionCount'],
        'clientNom' => ['column' => null, 'type' => 'relation', 'relation' => 'client.nom'],
        'clientPrenom' => ['column' => null, 'type' => 'relation', 'relation' => 'client.prenom'],
        'clientEmail' => ['column' => null, 'type' => 'relation', 'relation' => 'client.email'],
    ];

    /**
     * Appliquer le tri à une requête
     */
    public function applySorting(Builder $query, string $sortField, string $sortDirection = 'asc'): Builder
    {
        $sortDirection = strtolower($sortDirection);
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'asc';
        }

        $fieldConfig = self::SORTABLE_FIELDS[$sortField] ?? null;

        if (!$fieldConfig) {
            // Tri par défaut
            return $query->orderBy('date_creation', $sortDirection);
        }

        return match ($fieldConfig['type']) {
            'integer', 'string', 'date' => $query->orderBy($fieldConfig['column'], $sortDirection),
            'calculated' => $this->applyCalculatedSort($query, $fieldConfig['method'], $sortDirection),
            'relation' => $this->applyRelationSort($query, $fieldConfig['relation'], $sortDirection),
            default => $query->orderBy('date_creation', $sortDirection)
        };
    }

    /**
     * Appliquer le tri sur un champ calculé
     */
    private function applyCalculatedSort(Builder $query, string $method, string $direction): Builder
    {
        switch ($method) {
            case 'calculateSolde':
                return $query->orderByRaw(
                    '(SELECT COALESCE(SUM(CASE WHEN type = "depot" THEN montant ELSE -montant END), 0) FROM transactions WHERE compte_id = comptes.id) ' . $direction
                );

            case 'getTitulaire':
                return $query->join('clients', 'comptes.client_id', '=', 'clients.id')
                           ->orderByRaw('CONCAT(clients.prenom, " ", clients.nom) ' . $direction)
                           ->select('comptes.*');

            case 'getLastDepot':
                return $query->leftJoin('transactions', function ($join) {
                    $join->on('comptes.id', '=', 'transactions.compte_id')
                         ->where('transactions.type', '=', 'depot');
                })
                ->orderByRaw('MAX(transactions.created_at) ' . $direction)
                ->select('comptes.*')
                ->groupBy('comptes.id');

            case 'getLastRetrait':
                return $query->leftJoin('transactions', function ($join) {
                    $join->on('comptes.id', '=', 'transactions.compte_id')
                         ->whereIn('transactions.type', ['retrait', 'virement', 'paiement']);
                })
                ->orderByRaw('MAX(transactions.created_at) ' . $direction)
                ->select('comptes.*')
                ->groupBy('comptes.id');

            case 'getTransactionCount':
                return $query->withCount('transactions')
                           ->orderBy('transactions_count', $direction);

            default:
                return $query->orderBy('date_creation', $direction);
        }
    }

    /**
     * Appliquer le tri sur un champ de relation
     */
    private function applyRelationSort(Builder $query, string $relationField, string $direction): Builder
    {
        [$relation, $field] = explode('.', $relationField);

        return $query->join($relation, "comptes.{$relation}_id", '=', "{$relation}.id")
                   ->orderBy("{$relation}.{$field}", $direction)
                   ->select('comptes.*');
    }

    /**
     * Appliquer plusieurs tris
     */
    public function applyMultipleSorting(Builder $query, array $sortFields): Builder
    {
        foreach ($sortFields as $field => $direction) {
            if (is_numeric($field)) {
                // Format ['dateCreation', 'desc']
                $field = $direction;
                $direction = 'asc';
            }
            $query = $this->applySorting($query, $field, $direction);
        }

        return $query;
    }

    /**
     * Obtenir les champs de tri disponibles
     */
    public function getAvailableSortFields(): array
    {
        return array_keys(self::SORTABLE_FIELDS);
    }

    /**
     * Valider un champ de tri
     */
    public function isValidSortField(string $field): bool
    {
        return isset(self::SORTABLE_FIELDS[$field]);
    }

    /**
     * Obtenir la configuration d'un champ de tri
     */
    public function getSortFieldConfig(string $field): ?array
    {
        return self::SORTABLE_FIELDS[$field] ?? null;
    }

    /**
     * Tri intelligent par pertinence
     */
    public function applySmartSort(Builder $query, string $context = 'default'): Builder
    {
        return match ($context) {
            'client_view' => $this->applyClientViewSort($query),
            'admin_dashboard' => $this->applyAdminDashboardSort($query),
            'audit' => $this->applyAuditSort($query),
            'problematic' => $this->applyProblematicSort($query),
            default => $query->orderBy('date_creation', 'desc')
        };
    }

    /**
     * Tri optimisé pour la vue client
     */
    private function applyClientViewSort(Builder $query): Builder
    {
        return $query->orderBy('statut', 'asc') // Actifs en premier
                   ->orderBy('type', 'asc') // Epargne puis cheque
                   ->orderBy('date_creation', 'desc');
    }

    /**
     * Tri optimisé pour le dashboard admin
     */
    private function applyAdminDashboardSort(Builder $query): Builder
    {
        return $query->orderByRaw('
            CASE
                WHEN statut = "bloque" THEN 1
                WHEN statut = "ferme" THEN 2
                ELSE 3
            END ASC
        ')
        ->orderBy('date_creation', 'desc');
    }

    /**
     * Tri pour l'audit (par activité récente)
     */
    private function applyAuditSort(Builder $query): Builder
    {
        return $query->leftJoin('transactions', 'comptes.id', '=', 'transactions.compte_id')
                   ->orderByRaw('MAX(transactions.created_at) DESC NULLS LAST')
                   ->select('comptes.*')
                   ->groupBy('comptes.id');
    }

    /**
     * Tri pour les comptes problématiques
     */
    private function applyProblematicSort(Builder $query): Builder
    {
        return $query->orderByRaw('
            CASE
                WHEN statut = "bloque" THEN 1
                WHEN statut = "ferme" THEN 2
                WHEN (SELECT COUNT(*) FROM transactions WHERE compte_id = comptes.id AND created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)) = 0 THEN 3
                ELSE 4
            END ASC
        ')
        ->orderBy('date_creation', 'desc');
    }

    /**
     * Tri personnalisé avec fonction de callback
     */
    public function applyCustomSort(Builder $query, callable $sortFunction): Builder
    {
        return $sortFunction($query);
    }

    /**
     * Obtenir les options de tri par défaut pour différents contextes
     */
    public function getDefaultSortOptions(): array
    {
        return [
            'default' => ['dateCreation', 'desc'],
            'client' => ['statut', 'asc'],
            'admin' => ['date_creation', 'desc'],
            'recent' => ['date_creation', 'desc'],
            'balance' => ['solde', 'desc'],
            'alphabetical' => ['titulaire', 'asc']
        ];
    }

    /**
     * Appliquer le tri par défaut pour un contexte
     */
    public function applyDefaultSort(Builder $query, string $context = 'default'): Builder
    {
        $defaultOptions = $this->getDefaultSortOptions();
        $sortOption = $defaultOptions[$context] ?? $defaultOptions['default'];

        return $this->applySorting($query, $sortOption[0], $sortOption[1]);
    }
}
