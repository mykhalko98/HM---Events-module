<?php

namespace Modules\Events\Database\Seeders;

use App\Helpers\PageSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class EventsByTagPageSeeder extends Seeder
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
			'name'             => 'events_list_events_by_tag',
			'module'           => 'events',
			'link'             => '/tag/{filter_value?}',
			'route'            => 'events.tag.events',
			'title'            => 'Events By tag',
			'meta_title'       => 'Events By tag',
			'meta_description' => 'Events By tag',
			'meta_keywords'    => 'Events By tag',
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
										'content'   => '<widget>Events</widget>'
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