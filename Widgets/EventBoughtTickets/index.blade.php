<h3 class="block-title">{{ __('Attendees') }}</h3>
@if(!$event_users->count() && !$refunded_tickets->count())
    <div class="message">
        <span>{{ __('events::lang.No attendees') }}</span>
    </div>
@else
    @if($event->ticket_type == 'free')
        @include('widgets.events::EventBoughtTickets.free_event', ['event' => $event, 'event_users' => $event_users, 'viewer' => $viewer])
    @else
        @include('widgets.events::EventBoughtTickets.not_free_event', ['event_users' => $event_users, 'ticket_orders' => $ticket_orders, 'refunded_tickets' => $refunded_tickets, 'statuses' => $statuses, 'viewer' => $viewer])
    @endif
@endif

@if($order_id = request()->get('order'))
    <button type="button"
            id="open-modal-ticket-order-refund-confirmation"
            class="d-none"
            data-url="{{ route('events.ticket_order.refund_confirmation', [$order_id]) }}"
            data-toggle="modal"
            data-target="#modal-ticket-order-refund-confirmation"></button>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                $('#open-modal-ticket-order-refund-confirmation').click();
            }, 500);
        });
    </script>
@endif