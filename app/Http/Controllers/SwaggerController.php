<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Aboubakry Dieng Ges-Compte API",
 *     version="1.0.0",
 *     description="Documentation de l'API Ges-Compte développée par Aboubakry Dieng",
 *     @OA\Contact(
 *         email="aboubakry.dieng@example.com",
 *         name="Aboubakry Dieng"
 *     )
 * )
 * @OA\Server(
 *     url="https://gescompte-1.onrender.com",
 *     description="Serveur de production"
 * )
 * @OA\Server(
 *     url="http://127.0.0.1:9000",
 *     description="Serveur de développement local"
 * )
 * @OA\Schema(
 *     schema="Client",
 *     type="object",
 *     title="Client",
 *     description="Représente un client bancaire",
 *     required={"nom", "prenom", "email", "statut"},
 *     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000", description="Identifiant unique du client"),
 *     @OA\Property(property="nom", type="string", example="Martin", description="Nom du client"),
 *     @OA\Property(property="prenom", type="string", example="Jean", description="Prénom du client"),
 *     @OA\Property(property="email", type="string", format="email", example="jean.martin@example.com", description="Email unique du client"),
 *     @OA\Property(property="telephone", type="string", example="01 23 45 67 89", nullable=true, description="Numéro de téléphone"),
 *     @OA\Property(property="adresse", type="string", example="123 Avenue de la Test", nullable=true, description="Adresse postale"),
 *     @OA\Property(property="statut", type="string", enum={"actif", "inactif", "suspendu"}, example="actif", description="Statut du client"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Date de création"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Date de dernière modification")
 * )
 * @OA\Schema(
 *     schema="Compte",
 *     type="object",
 *     title="Compte",
 *     description="Représente un compte bancaire",
 *     required={"numero", "type", "statut", "devise", "date_creation"},
 *     @OA\Property(property="id", type="integer", example=1, description="Identifiant unique du compte"),
 *     @OA\Property(property="type", type="string", enum={"courant", "epargne", "cheque"}, example="courant", description="Type de compte"),
 *     @OA\Property(property="statut", type="string", enum={"actif", "bloque", "ferme"}, example="actif", description="Statut du compte"),
 *     @OA\Property(property="client_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000", nullable=true, description="Identifiant du client propriétaire"),
 *     @OA\Property(property="devise", type="string", example="FCFA", maxLength=4, description="Devise du compte"),
 *     @OA\Property(property="date_creation", type="string", format="date-time", example="2025-01-01T00:00:00Z", description="Date de création du compte"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, description="Date d'archivage (soft delete)"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Date de création dans le système"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Date de dernière modification")
 * )
 * @OA\Schema(
 *     schema="CompteApi",
 *     type="object",
 *     title="CompteApi",
 *     description="Représente un compte bancaire dans l'API (format camelCase)",
 *     @OA\Property(property="id", type="integer", example=1, description="Identifiant unique du compte"),
 *     @OA\Property(property="numeroCompte", type="string", example="CMPT2025001", description="Numéro unique du compte"),
 *     @OA\Property(property="titulaire", type="string", example="Jean Dupont", nullable=true, description="Nom complet du titulaire du compte"),
 *     @OA\Property(property="type", type="string", enum={"courant", "epargne", "cheque"}, example="courant", description="Type de compte"),
 *     @OA\Property(property="solde", type="number", format="float", example=1250000.0, description="Solde actuel du compte"),
 *     @OA\Property(property="devise", type="string", example="FCFA", maxLength=4, description="Devise du compte"),
 *     @OA\Property(property="dateCreation", type="string", format="date-time", example="2025-01-01T00:00:00Z", description="Date de création du compte"),
 *     @OA\Property(property="statut", type="string", enum={"actif", "bloque", "ferme"}, example="actif", description="Statut du compte"),
 *     @OA\Property(property="motifBlocage", type="string", example="Inactivité de 30+ jours", nullable=true, description="Raison du blocage si le statut est 'bloque'"),
 *     @OA\Property(
 *         property="metadata",
 *         type="object",
 *         @OA\Property(property="derniereModification", type="string", format="date-time", example="2023-06-10T14:30:00Z"),
 *         @OA\Property(property="version", type="integer", example=1)
 *     )
 * )
 * @OA\Schema(
 *     schema="CompteApiCollection",
 *     type="object",
 *     title="CompteApiCollection",
 *     description="Collection de comptes avec pagination",
 *     @OA\Property(property="success", type="boolean", example=true, description="Indique le succès de la requête"),
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/CompteApi")
 *     ),
 *     @OA\Property(
 *         property="pagination",
 *         type="object",
 *         ref="#/components/schemas/Pagination"
 *     ),
 *     @OA\Property(
 *         property="links",
 *         type="object",
 *         @OA\Property(property="self", type="string", example="http://api.example.com/api/v1/comptes?page=1"),
 *         @OA\Property(property="next", type="string", nullable=true, example="http://api.example.com/api/v1/comptes?page=2"),
 *         @OA\Property(property="previous", type="string", nullable=true, example=null),
 *         @OA\Property(property="first", type="string", example="http://api.example.com/api/v1/comptes?page=1"),
 *         @OA\Property(property="last", type="string", example="http://api.example.com/api/v1/comptes?page=3")
 *     )
 * )
 * @OA\Schema(
 *     schema="Pagination",
 *     type="object",
 *     title="Pagination",
 *     description="Informations de pagination",
 *     @OA\Property(property="currentPage", type="integer", example=1, description="Page actuelle"),
 *     @OA\Property(property="totalPages", type="integer", example=3, description="Nombre total de pages"),
 *     @OA\Property(property="totalItems", type="integer", example=25, description="Nombre total d'éléments"),
 *     @OA\Property(property="itemsPerPage", type="integer", example=10, description="Nombre d'éléments par page"),
 *     @OA\Property(property="hasNext", type="boolean", example=true, description="Page suivante disponible"),
 *     @OA\Property(property="hasPrevious", type="boolean", example=false, description="Page précédente disponible")
 * )
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *     title="ErrorResponse",
 *     description="Réponse d'erreur standardisée",
 *     @OA\Property(property="success", type="boolean", example=false, description="Indique l'échec"),
 *     @OA\Property(
 *         property="error",
 *         type="object",
 *         @OA\Property(property="code", type="string", example="VALIDATION_ERROR", description="Code d'erreur"),
 *         @OA\Property(property="message", type="string", example="Erreurs de validation", description="Message d'erreur"),
 *         @OA\Property(property="details", type="object", nullable=true, description="Détails de l'erreur")
 *     )
 * )
 */
class SwaggerController extends Controller
{
    // Cette classe contient les définitions Swagger
}
