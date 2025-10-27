<?php

namespace App\Validations;

class ClientValidation
{
    /**
     * Valider les données de création d'un client
     */
    public static function validateCreate(array $data): array
    {
        $errors = [];

        // Validation nom
        if (empty($data['nom'])) {
            $errors['nom'] = 'Le nom est obligatoire';
        } elseif (strlen($data['nom']) > 255) {
            $errors['nom'] = 'Le nom ne peut pas dépasser 255 caractères';
        }

        // Validation prenom
        if (empty($data['prenom'])) {
            $errors['prenom'] = 'Le prénom est obligatoire';
        } elseif (strlen($data['prenom']) > 255) {
            $errors['prenom'] = 'Le prénom ne peut pas dépasser 255 caractères';
        }

        // Validation email
        if (empty($data['email'])) {
            $errors['email'] = 'L\'email est obligatoire';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'L\'email n\'est pas valide';
        } elseif (strlen($data['email']) > 255) {
            $errors['email'] = 'L\'email ne peut pas dépasser 255 caractères';
        }

        // Validation téléphone (optionnel)
        if (!empty($data['telephone']) && strlen($data['telephone']) > 20) {
            $errors['telephone'] = 'Le téléphone ne peut pas dépasser 20 caractères';
        }

        // Validation adresse (optionnel)
        if (!empty($data['adresse']) && strlen($data['adresse']) > 1000) {
            $errors['adresse'] = 'L\'adresse ne peut pas dépasser 1000 caractères';
        }

        // Validation statut
        $allowedStatuses = ['actif', 'inactif', 'suspendu'];
        if (!empty($data['statut']) && !in_array($data['statut'], $allowedStatuses)) {
            $errors['statut'] = 'Le statut doit être : ' . implode(', ', $allowedStatuses);
        }

        return $errors;
    }

    /**
     * Valider les données de mise à jour d'un client
     */
    public static function validateUpdate(array $data): array
    {
        $errors = [];

        // Validation nom (optionnel pour update)
        if (isset($data['nom'])) {
            if (strlen($data['nom']) > 255) {
                $errors['nom'] = 'Le nom ne peut pas dépasser 255 caractères';
            }
        }

        // Validation prenom (optionnel pour update)
        if (isset($data['prenom'])) {
            if (strlen($data['prenom']) > 255) {
                $errors['prenom'] = 'Le prénom ne peut pas dépasser 255 caractères';
            }
        }

        // Validation email (optionnel pour update)
        if (isset($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'L\'email n\'est pas valide';
            } elseif (strlen($data['email']) > 255) {
                $errors['email'] = 'L\'email ne peut pas dépasser 255 caractères';
            }
        }

        // Validation téléphone (optionnel)
        if (isset($data['telephone']) && strlen($data['telephone']) > 20) {
            $errors['telephone'] = 'Le téléphone ne peut pas dépasser 20 caractères';
        }

        // Validation adresse (optionnel)
        if (isset($data['adresse']) && strlen($data['adresse']) > 1000) {
            $errors['adresse'] = 'L\'adresse ne peut pas dépasser 1000 caractères';
        }

        // Validation statut
        if (isset($data['statut'])) {
            $allowedStatuses = ['actif', 'inactif', 'suspendu'];
            if (!in_array($data['statut'], $allowedStatuses)) {
                $errors['statut'] = 'Le statut doit être : ' . implode(', ', $allowedStatuses);
            }
        }

        return $errors;
    }

    /**
     * Vérifier si l'email est unique (pour création et mise à jour)
     */
    public static function isEmailUnique(string $email, ?string $excludeId = null): bool
    {
        $query = \App\Models\Client::where('email', $email);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->count() === 0;
    }
}
