<?php

namespace Modules\Events\Widgets\ListTags;

use Hubmachine\Widgets\Widget;
use Illuminate\Support\Facades\View;

class Controller extends Widget
{
    public function handle()
    {
        $config = (object)$this->getConfigs();
        View::share('fields', collect([
            'tag'          => ['title' => 'Tag',           'width' => 200, 'sticky' => false, 'disabled' => false],
            'posts_number' => ['title' => 'Events Number', 'width' => 100, 'sticky' => false, 'disabled' => false]
        ]));
        return view('widgets.events::ListTags.index', ['config' => $config]);
    }
}
