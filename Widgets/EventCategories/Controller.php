<?php

namespace Modules\Events\Widgets\EventCategories;

use Hubmachine\Widgets\Widget;
use Illuminate\Support\Facades\View;
use Modules\Events\Entities\EventCategories;

class Controller extends Widget
{
    public function handle()
    {
        $event = View::shared('event');
        $is_edit = View::shared('widgets_state') == 'edit' ? true : false;
        $categories = $event ? $event->categories : collect([]);

        if ($is_edit || request()->get('edit')) {
            $all_categories = EventCategories::orderBy('created_at', 'desc')->get();
            return view('widgets.events::EventCategories.edit', ['all_categories' => $all_categories, 'categories' => $categories]);
        }

        return view('widgets.events::EventCategories.index', ['categories' => $categories]);
    }
}
