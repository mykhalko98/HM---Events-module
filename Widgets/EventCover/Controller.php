<?php

namespace Modules\Events\Widgets\EventCover;

use Hubmachine\Widgets\Widget;
use Illuminate\Support\Facades\View;

class Controller extends Widget
{
    public function handle()
    {
        $event   = View::shared('event');
        $is_edit = View::shared('widgets_state') == 'edit' ? true : false;
        $config  = (object)$this->getConfigs();

        !isset($config->fields->width->value)  && $config->fields->width->value = 1600;
        !isset($config->fields->height->value) && $config->fields->height->value = 530;
        if (!isset($config->fields->required_size->value)) {
            $config->fields->required_size = new \stdClass();
            $config->fields->required_size->value = false;
        } else {
            $config->fields->required_size->value = filter_var($config->fields->required_size->value, FILTER_VALIDATE_BOOLEAN);
        }

        $content   = core()->filters()->apply_filters('event_cover_widget_content_showing', '');
        $view_vars = core()->filters()->apply_filters('widget_view_vars', ['config' => $config, 'content' => $content]);

        if (empty($view_vars['content'])) {
            if ($event) {
                $event_image = media()->filter([
                    'resource_id' => ['=' => $event->getKey()],
                    'resource_type' => ['=' => \Modules\Events\Entities\EventEvents::class],
                    'type' => ['like' => 'events_event_cover_cropped']
                ])->first();
                if ($event_image) {
                    $view_vars['content']  = $event_image->getThumbnail($config->fields->width->value*2, $config->fields->height->value*2);
                    $view_vars['image_id'] = $event_image->getKey();
                }
            }
        } else {
            $view_vars['is_iframe'] = true;
        }

        if ($is_edit) {
            if ($event) {
                $event_cover_json = \Modules\Events\Entities\EventWidgetsContent::where('event_id', '=', $event->getKey())->where('widget_type', 'LIKE', 'events_event_cover_json')->first();
                if ($event_cover_json) {
                    $view_vars['event_cover_video'] = json_decode($event_cover_json->content)->video;
                }
            }
            return view('widgets.events::EventCover.edit', $view_vars);
        }

        return view('widgets.events::EventCover.index', $view_vars);
    }
}
