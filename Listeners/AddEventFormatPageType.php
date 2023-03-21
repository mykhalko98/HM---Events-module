<?php

namespace Modules\Events\Listeners;

class AddEventFormatPageType
{
    public function handle($event)
    {
        core()->filters()->add_filter('page_type_choices', function ($choices) {
            return $choices + ['events_event_format' => 'Event format'];
        });
    }
}
