<?php

namespace Modules\Events\Widgets\MyTickets;

use Modules\Events\Entities\EventEvents;
use Modules\Events\Entities\EventTicketOrders;
use Modules\Events\Widgets\EventsWidgets;

class Controller extends EventsWidgets
{
    public function handle()
    {
        $viewer = \Auth::user();
        if (!$viewer) {
            return '';
        }

        $user_events = EventEvents::select('event_events.*', 'follow.id as follow_id')
            ->withTrashed()
            ->leftJoin('follow', function($join) use($viewer) {
                $join->on('follow.recipient_id', '=', 'event_events.id')
                    ->where('recipient_type', EventEvents::class);
            })
            ->whereNotNull('follow.id')
            ->where('sender_type', get_class($viewer))
            ->where('sender_id', '=', $viewer->getKey())
            ->orderBy('follow.updated_at', 'DESC')
            ->get();

        $user_orders = EventTicketOrders::select()
            ->whereIn('event_id', $user_events ? $user_events->pluck('id') : [])
            ->where('buyer_type', get_class($viewer))
            ->where('buyer_id', '=', $viewer->getKey())
            ->get()
            ->keyBy('event_id');

        $refunded_tickets = EventTicketOrders::select()
            ->with('event')
            ->whereNotIn('event_id', $user_events ? $user_events->pluck('id') : [])
            ->where('buyer_type', get_class($viewer))
            ->where('buyer_id', '=', $viewer->getKey())
            ->where('status', 'LIKE', 'refunded')
            ->orderBy('updated_at', 'DESC')
            ->get();

        return view('widgets.events::MyTickets.index', ['user_events' => $user_events, 'user_orders' => $user_orders, 'refunded_tickets' => $refunded_tickets, 'statuses' => self::TICKET_STATUSES]);
    }
}
