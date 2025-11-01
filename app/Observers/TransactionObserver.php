<?php

namespace App\Observers;

use App\Events\DatabaseChanged;
use App\Models\Transaction;

class TransactionObserver
{
    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        event(new DatabaseChanged('transactions', 'created', $transaction->toArray()));
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        event(new DatabaseChanged('transactions', 'updated', $transaction->toArray()));
    }

    /**
     * Handle the Transaction "deleted" event.
     */
    public function deleted(Transaction $transaction): void
    {
        event(new DatabaseChanged('transactions', 'deleted', $transaction->toArray()));
    }

    /**
     * Handle the Transaction "restored" event.
     */
    public function restored(Transaction $transaction): void
    {
        event(new DatabaseChanged('transactions', 'restored', $transaction->toArray()));
    }

    /**
     * Handle the Transaction "force deleted" event.
     */
    public function forceDeleted(Transaction $transaction): void
    {
        event(new DatabaseChanged('transactions', 'forceDeleted', $transaction->toArray()));
    }
}