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
 * @OA\Schema(
 *     schema="Compte",
 *     title="Compte",
 *     description="Compte resource",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="numero", type="string", example="CMP-001"),
 *     @OA\Property(property="type", type="string", example="courant"),
 *     @OA\Property(property="statut", type="string", example="actif"),
 *     @OA\Property(property="solde", type="number", format="decimal", example=0.00),
 *     @OA\Property(property="client_id", type="string", format="uuid"),
 *     @OA\Property(property="devise", type="string", example="FCFA"),
 *     @OA\Property(property="date_creation", type="string", format="date-time"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(
 *         property="client",
 *         ref="#/components/schemas/Client"
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="Client",
 *     title="Client",
 *     description="Client resource",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="nom", type="string", example="Doe"),
 *     @OA\Property(property="prenom", type="string", example="John"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="telephone", type="string", example="+22112345678"),
 *     @OA\Property(property="adresse", type="string", nullable=true),
 *     @OA\Property(property="date_naissance", type="string", format="date", nullable=true)
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
 *     description="Generic error response",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(
 *         property="error",
 *         type="object",
 *         @OA\Property(property="code", type="string", example="VALIDATION_ERROR"),
 *         @OA\Property(property="message", type="string", example="Erreurs de validation"),
 *         @OA\Property(property="details", type="object", additionalProperties={"type":"array", "items": {"type":"string"}}, nullable=true)
 *     )
 * )
 */
