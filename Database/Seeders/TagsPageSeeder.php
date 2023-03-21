<?php

namespace Modules\Events\Database\Seeders;

use App\Helpers\PageSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class TagsPageSeeder extends Seeder
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
			'name'             => 'events_tags',
			'module'           => 'events',
			'link'             => '/tags',
			'route'            => 'events.tags',
			'title'            => 'Event Tags',
			'meta_title'       => 'Event Tags',
			'meta_description' => 'Event Tags',
			'meta_keywords'    => 'Event Tags',
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
							'list_of_event_tags' => [
								'classes'   => ['col-12'],
								'custom_id' => 'event_list_tags-column',
								'widgets' => [
									'events_list_tags' => [
										'classes'   => ['col-12'],
										'custom_id' => 'event_list_tags-widget',
										'content'   => '<widget>List Of Event Tags</widget>'
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