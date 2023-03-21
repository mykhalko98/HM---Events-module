<?php

namespace Modules\Events\Widgets\EventDashboardTitle;

use Hubmachine\Widgets\Widget;

class Controller extends Widget
{
    public function handle()
    {
        return view('widgets.events::EventDashboardTitle.index');
    }
}
