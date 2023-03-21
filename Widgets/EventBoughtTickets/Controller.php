<?php

namespace Modules\Events\Widgets\EventBoughtTickets;

use Illuminate\Support\Facades\View;
use Modules\Events\Entities\EventTicketOrders;
use Modules\Events\Widgets\EventsWidgets;
use Hubmachine\Friendships\Status;
use Hubmachine\Users\Models\User;

class Controller extends EventsWidgets
{
    public function handle()
    {
        $event = View::shared('event');
        !$event && $event = events()->getEventFromUrlParams();
        if (!$event) {
            return '';
        }

        $event_users = User::select('users.id', 'users.name', 'users.username', 'follow.id as follow_id', 'follow.created_at')
            ->leftJoin('follow', function($join) {
                $join->on('follow.sender_id', 'users.id')
                    ->where('sender_type', User::class);
            })
            ->whereNotNull('follow.id')
            ->where('follow.recipient_type', get_class($event))
            ->where('follow.recipient_id', '=', $event->getKey())
            ->where('follow.status', '=', Status::ACCEPTED)
            ->orderBy('follow.created_at', 'DESC')
            ->get();

        $ticket_orders = collect([]);
        $refunded_tickets = collect([]);
        if ($event->ticket_type == 'paid' || $event->ticket_type == 'combined') {
            $ticket_orders = EventTicketOrders::select()
                ->whereHasMorph('buyer', [\Hubmachine\Users\Models\User::class])
                ->where('event_id', '=', $event->getKey())
                ->whereIn('status', ['succeeded', 'refund_request'])
                ->orderBy('created_at', 'DESC')
                ->get()
                ->keyBy('buyer_id');

            $refunded_tickets = EventTicketOrders::select()
                ->whereHasMorph('buyer', [\Hubmachine\Users\Models\User::class])
                ->whereNotIn('buyer_id', $event_users->pluck('id'))
                ->where('event_id', '=', $event->getKey())
                ->where('status', 'LIKE', 'refunded')
                ->orderBy('created_at', 'DESC')
                ->get()
                ->keyBy('buyer_id');
        }

        return view('widgets.events::EventBoughtTickets.index', ['event' => $event, 'event_users' => $event_users, 'ticket_orders' => $ticket_orders, 'refunded_tickets' => $refunded_tickets, 'statuses' => self::TICKET_STATUSES, 'viewer' => \Auth::user()]);
    }
}
