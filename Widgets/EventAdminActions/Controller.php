<?php

namespace Modules\Events\Widgets\EventAdminActions;

use Hubmachine\Widgets\Widget;
use Illuminate\Support\Facades\View;
use Auth;

class Controller extends Widget
{
    public function handle()
    {
        $config = (object)$this->getConfigs();
        $event  = View::shared('event');
        $viewer = Auth::user();
        if (!$event || !$viewer || (!$viewer->isAdmin() && $viewer->getKey() !== $event->author_id)) {
            return '';
        }

        $view_vars = core()->filters()->apply_filters('widget_view_vars', [
            'config' => $config,
            'event'  => $event
        ]);

        return view('widgets.events::EventAdminActions.index', $view_vars);
    }
}
