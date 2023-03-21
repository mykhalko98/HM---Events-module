<?php

namespace Modules\Events\Widgets\EventTeaser;

use Hubmachine\Widgets\Widget;
use Illuminate\Support\Facades\View;

class Controller extends Widget
{
    public function handle()
    {

        $is_edit = View::shared('widgets_state') == 'edit' ? true : false;
        $config = (object)$this->getConfigs();

        if (!isset($config->elements)) {
            return false;
        }

        $content = core()->filters()->apply_filters('event_teaser_widget_content_showing', '');
        $show = core()->filters()->apply_filters('event_teaser_widget_show', false);
        $view_vars = core()->filters()->apply_filters('widget_view_vars', ['config' => $config, 'content' => $content,]);

        if ($is_edit) {
            return view('widgets.events::EventTeaser.edit', $view_vars);
        }

        return $show ? view('widgets.events::EventTeaser.index', $view_vars) : '';
    }

    /**
     *   Render rows, cols and widgets
     */
    protected function getWidgetContent($elements)
    {
        foreach ($elements as $ind => $element) {
            if (!isset($element->type))
                continue;

            if ($element->type == "widget-content") {
                if (!empty($element->content))
                    return $element->content;
                else
                    return '<p>Event teaser</p>';
            }

            if (isset($element->elements))
                return $this->getWidgetContent($element->elements);
        }

        return '<p>Event teaser</p>';
    }
}
