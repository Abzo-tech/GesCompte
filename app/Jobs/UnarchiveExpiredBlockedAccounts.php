<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UnarchiveExpiredBlockedAccounts implements ShouldQueue
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
        // Récupérer tous les comptes bloqués dont la date de déblocage prévue est dépassée
        $expiredAccounts = \App\Models\Compte::where('type', 'epargne')
            ->where('statut', 'bloque')
            ->where('date_deblocage_prevue', '<=', now())
            ->get();

        $unarchivedCount = 0;

        foreach ($expiredAccounts as $compte) {
            try {
                // Vérifier si le compte existe dans le stockage serverless
                $cloudService = app(\App\Services\CloudArchiveService::class);

                if ($cloudService->exists($compte->id)) {
                    // Désarchiver le compte depuis le serverless
                    $archiveData = $cloudService->retrieve($compte->id);

                    // Restaurer le compte dans la base de données locale
                    $this->restoreFromServerless($archiveData);

                    // Supprimer l'archive du serverless
                    $cloudService->delete($compte->id);

                    $unarchivedCount++;

                    \Log::info("Compte désarchivé avec succès: {$compte->numero} (ID: {$compte->id})");
                } else {
                    // Si pas d'archive, juste débloquer le compte
                    $compte->update([
                        'statut' => 'actif',
                        'motif_deblocage' => 'Déblocage automatique - Période de blocage expirée',
                        'date_deblocage_prevue' => null,
                    ]);

                    $unarchivedCount++;

                    \Log::info("Compte débloqué (pas d'archive): {$compte->numero} (ID: {$compte->id})");
                }

            } catch (\Exception $e) {
                \Log::error("Erreur lors du désarchivage du compte {$compte->numero}: " . $e->getMessage());
            }
        }

        \Log::info("Job de désarchivage exécuté: {$unarchivedCount} comptes désarchivés sur {$expiredAccounts->count()} comptes expirés");
    }

    /**
     * Restore account and transactions from serverless storage
     */
    private function restoreFromServerless(array $archiveData): void
    {
        $compteData = $archiveData['compte'];

        // Restaurer le client si nécessaire
        $client = null;
        if ($compteData['client']) {
            $client = \App\Models\Client::firstOrCreate(
                ['id' => $compteData['client']['id']],
                $compteData['client']
            );
        }

        // Restaurer le compte
        $compte = \App\Models\Compte::create([
            'id' => $compteData['id'],
            'numero' => $compteData['numero'],
            'type' => $compteData['type'],
            'statut' => 'actif', // Le compte est maintenant débloqué
            'client_id' => $client ? $client->id : null,
            'devise' => $compteData['devise'],
            'date_creation' => $compteData['date_creation'],
            'date_blocage' => null, // Réinitialiser
            'date_deblocage_prevue' => null, // Réinitialiser
            'motif_deblocage' => 'Désarchivage automatique - Période de blocage expirée',
        ]);

        // Restaurer les transactions
        foreach ($compteData['transactions'] as $transactionData) {
            \App\Models\Transaction::create([
                'id' => $transactionData['id'],
                'compte_id' => $compte->id,
                'montant' => $transactionData['montant'],
                'type' => $transactionData['type'],
                'description' => $transactionData['description'],
                'date_transaction' => $transactionData['date_transaction'],
                'created_at' => $transactionData['created_at'],
                'updated_at' => now(),
            ]);
        }
    }
}
