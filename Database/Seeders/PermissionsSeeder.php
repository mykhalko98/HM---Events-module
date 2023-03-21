<?php

namespace Modules\Events\Database\Seeders;

use Hubmachine\Access\Models\UserRoles;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $permissions = [[
            'type'        => 'events',
            'title'       => 'Events',
            'action'      => 'view_events',
            'description' => 'View Events',
            'params'      => '',
            'created_at'  => date('Y-m-d H:i:s')
        ], [
            'type'        => 'events',
            'title'       => 'Events',
            'action'      => 'create_events',
            'description' => 'Create Event posts',
            'params'      => '',
            'created_at'  => date('Y-m-d H:i:s')
        ]];
        $roles = UserRoles::pluck('role', 'id');
        \App\Helpers\PermissionsSeeder::seed($permissions, $roles);

        $permissions = [[
            'type'        => 'events',
            'title'       => 'Events',
            'action'      => 'edit_events',
            'description' => 'Ability edit or delete other Events',
            'params'      => '',
            'created_at'  => date('Y-m-d H:i:s')
        ], [
            'type'        => 'events',
            'title'       => 'Events',
            'action'      => 'edit_categories',
            'description' => 'Ability create, edit or delete Categories',
            'params'      => '',
            'created_at'  => date('Y-m-d H:i:s')
        ],[
            'type'        => 'events',
            'title'       => 'Events',
            'action'      => 'edit_tags',
            'description' => 'Ability create, edit or delete Tags',
            'params'      => '',
            'created_at'  => date('Y-m-d H:i:s')
        ]];
        \App\Helpers\PermissionsSeeder::seed($permissions, ['superadmin']);
    }
}