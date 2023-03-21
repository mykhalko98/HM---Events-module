<?php

namespace Modules\Events\Widgets\EventStatistics;

use Hubmachine\Widgets\Widget;
use Illuminate\Support\Facades\View;
use Modules\Events\Entities\EventTicketOrders;

class Controller extends Widget
{
    public function handle()
    {
        $event = View::shared('event');
        !$event && $event = events()->getEventFromUrlParams();
        if (!$event) {
            return '';
        }

        $event_users = $event->getFollowers();
        $refunds = null;
        if ($event->ticket_type !== 'free') {
            $refunds = EventTicketOrders::select()
                ->where('event_id', '=', $event->getKey())
                ->where('status', 'LIKE', 'refunded')
                ->whereNotIn('buyer_id', $event_users->pluck('id'))
                ->groupBy('buyer_id')
                ->count();
        }

        return view('widgets.events::EventStatistics.index', ['event_users' => $event_users, 'refunds' => $refunds]);
    }
}
