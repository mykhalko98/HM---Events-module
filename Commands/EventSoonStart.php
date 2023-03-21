<?php

namespace Modules\Events\Commands;

use Carbon\Carbon;
use Hubmachine\Users\Models\User;
use Illuminate\Console\Command;
use Modules\Events\Entities\EventEvents;
use Hubmachine\Notifications\Models\Notifications;

class EventSoonStart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder:events';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle()
    {
        return true;
        $events = EventEvents::where('status', 'public')->where('start_time', '>=', Carbon::now()->addMinutes(60))->get();
        foreach ($events as $event) {
            if (!$event->creatives()->count() && !$event->reviewers()->count()) {
                continue;
            }
            //1 hour notify
            if ($event->start_time->diffInMinutes(Carbon::now()) >= 60 && $event->start_time->diffInMinutes(Carbon::now()) <= 70) {
                info('Reminder about event start. Event ID = ' .$event->getKey());
                $notifications = Notifications::select()
                    ->where('type', \Modules\Events\Notifications\EventSoonStart::class)
                    ->where('subject_type', EventEvents::class)
                    ->where('subject_id', $event->getKey())
                    ->where('created_at', '>=', Carbon::now()->subMinutes(10))
                    ->where('created_at', '<=', Carbon::now())
                    ->first();

                if ($notifications) {
                    continue;
                }
                $time = "one hour";
                if ($event->creatives()->count()) {
                    $creatives = User::whereIn('id', $event->creatives()->pluck('user_id')->toArray())->get();
                    $creatives && notifications()->notifyViaJob($creatives, 'send_notification', new \Modules\Events\Notifications\EventSoonStart(['subject' => $event, 'time' => $time, 'author' => $event->author]));
                }
                if ($event->reviewers()->count()) {
                    $reviewers = $event->reviewers()->pluck('user_id');
                    $reviewers && notifications()->notifyViaJob($reviewers, 'send_notification', new \Modules\Events\Notifications\EventSoonStart(['subject' => $event, 'time' => $time, 'author' => $event->author]));
                }
            }

            //1 day notify
            if ($event->start_time->diffInMinutes(Carbon::now()) >= 1440 && $event->start_time->diffInMinutes(Carbon::now()) <= 1450) {
                info('Reminder about event start. Event ID = ' .$event->getKey());
                $notifications = Notifications::select()
                    ->where('type', \Modules\Events\Notifications\EventSoonStart::class)
                    ->where('subject_type', EventEvents::class)
                    ->where('subject_id', $event->getKey())
                    ->where('created_at', '>=', Carbon::now()->subMinutes(10))
                    ->where('created_at', '<=', Carbon::now())
                    ->first();

                if ($notifications) {
                    continue;
                }
                $time = "one day";
                if ($event->creatives()->count()) {
                    $creatives = User::whereIn('id', $event->creatives()->pluck('user_id')->toArray())->get();
                    $creatives && notifications()->notifyViaJob($creatives, 'send_notification', new \Modules\Events\Notifications\EventSoonStart(['subject' => $event, 'time' => $time, 'author' => $event->author]));
                }
                if ($event->reviewers()->count()) {
                    $reviewers = $event->reviewers()->pluck('user_id');
                    $reviewers && notifications()->notifyViaJob($reviewers, 'send_notification', new \Modules\Events\Notifications\EventSoonStart(['subject' => $event, 'time' => $time, 'author' => $event->author]));
                }
            }
        }
    }
}
