<?php

namespace Modules\Events\Database\Seeders;

use App\Helpers\PageSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class MyEventsPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

	    $page = [
		    'name'             => 'event_myevents',
		    'module'           => 'events',
		    'link'             => '/myevents',
		    'route'            => 'events.myevents',
		    'title'            => 'My Events',
		    'meta_title'       => 'My Events',
		    'meta_description' => 'My Events',
		    'meta_keywords'    => 'My Events',
		    'layout'           => [
			    'sections'    => [
				    'header' => [
					    'tag'       => 'header',
					    'classes'   => ['col-12', 'header', 'pm-0'],
					    'custom_id' => 'page-header-row',
					    'columns' => [
						    'header' => [
							    'classes'   => ['col-12'],
							    'custom_id' => 'page-header-column',
							    'widgets'   => [
								    'pages_menu_default' => [
									    'classes'   => ['col-12'],
									    'custom_id' => 'page-header-widget',
									    'content'   => '<widget>Header</widget>'
								    ]
							    ]
						    ]
					    ]
				    ],
				    'main' => [
					    'tag'       => 'main',
					    'classes'   => ['col-12', 'pm-xs-0', 'main'],
					    'custom_id' => 'page-main-row',
					    'fluid'     => false,
					    'columns'   => [
						    'events_myevents' => [
							    'classes'   => ['col-12'],
							    'custom_id' => 'events_myevents-column',
							    'widgets' => [
								    'events_myevents' => [
									    'classes'   => ['col-12'],
									    'custom_id' => 'events_myevents-widget',
									    'content'   => '<widget>My events</widget>'
								    ]
							    ]
						    ]
					    ]
				    ],
				    'footer' => [
					    'tag'       => 'footer',
					    'classes'   => ['col-12', 'footer'],
					    'custom_id' => 'page-footer-row',
					    'columns' => [
						    'footer' => [
							    'tag'       => 'div',
							    'classes'   => ['col-12'],
							    'custom_id' => 'page-footer-column',
							    'fluid'     => false,
							    'widgets'   => [
								    'pages_footer_component' => [
									    'custom_id' => 'page-footer-widget',
									    'classes'   => ['col-12'],
									    'content'   => '<widget>Footer</widget>'
								    ]
							    ]
						    ]
					    ]
				    ]
			    ]
		    ]
	    ];

	    PageSeeder::seed($page);
    }
}