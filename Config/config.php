<?php

use Modules\Events\Widgets;

return [
	'name'              => 'Events',
	'short_description' => 'Short description.',
	'description'       => 'Full description.',
	'author'            => 'hubmachine',
	'author_link'       => '//hubmachine.com',
	'version'           => '1.0.0',
	'released'          => 'Dec 18, 2020',
	'updated'           => 'Dec 18, 2020',
	'whatsnew'          => [
		'1.0.0' => 'Init'
	],
	'settings'    => [
		'form'   => \Modules\Events\Forms\Admin\Settings::class,
		'params' => [
			'method'     => 'POST',
			'route'      => 'events.update_settings',
			'class'      => 'form-settings-api',
			'novalidate' => 'novalidate',
			'onsubmit'   => 'javascript: return validateForm.validateAJAX($(this))'
		]
    ],
    'artisan_actions' => [
        '1.0.0' => [
            'seeds'   => [
                'PermissionsSeeder',
                'EventCategoriesTableSeeder',
                'EventsPageSeeder',
                'MyEventsPageSeeder',
                'MyTicketsPageSeeder',
                'CategoriesPageSeeder',
                'EventsByCategoryPageSeeder',
                'TagsPageSeeder',
                'EventsByTagPageSeeder',
                'EventFormatDefaultPageSeeder',
                'EventsMailablesSeeder',
                'EventDashboardPageSeeder',
                'EventsSettingsTableSeeder',
            ],
            'search'=> [
                [
                    "action" => "rebuild",
                    "class" => "Modules\Events\Entities\EventEvents"
                ], [
                    "action" => "reindex",
                    "class" => "Modules\Events\Entities\EventEvents"
                ]
            ],
            'migrate' => true
        ]
    ],
	'widgets' => [
        'events' => [
            'title' => 'Events',
			'events_list_events' => [
			    'title'       => 'Events',
			    'description' => 'List of events',
			    'options'     => ['content' => ''],
			    'image'       => asset('images/events/admin/icons/all-events.svg'),
                'instance'    => Modules\Events\Widgets\ListEvents\Controller::class
			],
			'events_other_events' => [
			    'title'       => 'Other Events',
			    'description' => 'List of other events',
			    'options'     => ['content' => ''],
			    'image'       => asset('images/events/admin/icons/all-events.svg'),
                'instance'    => Modules\Events\Widgets\OtherEvents\Controller::class
			],
            'events_myevents' => [
                'title'       => 'My events',
                'description' => 'List of user\'s events',
                'options'     => ['content' => ''],
                'image'       => asset('images/events/admin/icons/my-events.svg'),
                'instance'    => Modules\Events\Widgets\MyEvents\Controller::class
            ],
			'events_event_title' => [
			    'title'       => 'Event title',
			    'description' => 'Title for a post',
			    'options'     => ['content' => ''],
			    'image'       => asset('images/events/admin/icons/event-title.svg'),
                'instance'     => Modules\Events\Widgets\EventTitle\Controller::class
			],
            'events_event_url' => [
			    'title' => 'Event URL',
			    'description' => 'URL of the event',
			    'options' => ['content' => ''],
                'instance' => Modules\Events\Widgets\EventUrl\Controller::class
			],
			'events_event_content' => [
			    'title'       => 'Event content',
			    'description' => 'Editor, media blocks for a post',
			    'options'     => ['content' => ''],
			    'image'       => asset('images/events/admin/icons/event-content.svg'),
                'instance'    => Modules\Events\Widgets\EventContent\Controller::class
            ],
            'events_event_teaser' => [
                'title'       => 'Event teaser',
                'description' => 'Excerpt of a event content',
                'options'     => ['content' => ''],
                'image'       => asset('images/events/admin/icons/event-teaser.svg'),
                'instance'    => Modules\Events\Widgets\EventTeaser\Controller::class
			],
            'events_event_cover' => [
                'title'       => 'Event cover',
                'description' => 'Cover of a event',
                'options' => [
                    'content' => '',
                    'fields' => [
                        'width' => [
                            'tag'   => 'input',
                            'type'  => 'number',
                            'id'    => 'width',
                            'class' => 'form-control pb-form-control',
                            'value' => '1600',
                            'label' => 'Width',
                            'index' => '1'
                        ],
                        'height' => [
                            'tag'   => 'input',
                            'type'  => 'humber',
                            'id'    => 'height',
                            'class' => 'form-control pb-form-control',
                            'value' => '530',
                            'label' => 'Height',
                            'index' => '2'
                        ],
                        'required_size' => [
                            'tag'     => 'input',
                            'type'    => 'checkbox',
                            'id'      => 'required_size',
                            'class'   => 'form-control pb-form-control custom-control-input',
                            'value'   => 'false',
                            'label'   => 'Size is required',
                            'index'   => '3'
                        ],
                    ]
                ],
                'image'    => asset('images/events/admin/icons/event-cover.svg'),
                'instance' => Modules\Events\Widgets\EventCover\Controller::class
            ],
			'events_list_categories' => [
			    'title'       => 'Categories',
			    'description' => 'List of categories',
			    'options'     => ['content' => ''],
			    'image'       => asset('images/events/admin/icons/all-categories.svg'),
                'instance'    => Modules\Events\Widgets\ListCategories\Controller::class
            ],
            'events_list_tags' => [
                'title'       => 'Tags',
                'description' => 'List of tags',
                'options'     => ['content' => ''],
                'image'       => asset('images/events/admin/icons/all-tags.svg'),
                'instance'    => Modules\Events\Widgets\ListTags\Controller::class
            ],
			'events_event_categories' => [
			    'title'       => 'Event categories',
			    'description' => 'List of event categories',
			    'options'     => ['content' => ''],
			    'image'       => asset('images/events/admin/icons/event-category.svg'),
                'instance'    => Modules\Events\Widgets\EventCategories\Controller::class
            ],
            'events_event_tags' => [
                'title'       => 'Event tags',
                'description' => 'List of event tags',
                'options'     => ['content' => ''],
                'image'       => asset('images/events/admin/icons/event-category.svg'),
                'instance'    => Modules\Events\Widgets\EventTags\Controller::class
            ],
            'events_featured_events' => [
                'title'       => 'Featured events',
                'description' => 'Highlighted events',
                'options'     => ['content' => ''],
                'image'       => asset('images/events/admin/icons/featured-events.svg'),
                'instance'    => Modules\Events\Widgets\FeaturedEvents\Controller::class
            ],
            'events_event_author' => [
                'title'       => 'Event author',
                'description' => 'Block with author\'s name, photo and published date',
                'options'     => ['content' => ''],
                'image'       => asset('images/events/admin/icons/event-author.svg'),
                'instance'    => Modules\Events\Widgets\EventAuthor\Controller::class
            ],
            'events_event_dates' => [
                'title'       => 'Event dates',
                'description' => 'Event start/end date',
                'options'     => ['content' => ''],
                'image'       => asset('images/events/admin/icons/event-dates.svg'),
                'instance'    => Modules\Events\Widgets\EventDates\Controller::class
            ],
            'events_event_tickets' => [
                'title'       => 'Event tickets',
                'description' => 'Event ticket settings',
                'options' => [
                    'content' => '',
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
                    ]
                ],
                'image'       => asset('images/events/admin/icons/event-tickets.svg'),
                'instance'    => Modules\Events\Widgets\EventTickets\Controller::class,
            ],
            'events_event_location' => [
                'title'       => 'Event location',
                'description' => 'Event location settings',
                'options'     => ['content' => ''],
                'image'       => asset('images/events/admin/icons/event-location.svg'),
                'instance'    => Modules\Events\Widgets\EventLocation\Controller::class
            ],
            'events_event_privacy' => [
                'title'       => 'Event privacy',
                'description' => 'Event privacy settings',
                'options'     => ['content' => ''],
                'image'       => asset('images/events/admin/icons/event-privacy.svg'),
                'instance'    => Modules\Events\Widgets\EventPrivacy\Controller::class
            ],
            'events_event_admin_actions' => [
                'title'       => 'Event Admin Actions',
                'description' => 'Event Admin Actions',
                'options'     => ['content' => ''],
                'image'       => asset('images/events/admin/icons/follow-button.svg'),
                'instance'    => Modules\Events\Widgets\EventAdminActions\Controller::class
            ],
            'events_dashboard_title' => [
                'title'       => 'Dashboard Title',
                'description' => 'Dashboard Title',
                'options'     => ['content' => ''],
                'image'       => asset('images/events/admin/icons/event-title.svg'),
                'instance'    => Modules\Events\Widgets\EventDashboardTitle\Controller::class
            ],
            'events_bought_tickets' => [
                'title'       => 'Bought Tickets',
                'description' => 'Bought Tickets',
                'options'     => ['content' => ''],
                'image'       => asset('images/events/admin/icons/event-tickets.svg'),
                'instance'    => Modules\Events\Widgets\EventBoughtTickets\Controller::class
            ],
            'events_event_statistics' => [
                'title'       => 'Statistics',
                'description' => 'Event Statistics',
                'options'     => ['content' => ''],
                'image'       => asset('images/events/admin/icons/event-tickets.svg'),
                'instance'    => Modules\Events\Widgets\EventStatistics\Controller::class
            ],
            'events_mytickets' => [
                'title'       => 'My tickets',
                'description' => 'List of tickets',
                'options'     => ['content' => ''],
                'image'       => asset('images/events/admin/icons/my-events.svg'),
                'instance'    => Modules\Events\Widgets\MyTickets\Controller::class
            ],
        ]
    ],
    'show_in_menu' => true,
    'notifications' => [
        'events' => [
            'refund_request' => 'Modules\Events\Notifications\RefundRequest',
            'refund_request_approved' => 'Modules\Events\Notifications\RefundRequestApproved',
            'ticket_successfully_issued' => 'Modules\Events\Notifications\TicketSuccessfullyIssued'
        ]
    ]
];
