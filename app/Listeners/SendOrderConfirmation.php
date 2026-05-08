<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Services\OrderConfirmationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Mail;

class SendOrderConfirmation implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(OrderPlaced $event): void
    {
        Mail::send(new OrderConfirmationMail($event->order));
    }
}
