<?php

namespace Modules\Events\Listeners;

class SaveEventFormat
{
    public function handle($event)
    {
        if ($event->page_type === 'events_event_format') {
            $event->page->__set('module', 'events');
            $event->page->__set('visibility', 'private');
            $event->page->__set('show_in_menu', false);
            $event->page->__set('link', '/' . settings()->get('hubmachine.events.prefix') . '/create-event/{event_format_id?}');
            $event->page->__set('route', 'events.event.create');
            $event->page->__set('route_params', json_encode(['event_format_id' => $event->page->getKey()]));
            $event->page->save();
        }
    }
}
