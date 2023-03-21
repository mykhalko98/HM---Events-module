<?php

namespace Modules\Events\Widgets\EventLocation;

use Hubmachine\Widgets\Widget;
use Illuminate\Support\Facades\View;

class Controller extends Widget
{
    public function handle()
    {
        $is_edit = View::shared('widgets_state') == 'edit' ? true : false;
        $config = (object)$this->getConfigs();

        $content = core()->filters()->apply_filters('event_location_widget_content_showing', '');
        $view_vars = core()->filters()->apply_filters('widget_view_vars', ['config' => $config, 'content' => $content]);

        if ($is_edit) {
            return view('widgets.events::EventLocation.edit', $view_vars);
        }

        return view('widgets.events::EventLocation.index', $view_vars);
    }
}
