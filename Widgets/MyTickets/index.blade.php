@php
    $media_placeholder = media()->get(settings()->get('hubmachine.media.placeholder'))->getThumbnail(860, 484);
    $viewer = auth()->user();
@endphp
<div class="dashboard-tickets mb-3">
    <h3 class="block-title">My Tickets</h3>
    @if($user_events->count())
        <div class="items">
            @foreach($user_events as $event)
                <div class="item" data-id="{{ $event->follow_id }}">
                    <div class="item-event">
                        @php
                        $event_cover = $event->getCover(860, 484);
                        $user_order  = $event->ticket_type !== 'free' ? $user_orders[$event->getKey()]: null;
                        @endphp
                        <a href="{{ empty($event->deleted_at) ? $event->getUrl() : 'javascript:void(0);' }}" class="card-link">
                            <img src="{!! $event_cover ?: $media_placeholder !!}" class="bd-placeholder-img bd-placeholder-img-lg w-100 img-fluid" alt="{{ $event->getTitle() }}" loading="lazy">
                            @if($event->ticket_type !== 'free')
                                <span class="badge badge-{{ $statuses[$user_order->status]['badge'] }}">{{ $statuses[$user_order->status]['label'] }}</span>
                            @endif
                        </a>
                        <div class="info">
                            <div class="date">
                                <span title="{{ core()->time()->format(core()->time()->localize($event->start_time), 'short') }}">{{ core()->time()->format(core()->time()->localize($event->start_time), 'short') }}</span>
                            </div>
                            @if(empty($event->deleted_at))
                                <a href="{{ $event->getUrl() }}" target="_blank" class="title">{{ $event->getTitle() }}</a>
                            @else
                                <del><a href="javascript:void(0);" class="title">{{ $event->getTitle() }}</a></del>
                            @endif
                            <div class="location">{{ $event->location }}</div>
                        </div>
                    </div>
                    <div class="item-count">
                        @if($event->ticket_type !== 'free')
                            {{ $user_order->count }} {{ trans_choice('events::lang.Tickets', $user_order->count) }}
                        @else
                            &nbsp;
                        @endif
                    </div>
                    <div class="item-price">
                        @if($event->ticket_type !== 'free')
                            {{ number_format($user_order->ticket->price*$user_order->count, 2) }} {{ $user_order->transactions ? strtoupper($user_order->transactions->first()->currency) : settings()->get('hubmachine.api.stripe.currency', 'USD') }}
                        @else
                            {{ __('events::lang.FREE') }}
                        @endif
                    </div>
                    <div class="item-order">
                        @if($event->ticket_type !== 'free')
                            {{ __('Order number:') }}
                            <span class="count">{{ $user_order->getKey() }}</span>
                        @else
                            &nbsp;
                        @endif
                    </div>
                    <div class="item-options text-right">
                        @if($event->ticket_type == 'free' || $user_order->status == 'succeeded')
                            <div class="dropdown dropdown-options ml-auto">
                                <button class="btn btn-link p-0" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <icon-image data-icon="more_horiz"></icon-image>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="wc-settings">
                                    @if($event->ticket_type == 'free')
                                        <button type="button"
                                                class="btn btn-link text-dark dropdown-item"
                                                data-url="{{ route('events.event.unfollow_confirmation', [$event->getKey()]) }}"
                                                data-toggle="modal"
                                                data-target="#modal-event-unfollow-confirmation">
                                            <icon-image data-icon="undo"></icon-image>
                                            {{ __('events::lang.Delete ticket') }}
                                        </button>
                                    @else
                                        <button type="button"
                                                class="btn btn-link text-dark dropdown-item"
                                                data-url="{{ route('events.ticket_order.refund_request', [$user_order->getKey()]) }}"
                                                data-toggle="modal"
                                                data-target="#modal-ticket-order-refund-request">
                                            <icon-image data-icon="undo"></icon-image>
                                            {{ __('events::lang.Refund request') }}
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @else
                            &nbsp;
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if($refunded_tickets->count())
        <div class="items refunded-tickets{{ $user_events->count() ? ' custom-border-top' : '' }}">
            @foreach($refunded_tickets as $refunded_ticket)
                <div class="item">
                    <div class="item-event">
                        @php
                            $event = $refunded_ticket->event;
                            $event_cover = $event ? $event->getCover(860, 484) : null;
                        @endphp
                        <a href="{{ $event ? $event->getUrl() : 'javascript:void(0);' }}" class="card-link">
                            <img src="{!! $event_cover ?: $media_placeholder !!}" class="bd-placeholder-img bd-placeholder-img-lg w-100 img-fluid" alt="{{ $event ? $event->getTitle() : '' }}" loading="lazy">
                            @if(isset($refunded_ticket->status))
                                <span class="badge badge-{{ $statuses[$refunded_ticket->status]['badge'] }}">{{ $statuses[$refunded_ticket->status]['label'] }}</span>
                            @endif
                        </a>
                        <div class="info">
                            <div class="date">
                                @if($event)
                                    <span title="{{ core()->time()->format(core()->time()->localize($event->start_time), 'short') }}">{{ core()->time()->format(core()->time()->localize($event->start_time), 'short') }}</span>
                                @else
                                    <span>&nbsp;</span>
                                @endif
                            </div>
                            @if($event)
                                <a href="{{ $event->getUrl() }}" target="_blank" class="title">{{ $event->getTitle() }}</a>
                            @else
                                <del><a href="javascript:void(0);" class="title">{{ __('events::lang.Deleted') }}</a></del>
                            @endif
                            <div class="location">{!! $event ? $event->location : '&nbsp;' !!}</div>
                        </div>
                    </div>
                    <div class="item-count">
                        {{ $refunded_ticket->count }} {{ trans_choice('events::lang.Tickets', $refunded_ticket->count) }}
                    </div>
                    <div class="item-price">
                        @if(isset($refunded_ticket->ticket))
                            {{ number_format($refunded_ticket->ticket->price*$refunded_ticket->count, 2) }} {{ $refunded_ticket->transactions ? strtoupper($refunded_ticket->transactions->first()->currency) : settings()->get('hubmachine.api.stripe.currency', 'USD') }}
                        @else
                            &nbsp;
                        @endif
                    </div>
                    <div class="item-order">
                        {{ __('Order number:') }}
                        <span class="count">{{ $refunded_ticket->getKey() }}</span>
                    </div>
                    <div class="item-options text-right">
                        &nbsp;
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if($user_events->count() == 0 && $refunded_tickets->count() == 0)
        <h5 class="text-muted">{{ __('No tickets') }}</h5>
    @endif
</div>