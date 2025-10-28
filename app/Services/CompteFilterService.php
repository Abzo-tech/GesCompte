<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Service de filtrage spécialisé pour les comptes
 *
 * Gère tous les types de filtres applicables aux comptes
 */
class CompteFilterService
{
    /**
     * Filtres disponibles avec leur configuration
     */
    private const AVAILABLE_FILTERS = [
        'type' => [
            'allowed_values' => ['epargne', 'cheque'],
            'default' => null,
            'multiple' => false
        ],
        'statut' => [
            'allowed_values' => ['actif', 'bloque', 'ferme'],
            'default' => 'actif',
            'multiple' => false
        ],
        'client_id' => [
            'allowed_values' => null, // UUID validation
            'default' => null,
            'multiple' => false
        ],
        'date_creation' => [
            'allowed_values' => null,
            'default' => null,
            'multiple' => true // from/to
        ],
        'solde' => [
            'allowed_values' => null,
            'default' => null,
            'multiple' => true // min/max
        ],
        'last_activity' => [
            'allowed_values' => null,
            'default' => null,
            'multiple' => true // from/to
        ]
    ];

    /**
     * Appliquer tous les filtres à une requête
     */
    public function applyFilters(Builder $query, Request $request): Builder
    {
        $filters = $this->extractFilters($request);

        foreach ($filters as $filter => $value) {
            $query = $this->applySingleFilter($query, $filter, $value);
        }

        return $query;
    }

    /**
     * Appliquer un filtre spécifique
     */
    private function applySingleFilter(Builder $query, string $filter, $value): Builder
    {
        switch ($filter) {
            case 'type':
                return $query->where('type', $value);

            case 'statut':
                return $query->where('statut', $value);

            case 'client_id':
                return $query->where('client_id', $value);

            case 'date_creation_from':
                return $query->where('date_creation', '>=', $value);

            case 'date_creation_to':
                return $query->where('date_creation', '<=', $value);

            case 'solde_min':
                return $query->whereRaw('(SELECT COALESCE(SUM(CASE WHEN type = "depot" THEN montant ELSE -montant END), 0) FROM transactions WHERE compte_id = comptes.id) >= ?', [$value]);

            case 'solde_max':
                return $query->whereRaw('(SELECT COALESCE(SUM(CASE WHEN type = "depot" THEN montant ELSE -montant END), 0) FROM transactions WHERE compte_id = comptes.id) <= ?', [$value]);

            case 'last_activity_from':
                return $query->whereHas('transactions', function ($q) use ($value) {
                    $q->where('created_at', '>=', $value);
                });

            case 'last_activity_to':
                return $query->whereHas('transactions', function ($q) use ($value) {
                    $q->where('created_at', '<=', $value);
                });

            case 'has_transactions':
                if ($value) {
                    return $query->whereHas('transactions');
                } else {
                    return $query->whereDoesntHave('transactions');
                }

            case 'no_activity_days':
                $dateThreshold = now()->subDays($value);
        return $query->whereDoesntHave('transactions', function ($q) use ($dateThreshold) {
            $q->where('created_at', '>=', $dateThreshold);
        });

            default:
                return $query;
        }
    }

    /**
     * Extraire les filtres de la requête
     */
    public function extractFilters(Request $request): array
    {
        $filters = [];

        foreach (self::AVAILABLE_FILTERS as $filter => $config) {
            if ($request->has($filter)) {
                $value = $request->get($filter);

                // Validation des valeurs autorisées
                if ($this->isValidFilterValue($filter, $value)) {
                    $filters[$filter] = $value;
                }
            }

            // Gestion des filtres multiples (from/to, min/max)
            if ($config['multiple']) {
                $this->extractMultipleFilters($request, $filter, $filters);
            }
        }

        return $filters;
    }

    /**
     * Extraire les filtres multiples (from/to, min/max)
     */
    private function extractMultipleFilters(Request $request, string $baseFilter, array &$filters): void
    {
        switch ($baseFilter) {
            case 'date_creation':
                if ($request->has('date_creation_from') || $request->has('date_creation_to')) {
                    $filters['date_creation_from'] = $request->get('date_creation_from');
                    $filters['date_creation_to'] = $request->get('date_creation_to');
                }
                break;

            case 'solde':
                if ($request->has('solde_min') || $request->has('solde_max')) {
                    $filters['solde_min'] = $request->get('solde_min');
                    $filters['solde_max'] = $request->get('solde_max');
                }
                break;

            case 'last_activity':
                if ($request->has('last_activity_from') || $request->has('last_activity_to')) {
                    $filters['last_activity_from'] = $request->get('last_activity_from');
                    $filters['last_activity_to'] = $request->get('last_activity_to');
                }
                break;
        }
    }

    /**
     * Valider une valeur de filtre
     */
    private function isValidFilterValue(string $filter, $value): bool
    {
        $config = self::AVAILABLE_FILTERS[$filter] ?? null;

        if (!$config) {
            return false;
        }

        // Validation pour les filtres avec valeurs prédéfinies
        if ($config['allowed_values'] !== null) {
            if (is_array($value)) {
                return empty(array_diff($value, $config['allowed_values']));
            }
            return in_array($value, $config['allowed_values']);
        }

        // Validation pour les UUID
        if ($filter === 'client_id') {
            return $this->isValidUUID($value);
        }

        // Validation pour les dates
        if (str_contains($filter, 'date')) {
            return $this->isValidDate($value);
        }

        // Validation pour les montants
        if (str_contains($filter, 'solde') || str_contains($filter, 'montant')) {
            return is_numeric($value);
        }

        return true;
    }

    /**
     * Valider un UUID
     */
    private function isValidUUID($value): bool
    {
        return is_string($value) && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value);
    }

    /**
     * Valider une date
     */
    private function isValidDate($value): bool
    {
        if (is_string($value)) {
            return Carbon::parse($value)->isValid();
        }
        return false;
    }

    /**
     * Obtenir la configuration des filtres disponibles
     */
    public function getAvailableFilters(): array
    {
        return self::AVAILABLE_FILTERS;
    }

    /**
     * Créer des filtres prédéfinis pour des cas d'usage courants
     */
    public function getPredefinedFilters(): array
    {
        return [
            'active_accounts' => ['statut' => 'actif'],
            'savings_accounts' => ['type' => 'epargne'],
            'checking_accounts' => ['type' => 'cheque'],
            'recent_accounts' => [
                'date_creation_from' => now()->subDays(30)->format('Y-m-d'),
                'date_creation_to' => now()->format('Y-m-d')
            ],
            'inactive_accounts' => ['no_activity_days' => 90],
            'high_balance' => ['solde_min' => 1000000], // 1M FCFA
            'low_balance' => ['solde_max' => 10000], // 10K FCFA
            'problematic_accounts' => [
                'statut' => 'bloque',
                'no_activity_days' => 30
            ]
        ];
    }

    /**
     * Appliquer un filtre prédéfini
     */
    public function applyPredefinedFilter(Builder $query, string $filterName): Builder
    {
        $predefinedFilters = $this->getPredefinedFilters();

        if (isset($predefinedFilters[$filterName])) {
            return $this->applyFilters($query, $predefinedFilters[$filterName]);
        }

        return $query;
    }

    /**
     * Obtenir les statistiques des filtres appliqués
     */
    public function getFilterStatistics(Builder $query): array
    {
        $baseCount = $query->count();

        $stats = [
            'total' => $baseCount,
            'by_type' => [],
            'by_statut' => [],
            'by_date' => []
        ];

        // Statistiques par type
        $stats['by_type'] = Compte::selectRaw('type, COUNT(*) as count')
            ->whereIn('id', $query->select('id'))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        // Statistiques par statut
        $stats['by_statut'] = Compte::selectRaw('statut, COUNT(*) as count')
            ->whereIn('id', $query->select('id'))
            ->groupBy('statut')
            ->pluck('count', 'statut')
            ->toArray();

        return $stats;
    }
}
