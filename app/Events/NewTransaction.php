<?php

namespace App\Events;

use App\Models\Transaction;

class NewTransaction extends Event
{
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }
}
