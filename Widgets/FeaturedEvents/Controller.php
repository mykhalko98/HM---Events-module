<?php
namespace Modules\Events\Widgets\FeaturedEvents;

use App\Helpers\Filter;
use Illuminate\Support\Facades\View;
use Modules\Events\Entities\EventEvents;
use Modules\Events\Entities\EventWidgetsContent;
use Hubmachine\Widgets\Widget;

class Controller extends Widget
{
    public function handle()
    {
        $config = (object)$this->getConfigs();
        $is_edit = View::shared('widgets_state') == 'edit' ? true : false;
        $view_vars = core()->filters()->apply_filters('widget_view_vars', ['config' => $config]);
        $event_id = isset($view_vars['event_id']) ? $view_vars['event_id'] : null;
        if ($is_edit) {
            return false;
        }

        $featured_events_select = EventEvents::select('event_events.*', 'files.id as media_id', 'event_widgets_content.id as event_cover')
            ->leftJoin('files', function ($join) {
                $join->on('files.resource_id', '=', 'event_events.id')->where('files.resource_type', '=', EventEvents::class);
            })
            ->leftJoin('event_widgets_content', function($join) {
                $join->on('event_events.id', '=', 'event_widgets_content.event_id')->where('widget_type', 'LIKE', 'events_event_cover_json');
            })
            ->where('featured', '=', 1)
            ->where(function ($q) {
                $q->whereNotNull('files.id')->orWhereNotNull('event_widgets_content.id');
            });
        if($event_id) {
            $featured_events_select->where('event_events.id', '!=', $event_id);
        }
        $featured_events_select->orderBy('created_at', 'DESC');
        $featured_events = $featured_events_select->get()->keyBy('id');

        if(count($featured_events) == 0) {
            return false;
        }

        $events_images = media()->filter(['id' => ['in' => array_column($featured_events->toArray(), 'media_id')]])->get()->keyBy('resource_id');

        $diff = array_diff(array_column($featured_events->toArray(), 'id'), array_column($events_images->toArray(), 'resource_id'));
        $event_widgets_content = Filter::filter(EventWidgetsContent::class, ['widget_type' => 'events_event_cover_json', 'event_id' => ['in' => array_values($diff)]])->get()->keyBy('event_id');
        $event_widgets_json = array_column($event_widgets_content->toArray(), 'content', 'event_id');
        $events_posters = [];
        foreach ($event_widgets_json as $key => $data) {
            $events_posters[$key] = json_decode($data)->poster;
        }
        
        return view('widgets.events::FeaturedEvents.index', ['config' => $config, 'events' => $featured_events, 'events_covers' => $events_images, 'events_posters' => $events_posters]);
    }
}
