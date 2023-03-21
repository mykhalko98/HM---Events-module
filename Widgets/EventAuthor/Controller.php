<?php

namespace Modules\Events\Widgets\EventAuthor;

use Hubmachine\Widgets\Widget;
use Illuminate\Support\Facades\View;

class Controller extends Widget
{
    public function handle()
    {
        $is_edit = View::shared('widgets_state') == 'edit' ? true : false;
        $config = (object)$this->getConfigs();

        $content = core()->filters()->apply_filters('event_author_widget_content_showing', '');
        $view_vars = core()->filters()->apply_filters('widget_view_vars', ['config' => $config, 'content' => $content]);

        if ($is_edit) {
            return view('widgets.events::EventAuthor.edit', $view_vars);
        }

        return view('widgets.events::EventAuthor.index', $view_vars);
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
                    return 'Event author';
            }

            if (isset($element->elements))
                return $this->getWidgetContent($element->elements);
        }

        return 'Event author';
    }
}
