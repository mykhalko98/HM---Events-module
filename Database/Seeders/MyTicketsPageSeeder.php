<?php

namespace Modules\Events\Database\Seeders;

use App\Helpers\PageSeeder;
use Hubmachine\Core\Models\Layout\Layouts;
use Hubmachine\Core\Models\Pages\Pages;
use Hubmachine\Menu\Models\Menu;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class MyTicketsPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        if (!Pages::where('name', 'LIKE', 'mytickets')->exists()) {
            $page = [
                'name'             => 'mytickets',
                'module'           => 'events',
                'link'             => '/mytickets',
                'route'            => 'events.mytickets',
                'title'            => 'My tickets',
                'meta_title'       => 'My tickets',
                'meta_description' => 'My tickets',
                'meta_keywords'    => 'My tickets',
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
                                'list_of_own_events' => [
                                    'classes'   => ['col-12'],
                                    'custom_id' => 'events_mytickets_column',
                                    'widgets' => [
                                        'events_mytickets' => [
                                            'classes'   => ['col-12'],
                                            'custom_id' => 'events_mytickets_widget',
                                            'content'   => '<widget>My tickets</widget>'
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

            $menu = Menu::where('name', 'LIKE', 'main-menu')->first();
            if ($menu) {
                $layout = Layouts::where('object_type', '=', 'menu')->where('object_id', '=', $menu->getKey())->orderBy('updated_at', 'DESC')->first();
                $data = json_decode($layout->data);
                $data[] = (object)[
                    'href'   => '/events/mytickets',
                    'icon'   => 'empty',
                    'text'   => 'My tickets',
                    'type'   => 'link',
                    'class'  => '',
                    'title'  => '',
                    'status' => 'visible',
                    'target' => ''
                ];
                $layout->data = json_encode($data);
                $layout->save();
            }
        }
    }
}