<?php

namespace App\Observers;

use App\Events\DatabaseChanged;
use App\Models\Client;

class ClientObserver
{
    /**
     * Handle the Client "created" event.
     */
    public function created(Client $client): void
    {
        event(new DatabaseChanged('clients', 'created', $client->toArray()));
    }

    /**
     * Handle the Client "updated" event.
     */
    public function updated(Client $client): void
    {
        event(new DatabaseChanged('clients', 'updated', $client->toArray()));
    }

    /**
     * Handle the Client "deleted" event.
     */
    public function deleted(Client $client): void
    {
        event(new DatabaseChanged('clients', 'deleted', $client->toArray()));
    }

    /**
     * Handle the Client "restored" event.
     */
    public function restored(Client $client): void
    {
        event(new DatabaseChanged('clients', 'restored', $client->toArray()));
    }

    /**
     * Handle the Client "force deleted" event.
     */
    public function forceDeleted(Client $client): void
    {
        event(new DatabaseChanged('clients', 'forceDeleted', $client->toArray()));
    }
}