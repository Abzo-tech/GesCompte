<?php

namespace App\Observers;

use App\Events\DatabaseChanged;
use App\Models\Compte;

class CompteObserver
{
    /**
     * Handle the Compte "created" event.
     */
    public function created(Compte $compte): void
    {
        event(new DatabaseChanged('comptes', 'created', $compte->toArray()));
    }

    /**
     * Handle the Compte "updated" event.
     */
    public function updated(Compte $compte): void
    {
        event(new DatabaseChanged('comptes', 'updated', $compte->toArray()));
    }

    /**
     * Handle the Compte "deleted" event.
     */
    public function deleted(Compte $compte): void
    {
        event(new DatabaseChanged('comptes', 'deleted', $compte->toArray()));
    }

    /**
     * Handle the Compte "restored" event.
     */
    public function restored(Compte $compte): void
    {
        event(new DatabaseChanged('comptes', 'restored', $compte->toArray()));
    }

    /**
     * Handle the Compte "force deleted" event.
     */
    public function forceDeleted(Compte $compte): void
    {
        event(new DatabaseChanged('comptes', 'forceDeleted', $compte->toArray()));
    }
}