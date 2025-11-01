<?php

namespace App\Http\Controllers\Api\V1;

/**
 * @OA\Schema(
 *     schema="CompteRequest",
 *     title="Compte Request",
 *     description="Compte creation/update request",
 *     @OA\Property(property="numero", type="string", nullable=true, description="Numéro du compte"),
 *     @OA\Property(property="type", type="string", enum={"courant","epargne","cheque"}, description="Type de compte"),
 *     @OA\Property(property="statut", type="string", enum={"actif","bloque","ferme"}, nullable=true, description="Statut du compte"),
 *     @OA\Property(property="client_id", type="string", format="uuid", nullable=true, description="ID du client"),
 *     @OA\Property(property="devise", type="string", maxLength=4, description="Devise du compte"),
 *     @OA\Property(property="date_creation", type="string", format="date-time", description="Date de création")
 * )
 */

/**
 *     @OA\Schema(
 *     schema="Compte",
 *     title="Compte",
 *     description="Objet représentant un compte bancaire avec toutes ses propriétés",
 *     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000", description="Identifiant unique du compte"),
 *     @OA\Property(property="numero", type="string", example="C00123456", description="Numéro du compte généré automatiquement"),
 *     @OA\Property(property="type", type="string", enum={"courant","epargne","cheque"}, example="cheque", description="Type de compte bancaire"),
 *     @OA\Property(property="statut", type="string", enum={"actif","bloque","ferme"}, example="actif", description="Statut actuel du compte"),
 *     @OA\Property(property="solde", type="number", format="decimal", example=150000.50, description="Solde actuel du compte"),
 *     @OA\Property(property="client_id", type="string", format="uuid", description="Référence vers le client propriétaire"),
 *     @OA\Property(property="devise", type="string", example="FCFA", description="Devise du compte"),
 *     @OA\Property(property="date_creation", type="string", format="date-time", description="Date de création du compte"),
 *     @OA\Property(property="date_blocage", type="string", format="date-time", nullable=true, description="Date de blocage du compte"),
 *     @OA\Property(property="date_deblocage_prevue", type="string", format="date-time", nullable=true, description="Date prévue de déblocage"),
 *     @OA\Property(property="motif_blocage", type="string", nullable=true, description="Motif du blocage du compte"),
 *     @OA\Property(property="motif_deblocage", type="string", nullable=true, description="Motif du déblocage du compte"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, description="Date de suppression (soft delete)"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Date de création en base"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Date de dernière modification"),
 *     @OA\Property(
 *         property="client",
 *         ref="#/components/schemas/Client",
 *         description="Informations du client propriétaire du compte"
 *     ),
 *     @OA\Property(
 *         property="transactions",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Transaction"),
 *         description="Liste des transactions du compte"
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="Client",
 *     title="Client",
 *     description="Objet représentant un client bancaire avec toutes ses informations personnelles",
 *     @OA\Property(property="id", type="string", format="uuid", example="ffb7b99a-9aa2-41c9-bfe8-6b4fc81274db", description="Identifiant unique du client"),
 *     @OA\Property(property="nom", type="string", example="Diallo", description="Nom de famille du client"),
 *     @OA\Property(property="prenom", type="string", example="Amadou", description="Prénom du client"),
 *     @OA\Property(property="full_name", type="string", example="Amadou Diallo", description="Nom complet du client"),
 *     @OA\Property(property="email", type="string", format="email", example="amadou.diallo@email.com", description="Adresse email du client"),
 *     @OA\Property(property="telephone", type="string", example="+221771234567", description="Numéro de téléphone sénégalais"),
 *     @OA\Property(property="adresse", type="string", nullable=true, example="123 Rue de la Paix, Dakar, Sénégal", description="Adresse complète du client"),
 *     @OA\Property(property="statut", type="string", enum={"actif","inactif","suspendu"}, example="actif", description="Statut du client"),
 *     @OA\Property(property="nci", type="string", nullable=true, example="1234567890123456", description="Numéro de Carte Nationale d'Identité"),
 *     @OA\Property(property="date_naissance", type="string", format="date", nullable=true, example="1990-05-15", description="Date de naissance"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Date de création du compte client"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Date de dernière modification")
 * )
 */

/**
 * @OA\Schema(
 *     schema="Transaction",
 *     title="Transaction",
 *     description="Objet représentant une transaction bancaire",
 *     @OA\Property(property="id", type="string", format="uuid", description="Identifiant unique de la transaction"),
 *     @OA\Property(property="reference", type="string", example="TXN-20250101-001", description="Référence unique de la transaction"),
 *     @OA\Property(property="type", type="string", enum={"depot","retrait","virement"}, example="depot", description="Type de transaction"),
 *     @OA\Property(property="montant", type="number", format="decimal", example=50000.00, description="Montant de la transaction"),
 *     @OA\Property(property="description", type="string", example="Dépôt initial", description="Description de la transaction"),
 *     @OA\Property(property="date_transaction", type="string", format="date-time", description="Date et heure de la transaction"),
 *     @OA\Property(property="compte_id", type="string", format="uuid", description="Référence vers le compte concerné"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Date de création en base"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Date de dernière modification")
 * )
 */

/**
 * @OA\Schema(
 *     schema="CompteApiCollection",
 *     title="Compte API Collection",
 *     description="Collection of comptes with pagination",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Compte")
 *     ),
 *     @OA\Property(
 *         property="pagination",
 *         type="object",
 *         @OA\Property(property="currentPage", type="integer", example=1),
 *         @OA\Property(property="totalPages", type="integer", example=1),
 *         @OA\Property(property="totalItems", type="integer", example=10),
 *         @OA\Property(property="itemsPerPage", type="integer", example=10),
 *         @OA\Property(property="hasNext", type="boolean", example=false),
 *         @OA\Property(property="hasPrevious", type="boolean", example=false)
 *     ),
 *     @OA\Property(
 *         property="links",
 *         type="object",
 *         @OA\Property(property="self", type="string", format="uri"),
 *         @OA\Property(property="next", type="string", format="uri", nullable=true),
 *         @OA\Property(property="previous", type="string", format="uri", nullable=true),
 *         @OA\Property(property="first", type="string", format="uri"),
 *         @OA\Property(property="last", type="string", format="uri")
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     title="Error Response",
 *     description="Réponse d'erreur générique pour toutes les API",
 *     @OA\Property(property="success", type="boolean", example=false, description="Indicateur de succès (toujours false pour les erreurs)"),
 *     @OA\Property(
 *         property="error",
 *         type="object",
 *         description="Détails de l'erreur",
 *         @OA\Property(property="code", type="string", example="VALIDATION_ERROR", description="Code d'erreur unique"),
 *         @OA\Property(property="message", type="string", example="Les données fournies sont invalides", description="Message d'erreur lisible"),
 *         @OA\Property(property="details", type="object", nullable=true, description="Détails supplémentaires de l'erreur (champs spécifiques, etc.)")
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="SuccessResponse",
 *     title="Success Response",
 *     description="Réponse de succès générique pour toutes les API",
 *     @OA\Property(property="success", type="boolean", example=true, description="Indicateur de succès"),
 *     @OA\Property(property="message", type="string", example="Opération réalisée avec succès", description="Message de confirmation"),
 *     @OA\Property(property="data", type="object", nullable=true, description="Données de réponse"),
 *     @OA\Property(
 *         property="pagination",
 *         type="object",
 *         nullable=true,
 *         description="Informations de pagination (pour les listes)",
 *         @OA\Property(property="currentPage", type="integer"),
 *         @OA\Property(property="totalPages", type="integer"),
 *         @OA\Property(property="totalItems", type="integer"),
 *         @OA\Property(property="itemsPerPage", type="integer"),
 *         @OA\Property(property="hasNext", type="boolean"),
 *         @OA\Property(property="hasPrevious", type="boolean")
 *     ),
 *     @OA\Property(
 *         property="links",
 *         type="object",
 *         nullable=true,
 *         description="Liens de navigation (pour les listes paginées)",
 *         @OA\Property(property="self", type="string", format="uri"),
 *         @OA\Property(property="next", type="string", format="uri", nullable=true),
 *         @OA\Property(property="previous", type="string", format="uri", nullable=true),
 *         @OA\Property(property="first", type="string", format="uri"),
 *         @OA\Property(property="last", type="string", format="uri")
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="ValidationError",
 *     title="Validation Error",
 *     description="Erreurs de validation détaillées",
 *     @OA\Property(property="field_name", type="array", @OA\Items(type="string"), example={"Le champ est requis", "Le format est invalide"})
 * )
 */
