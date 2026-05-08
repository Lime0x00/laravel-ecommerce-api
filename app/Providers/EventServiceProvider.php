<?php

namespace App\Providers;

use App\Events\OrderPlaced;
use App\Listeners\SendOrderConfirmation;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        OrderPlaced::class => [
            SendOrderConfirmation::class,
        ],
    ];

    /**
     * Enable the booting of all of your subscribers.
     *
     * @var bool
     */
    protected $shouldDiscoverEvents = true;
}
