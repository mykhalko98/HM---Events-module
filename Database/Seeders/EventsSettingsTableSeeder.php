<?php

namespace Modules\Events\Database\Seeders;

use Illuminate\Database\Seeder;

class EventsSettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('settings')->insert([
            ['type' => 'hubmachine.events.prefix', 'value' => 'event', 'params' => null],
        ]);
    }
}
