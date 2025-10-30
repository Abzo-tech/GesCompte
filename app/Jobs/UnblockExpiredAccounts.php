<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UnblockExpiredAccounts implements ShouldQueue
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
        // Récupérer tous les comptes épargne bloqués dont la date de déblocage prévue est dépassée
        $expiredAccounts = \App\Models\Compte::where('type', 'epargne')
            ->where('statut', 'bloque')
            ->where('date_deblocage_prevue', '<=', now())
            ->get();

        foreach ($expiredAccounts as $compte) {
            // Débloquer automatiquement le compte
            $compte->update([
                'statut' => 'actif',
                'motif_deblocage' => 'Déblocage automatique - Période de blocage expirée',
                'date_deblocage_prevue' => null,
            ]);

            // Log de l'opération automatique
            \Log::info("Compte débloqué automatiquement: {$compte->numero} (ID: {$compte->id})");
        }

        \Log::info("Job de déblocage automatique exécuté: {$expiredAccounts->count()} comptes débloqués");
    }
}
