<?php

namespace Modules\Events\Listeners;

use Hubmachine\Core\Models\Pages\Pages;

class MenuDefaultWidgetItems
{
    public function handle()
    {
        if (!access()->isAllowed('events', 'create_events')) {
            return TRUE;
        }
        core()->filters()->add_filter('menu_default_widget_items', function ($items) {
            $items[] = [
                'priority' => 101,
                'type'     => 'separator'
            ];
            $items[] = [
                'priority' => 101,
                'type'     => 'heading',
                'title'    => __('Events'),
            ];

            $pages = Pages::where('module', 'events')->where('route', '=', 'events.event.create')->get();
            foreach ($pages as $page) {
                $items[] = [
                    'priority' => 102,
                    'type'     => 'link',
                    'title'    => __('Add :event_format', ['event_format' => $page->title]),
                    'url'      => route('events.event.create', [$page->getKey()])
                ];
            }
            return $items;
        });
    }
}
