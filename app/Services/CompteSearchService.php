<?php

namespace App\Services;

use App\Models\Compte;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Service de recherche spécialisé pour les comptes
 *
 * Gère tous les types de recherche sur les comptes et clients
 */
class CompteSearchService
{
    /**
     * Rechercher des comptes par critères multiples
     */
    public function search(array $criteria): Collection
    {
        $query = Compte::with('client');

        // Recherche par numéro de compte
        if (isset($criteria['numero'])) {
            $query->where('numero', 'like', "%{$criteria['numero']}%");
        }

        // Recherche par type de compte
        if (isset($criteria['type'])) {
            $query->where('type', $criteria['type']);
        }

        // Recherche par statut
        if (isset($criteria['statut'])) {
            $query->where('statut', $criteria['statut']);
        }

        // Recherche par client
        if (isset($criteria['client_id'])) {
            $query->where('client_id', $criteria['client_id']);
        }

        // Recherche textuelle avancée
        if (isset($criteria['query'])) {
            $this->applyTextSearch($query, $criteria['query']);
        }

        // Recherche par solde
        if (isset($criteria['solde_min'])) {
            $query->whereRaw('(SELECT COALESCE(SUM(CASE WHEN type = "depot" THEN montant ELSE -montant END), 0) FROM transactions WHERE compte_id = comptes.id) >= ?', [$criteria['solde_min']]);
        }

        if (isset($criteria['solde_max'])) {
            $query->whereRaw('(SELECT COALESCE(SUM(CASE WHEN type = "depot" THEN montant ELSE -montant END), 0) FROM transactions WHERE compte_id = comptes.id) <= ?', [$criteria['solde_max']]);
        }

        // Recherche par date
        if (isset($criteria['date_from'])) {
            $query->where('date_creation', '>=', $criteria['date_from']);
        }

        if (isset($criteria['date_to'])) {
            $query->where('date_creation', '<=', $criteria['date_to']);
        }

        return $query->get();
    }

    /**
     * Recherche textuelle avancée
     */
    private function applyTextSearch(Builder $query, string $searchQuery): void
    {
        $query->where(function ($q) use ($searchQuery) {
            // Recherche dans le numéro de compte
            $q->where('numero', 'like', "%{$searchQuery}%")

              // Recherche dans les informations du client
              ->orWhereHas('client', function ($clientQuery) use ($searchQuery) {
                  $clientQuery->where('nom', 'like', "%{$searchQuery}%")
                             ->orWhere('prenom', 'like', "%{$searchQuery}%")
                             ->orWhere('email', 'like', "%{$searchQuery}%")
                             ->orWhere('telephone', 'like', "%{$searchQuery}%")
                             ->orWhereRaw("CONCAT(prenom, ' ', nom) LIKE ?", ["%{$searchQuery}%"])
                             ->orWhereRaw("CONCAT(nom, ' ', prenom) LIKE ?", ["%{$searchQuery}%"]);
              })

              // Recherche par montant approximatif
              ->orWhereRaw('(SELECT COUNT(*) FROM transactions WHERE compte_id = comptes.id AND montant LIKE ?) > 0', ["%{$searchQuery}%"]);
        });
    }

    /**
     * Recherche par similarité phonétique (soundex)
     */
    public function searchBySound(string $term): Collection
    {
        return Compte::with('client')
            ->whereHas('client', function ($query) use ($term) {
                $query->whereRaw('SOUNDEX(nom) = SOUNDEX(?)', [$term])
                      ->orWhereRaw('SOUNDEX(prenom) = SOUNDEX(?)', [$term]);
            })
            ->get();
    }

    /**
     * Recherche par proximité géographique (si adresse disponible)
     */
    public function searchByLocation(float $latitude, float $longitude, int $radiusKm = 10): Collection
    {
        // Note: Cette fonction nécessiterait une colonne de géolocalisation
        // Pour l'instant, on simule avec une recherche par ville/pays dans l'adresse
        return Compte::with('client')
            ->whereHas('client', function ($query) use ($latitude, $longitude, $radiusKm) {
                // Simulation - en production, utiliser une vraie géolocalisation
                $query->where('adresse', 'like', '%NY%'); // Exemple pour New York
            })
            ->get();
    }

    /**
     * Recherche par pattern de numéro de compte
     */
    public function searchByNumeroPattern(string $pattern): Collection
    {
        // Conversion du pattern en expression régulière
        $regexPattern = $this->convertToRegex($pattern);

        return Compte::with('client')
            ->where('numero', 'regexp', $regexPattern)
            ->get();
    }

    /**
     * Recherche par activité récente
     */
    public function searchByRecentActivity(int $days = 30): Collection
    {
        $dateThreshold = now()->subDays($days);

        return Compte::with('client')
            ->where('updated_at', '>=', $dateThreshold)
            ->orWhereHas('transactions', function ($query) use ($dateThreshold) {
                $query->where('created_at', '>=', $dateThreshold);
            })
            ->get();
    }

    /**
     * Recherche par inactivité
     */
    public function searchByInactivity(int $days = 90): Collection
    {
        $dateThreshold = now()->subDays($days);

        return Compte::with('client')
            ->where('updated_at', '<', $dateThreshold)
            ->whereDoesntHave('transactions', function ($query) use ($dateThreshold) {
                $query->where('created_at', '>=', $dateThreshold);
            })
            ->get();
    }

    /**
     * Recherche avancée avec scoring de pertinence
     */
    public function searchWithScoring(string $query, int $limit = 20): Collection
    {
        $results = Compte::with('client')
            ->where(function ($q) use ($query) {
                // Score élevé pour correspondance exacte
                $q->where('numero', $query)

                  // Score moyen pour correspondance partielle
                  ->orWhere('numero', 'like', "%{$query}%")

                  // Score pour correspondance client
                  ->orWhereHas('client', function ($clientQuery) use ($query) {
                      $clientQuery->where('nom', $query)
                                 ->orWhere('prenom', $query)
                                 ->orWhere('email', $query)
                                 ->orWhere('telephone', $query);
                  });
            })
            ->limit($limit)
            ->get();

        // Ajouter le score de pertinence
        return $results->map(function ($compte) use ($query) {
            $compte->search_score = $this->calculateRelevanceScore($compte, $query);
            return $compte;
        })->sortByDesc('search_score');
    }

    /**
     * Calculer le score de pertinence d'une recherche
     */
    private function calculateRelevanceScore(Compte $compte, string $query): float
    {
        $score = 0;

        // Score pour correspondance exacte du numéro
        if ($compte->numero === $query) {
            $score += 100;
        }

        // Score pour correspondance partielle du numéro
        if (str_contains($compte->numero, $query)) {
            $score += 50;
        }

        // Score pour correspondance client
        if ($compte->client) {
            if ($compte->client->nom === $query || $compte->client->prenom === $query) {
                $score += 80;
            }

            if (str_contains($compte->client->nom, $query) || str_contains($compte->client->prenom, $query)) {
                $score += 40;
            }

            if ($compte->client->email === $query) {
                $score += 60;
            }

            if ($compte->client->telephone === $query) {
                $score += 70;
            }
        }

        return $score;
    }

    /**
     * Convertir un pattern en expression régulière
     */
    private function convertToRegex(string $pattern): string
    {
        // Remplacer les caractères spéciaux par des expressions régulières
        $pattern = str_replace('*', '.*', $pattern);
        $pattern = str_replace('?', '.', $pattern);

        return '^' . $pattern . '$';
    }

    /**
     * Recherche par suggestions (autocomplétion)
     */
    public function getSuggestions(string $term, int $limit = 10): array
    {
        $suggestions = [];

        // Suggestions par numéro de compte
        $numeroSuggestions = Compte::where('numero', 'like', "{$term}%")
            ->limit($limit / 2)
            ->pluck('numero')
            ->toArray();
        $suggestions = array_merge($suggestions, $numeroSuggestions);

        // Suggestions par nom de client
        $clientSuggestions = Compte::with('client')
            ->whereHas('client', function ($query) use ($term) {
                $query->where('nom', 'like', "{$term}%")
                      ->orWhere('prenom', 'like', "{$term}%");
            })
            ->limit($limit / 2)
            ->get()
            ->map(function ($compte) {
                return $compte->client->prenom . ' ' . $compte->client->nom;
            })
            ->unique()
            ->toArray();
        $suggestions = array_merge($suggestions, $clientSuggestions);

        return array_unique($suggestions);
    }
}
