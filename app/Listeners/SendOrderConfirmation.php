<?php

namespace App\Listeners;

use App\Events\OrderPlaced;

class SendOrderConfirmation
{
    /**
     * Handle the event.
     */
    public function handle(OrderPlaced $event): void
    {
        //
    }
}
