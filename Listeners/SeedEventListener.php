<?php

namespace Modules\Events\Listeners;

use App\Events\SeedEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SeedEventListener
{
    /**
     * Handle the event.
     *
     * @param  Event  $event
     * @return void
     */
    public function handle(SeedEvent $event) {

        $db_seeder = $event->getDBSeeder();

        $files = scandir(base_path('Modules/Events/Database/Seeders'));
        foreach ($files as $key => $file) {
            if(in_array($file, [".", ".."])){
                continue;
            }
            if(file_exists(base_path('Modules/Events/Database/Seeders/' . $file))){
                $class_name = 'Modules\Events\Database\Seeders\\' . rtrim($file, '.php');
                $db_seeder->call($class_name, ['force' => true]);
            }
        }
    }
}
