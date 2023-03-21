<?php

namespace Modules\Events\Widgets\MyEvents;

use Hubmachine\Widgets\Widget;
use Illuminate\Support\Facades\View;

class Controller extends Widget
{
    public function handle()
    {
        $config = (object)$this->getConfigs();
        View::share('fields', collect([
            'title' => ['title' => 'Title', 'width' => 200, 'sticky' => false, 'disabled' => false],
            'format' => ['title' => 'Event Format', 'width' => 100, 'sticky' => false, 'disabled' => false],
            'category' => ['title' => 'Category', 'width' => 100, 'sticky' => false, 'disabled' => false],
            'author' => ['title' => 'Author', 'width' => 100, 'sticky' => false, 'disabled' => false],
            'date' => ['title' => 'Date', 'width' => 200, 'sticky' => false, 'disabled' => false]
        ]));
        return view('widgets.events::MyEvents.index', ['config' => $config]);
    }
}
