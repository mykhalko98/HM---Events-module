<?php

namespace Modules\Events\Database\Seeders;

use App\Helpers\PageSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Hubmachine\Core\Models\Pages\Pages;

class EventFormatDefaultPageSeeder extends Seeder
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
			'name'             => 'events_event_format_default',
			'module'           => 'events',
			'link'             => '/create-event/{event_format_id?}',
			'route'            => 'events.event.create',
			'title'            => 'Event Format - Default',
			'meta_title'       => 'Event Format - Default',
			'meta_description' => 'Event Format - Default',
			'meta_keywords'    => 'Event Format - Default',
            'visibility'       => 'private',
            'indexing'         => FALSE,
            'show_in_menu'     => FALSE,
            'destroyable'      => TRUE,
            'layout'           => [
				'sections' => [
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
							'create_event_left' => [
								'classes'   => ['col-9'],
								'custom_id' => 'create_event_left-column',
								'widgets' => [
                                    'events_event_cover' => [
                                        'classes'   => ['col-12'],
                                        'custom_id' => 'events_event_cover-widget',
                                        'fields' => [
                                            'width' => [
                                                'tag'   => 'input',
                                                'type'  => 'number',
                                                'id'    => 'width',
                                                'class' => 'form-control pb-form-control',
                                                'value' => '1200',
                                                'label' => 'Width',
                                                'index' => '1'
                                            ],
                                            'height' => [
                                                'tag'   => 'input',
                                                'type'  => 'humber',
                                                'id'    => 'height',
                                                'class' => 'form-control pb-form-control',
                                                'value' => '400',
                                                'label' => 'Height',
                                                'index' => '2'
                                            ]
                                        ],
                                        'content'   => '<widget>Event cover</widget>'
                                    ],
                                    'events_event_title' => [
                                        'classes'   => ['col-12'],
                                        'custom_id' => 'events_event_title-widget',
                                        'content'   => '<widget>Event title</widget>'
                                    ],
                                    'events_event_author' => [
                                        'classes'   => ['col-12'],
                                        'custom_id' => 'events_event_author-widget',
                                        'content'   => '<widget>Event author</widget>'
                                    ],
                                    'events_event_content' => [
                                        'classes'   => ['col-12'],
                                        'custom_id' => 'events_event_content-widget',
                                        'content'   => '<widget>Event content</widget>'
                                    ],
									'events_event_categories' => [
										'classes'   => ['col-12'],
										'custom_id' => 'events_event_categories-widget',
										'content'   => '<widget>Event Categories</widget>'
									],
                                    'events_event_tags' => [
                                        'classes'   => ['col-12'],
                                        'custom_id' => 'events_event_tags-widget',
                                        'content'   => '<widget>Event Tags</widget>'
                                    ],
								]
							],
                            'create_event_right' => [
                                'classes'   => ['col-3'],
                                'custom_id' => 'create_event_right-column',
                                'widgets' => [
                                    'events_event_admin_actions' => [
                                        'classes'   => ['col-12'],
                                        'custom_id' => 'events_event_admin_actions-widget',
                                        'content'   => '<widget>Event Admin Actions</widget>'
                                    ],
                                    'events_event_dates' => [
                                        'classes'   => ['col-12'],
                                        'custom_id' => 'events_event_dates-widget',
                                        'content'   => '<widget>Event dates</widget>'
                                    ],
                                    'events_event_tickets' => [
                                        'classes'   => ['col-12'],
                                        'custom_id' => 'events_event_tickets-widget',
                                        'content'   => '<widget>Event tickets</widget>',
                                        'fields' => [
                                            'buy_button' => [
                                                'tag'     => 'input',
                                                'type'    => 'checkbox',
                                                'id'      => 'buy_button',
                                                'class'   => 'form-control pb-form-control custom-control-input',
                                                'value'   => 'true',
                                                'label'   => 'Display button for the buy ticket',
                                                'index'   => '1'
                                            ],
                                        ],
                                    ],
                                    'events_event_location' => [
                                        'classes'   => ['col-12'],
                                        'custom_id' => 'events_event_location-widget',
                                        'content'   => '<widget>Event location</widget>'
                                    ],
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

        \DB::beginTransaction();
        try {
            PageSeeder::seed($page);

            $page = Pages::find($page['id']);
            if ($page) {
                $page->route_params = json_encode(['event_format_id' => $page->getKey()]);
                $page->save();
            }
        } catch (\Throwable $th) {
            \DB::rollBack();
            throw $th;
        }
        \DB::commit();
	}
}