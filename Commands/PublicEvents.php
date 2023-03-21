<?php

namespace Modules\Events\Commands;

use Illuminate\Console\Command;
use Modules\Events\Entities\EventEvents;
use Carbon\Carbon;

class PublicEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'public:events';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publication of events at a specified time';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $events = EventEvents::where('status', '=', 'draft')->where('publish_at', '<=', Carbon::now()->setTimezone('UTC'))->get();
        foreach ($events as $event){
            info('published event ID='.$event->getKey());
            $event->status = 'public';
            $event->save();
        }
    }
}
