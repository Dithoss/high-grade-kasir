<?php

namespace App\Listeners;

use App\Events\TransactionCreated;

class ReduceBookStock
{
    public function __construct() {}

    public function handle(TransactionCreated $event): void
    {
        if ($event->transaction->status === 'pending_approval') {
            return;
        }

        $event->transaction->load('items.book');

        foreach ($event->transaction->items as $item) {
            $item->book->decrement('stock', $item->quantity);
        }
    }
}