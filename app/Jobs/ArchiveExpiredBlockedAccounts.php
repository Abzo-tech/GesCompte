<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ArchiveExpiredBlockedAccounts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Récupérer tous les comptes bloqués dont la date de déblocage prévue est dépassée depuis plus de 30 jours
        $expiredAccounts = \App\Models\Compte::where('type', 'epargne')
            ->where('statut', 'bloque')
            ->where('date_deblocage_prevue', '<=', now()->subDays(30))
            ->with(['client', 'transactions'])
            ->get();

        $archivedCount = 0;

        foreach ($expiredAccounts as $compte) {
            try {
                // Archiver le compte et ses transactions vers le serverless
                $this->archiveToServerless($compte);

                // Supprimer définitivement le compte de la base de données locale
                $compte->forceDelete();

                $archivedCount++;

                \Log::info("Compte archivé avec succès: {$compte->numero} (ID: {$compte->id})");

            } catch (\Exception $e) {
                \Log::error("Erreur lors de l'archivage du compte {$compte->numero}: " . $e->getMessage());
            }
        }

        \Log::info("Job d'archivage exécuté: {$archivedCount} comptes archivés sur {$expiredAccounts->count()} comptes expirés");
    }

    /**
     * Archive account and transactions to serverless storage
     */
    private function archiveToServerless(\App\Models\Compte $compte): void
    {
        $archiveData = [
            'compte' => [
                'id' => $compte->id,
                'numero' => $compte->numero,
                'type' => $compte->type,
                'statut' => $compte->statut,
                'solde' => $compte->solde,
                'devise' => $compte->devise,
                'date_creation' => $compte->date_creation,
                'date_blocage' => $compte->date_blocage,
                'date_deblocage_prevue' => $compte->date_deblocage_prevue,
                'motif_blocage' => $compte->motif_blocage,
                'client' => $compte->client ? [
                    'id' => $compte->client->id,
                    'nom' => $compte->client->nom,
                    'prenom' => $compte->client->prenom,
                    'email' => $compte->client->email,
                    'telephone' => $compte->client->telephone,
                    'nci' => $compte->client->nci,
                    'adresse' => $compte->client->adresse,
                ] : null,
                'transactions' => $compte->transactions->map(function ($transaction) {
                    return [
                        'id' => $transaction->id,
                        'montant' => $transaction->montant,
                        'type' => $transaction->type,
                        'description' => $transaction->description,
                        'date_transaction' => $transaction->date_transaction,
                        'created_at' => $transaction->created_at,
                    ];
                }),
                'archived_at' => now(),
                'archive_reason' => 'Blocage expiré depuis plus de 30 jours'
            ]
        ];

        // Ici, vous intégreriez votre service serverless (AWS S3, Google Cloud Storage, etc.)
        // Pour l'exemple, on utilise le service CloudArchiveService existant
        $cloudService = app(\App\Services\CloudArchiveService::class);
        $cloudService->archive($compte->id, $archiveData);
    }
}
