<?php

namespace Modules\Events\Database\Seeders;

use App\Helpers\PageSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Hubmachine\Core\Models\Layout\Layouts;
use Hubmachine\Menu\Models\Menu;

class EventsPageSeeder extends Seeder
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
			'name'             => 'events_list_events',
			'module'           => 'events',
			'link'             => '/{filter_by?}/{filter_value?}',
			'route'            => 'events.events',
			'title'            => 'Events',
			'meta_title'       => 'Events',
			'meta_description' => 'Events',
			'meta_keywords'    => 'Events',
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
							'list_of_events' => [
								'classes'   => ['col-12'],
								'custom_id' => 'events_list_events-column',
								'widgets' => [
									'events_list_events' => [
										'classes'   => ['col-12'],
										'custom_id' => 'events_list_events-widget',
										'content'   => '<widget>List Of Events</widget>'
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

        // seed menu
        $menu = Menu::where('name', '=', 'main-menu')->first();
        if ($menu) {
            $menu_layout = Layouts::where('object_type', '=', 'menu')->where('object_id', '=', $menu->getKey())->first();
            if ($menu_layout) {
                try {
                    $data = json_decode($menu_layout->data, true);

                    $data[] = [
                        'href'   => '/events',
                        'icon'   => 'empty',
                        'text'   => 'Events',
                        'title'  => 'Events',
                        'target' => '_self'
                    ];

                    $menu_layout->data = json_encode($data);
                    $menu_layout->save();
                    $menu_layout->generateMenu();
                } catch (\Throwable $th) {
                    info('EVENTS MENU SEED ERROR', [$th]);
                }
            }
        }
	}
}