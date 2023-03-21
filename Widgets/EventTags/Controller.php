<?php

namespace Modules\Events\Widgets\EventTags;

use Hubmachine\Widgets\Widget;
use Illuminate\Support\Facades\View;

use Modules\Events\Entities\EventTags;

class Controller extends Widget
{
    public function handle()
    {
        $event = View::shared('event');
        $is_edit = View::shared('widgets_state') == 'edit' ? true : false;
        $tags = $event ? $event->tags : collect([]);

        if ($is_edit || request()->get('edit')) {
            $all_tags = EventTags::orderBy('created_at', 'desc')->get();
            return view('widgets.events::EventTags.edit', ['all_tags' => $all_tags, 'tags' => $tags]);
        }

        return view('widgets.events::EventTags.index', ['tags' => $tags]);
    }
}
