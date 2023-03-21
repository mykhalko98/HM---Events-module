<?php

namespace Modules\Events\Widgets\EventTickets;

use Hubmachine\Widgets\Widget;
use Illuminate\Support\Facades\View;

class Controller extends Widget
{
    public function handle()
    {
        $is_edit = View::shared('widgets_state') == 'edit' ? true : false;
        $config = (object)$this->getConfigs();

        $view_vars = core()->filters()->apply_filters('widget_view_vars', ['config' => $config]);

        if ($is_edit) {
            return view('widgets.events::EventTickets.edit', $view_vars);
        }

        return view('widgets.events::EventTickets.index', $view_vars + ['viewer' => \Auth::user()]);
    }
}
