<?php

namespace Modules\Events\Widgets\ListCategories;

use Hubmachine\Widgets\Widget;
use Illuminate\Support\Facades\View;

class Controller extends Widget
{
    public function handle()
    {
        $config = (object)$this->getConfigs();
        View::share('fields', collect([
            'name' => ['title' => 'Category Name', 'width' => 200, 'sticky' => false, 'disabled' => false],
            'events_number' => ['title' => 'Events Number', 'width' => 100, 'sticky' => false, 'disabled' => false],
            'date' => ['title' => 'Date', 'width' => 200, 'sticky' => false, 'disabled' => false]
        ]));
        return view('widgets.events::ListCategories.index', ['config' => $config]);
    }
}
