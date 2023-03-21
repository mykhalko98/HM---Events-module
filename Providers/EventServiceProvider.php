<?php

namespace Modules\Events\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'Hubmachine\Core\Events\Pages\PageCreateBegin' => [
            'Modules\Events\Listeners\AddEventFormatPageType'
        ],
        'Hubmachine\Core\Events\Pages\PageCreated' => [
            'Modules\Events\Listeners\SaveEventFormat'
        ],
        'Hubmachine\Core\Events\Widgets\WidgetGroupContentChanging' => [
            'Modules\Events\Listeners\SaveEventContent',
            'Modules\Events\Listeners\CreateEvent'
        ],
        'Hubmachine\Modules\Events\MenuDefaultWidgetItems' => [
            'Modules\Events\Listeners\MenuDefaultWidgetItems'
        ],
        'Hubmachine\Users\Events\Deleting' => [
            'Modules\Events\Listeners\UserDeleting'
        ],
        'App\Events\ConnectResources' => [
            'Modules\Events\Listeners\ConnectResourcesListener'
        ],
        'Hubmachine\Payments\Events\Purchase' => [
            'Modules\Events\Listeners\TicketPurchase'
        ],
        'Hubmachine\Payments\Events\Refund' => [
            'Modules\Events\Listeners\TicketOrderRefund'
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
