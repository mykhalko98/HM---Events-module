<?php

namespace Modules\Events\Listeners;

class ConnectResourcesListener
{
    public function handle($event)
    {
        if (($event->page && $event->page->module == 'events') || strpos($event->route_name, 'events') === 0) {
            events()->resources();
            core()->filters()->add_filter('body_attrs', function ($attrs) {
                $attrs['currency'] = strtolower(settings()->get('hubmachine.api.stripe.currency', 'USD'));
                return $attrs;
            });
        }
        $route_name = $event->route_name;
        switch ($route_name) {
            case 'events.event.create':
            case 'events.event.edit':
                core()->resources()->redactor();
                break;

                // admin
            case 'events.admin.events':
                events()->admin_resources();
                break;
            default:
                # code
        }
    }
}
