<?php

namespace Modules\Events\Database\Seeders;

use App\Helpers\PageSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class EventDashboardPageSeeder extends Seeder
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
		    'name'             => 'event_dashboard',
		    'module'           => 'events',
		    'link'             => '/event/{link}/dashboard',
		    'route'            => 'events.event.dashboard',
		    'title'            => 'Event Dashboard',
		    'meta_title'       => 'Event Dashboard',
		    'meta_description' => 'Event Dashboard',
		    'meta_keywords'    => 'Event Dashboard',
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
								'fluid'     => false,
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
					        'dashboard_event_header' => [
					            'tag'       => 'aside',
					            'classes'   => ['col-12'],
                                'custom_id' => 'dashboard_event_header-column',
                                'widgets'   => [
                                    'events_dashboard_title' => [
                                        'classes'   => ['col-12'],
                                        'custom_id' => 'events_dashboard_title-widget',
                                        'content'   => '<widget>Dashboard title</widget>'
                                    ]
                                ]
                            ],
					        'dashboard_event_left' => [
					            'classes'   => ['col-8'],
                                'custom_id' => 'dashboard_event_left-column',
                                'widgets'   => [
                                    'events_bought_tickets' => [
                                        'classes'   => ['col-12'],
                                        'custom_id' => 'events_bought_tickets-widget',
                                        'content'   => '<widget>Bought tickets</widget>'
                                    ]
                                ]
                            ],
						    'dashboard_event_right' => [
							    'classes'   => ['col-4', 'aside'],
							    'custom_id' => 'dashboard_event_right-column',
							    'widgets' => [
								    'events_event_dates' => [
									    'classes'   => ['col-12'],
									    'custom_id' => 'events_event_dates-widget',
									    'content'   => '<widget>Event start/end dates</widget>'
								    ],
                                    'events_event_tickets' => [
                                        'classes'   => ['col-12'],
                                        'custom_id' => 'events_event_tickets-widget',
                                        'content'   => '<widget>Tickets</widget>'
                                    ],
                                    'events_event_location' => [
                                        'classes'   => ['col-12'],
                                        'custom_id' => 'events_event_location-widget',
                                        'content'   => '<widget>Event location</widget>'
                                    ],
                                    'events_event_statistics' => [
                                        'classes'   => ['col-12'],
                                        'custom_id' => 'events_event_statistics-widget',
                                        'content'   => '<widget>Statistics</widget>'
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