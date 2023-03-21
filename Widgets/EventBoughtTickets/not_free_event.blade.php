@if($event_users->count())
    <div class="items">
        @foreach($event_users as $event_user)
            <div class="item w-100 d-inline-block mb-3">
                <div class="item-user w-50">
                    @php
                        $order = $ticket_orders[$event_user->getKey()];
                    @endphp
                    {!! $event_user->getAvatar(['class' => 'image img-thumbnail-sm'], true) !!}
                    <div class="info pl-3">
                        <a href="{{ $event_user->getUrl() }}" target="_blank" class="name">{{ $event_user->getName() }}</a>
                        <div class="date">
                            <span class="text-muted">{{core()->time()->format(core()->time()->localize($event_user->created_at), 'short') }}</span>
                        </div>
                    </div>
                </div>
                <div class="item-count pl-4">
                    {{ $order->count }} {{ trans_choice('events::lang.Tickets', $order->count) }}
                </div>
                <div class="item-status pl-4">
                    <span class="badge badge-pill badge-{{ $statuses[$order->status]['badge'] }} p-2">{{ $statuses[$order->status]['label'] }}</span>
                </div>
                <div class="item-price pl-4 left-0">
                    @if($order->transactions)
                        {{ $order->transactions->first()->amount }} {{ strtoupper($order->transactions->first()->currency) }}
                    @else
                        {{ __('events::lang.Transaction not found') }}
                    @endif
                </div>
                @if($order->status == 'succeeded' || $order->status == 'refund_request')
                    <div class="item-options text-right float-right">
                        <div class="dropdown dropdown-options ml-auto">
                            <button class="btn btn-link p-0" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <icon-image data-icon="more_horiz"></icon-image>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="wc-settings">
                                <button type="button"
                                        class="btn btn-link dropdown-item text-dark py-2"
                                        data-url="{{ route('events.ticket_order.refund_confirmation', [$order->getKey()]) }}"
                                        data-toggle="modal"
                                        data-target="#modal-ticket-order-refund-confirmation">
                                    <icon-image data-icon="undo"></icon-image>
                                    {{ __('events::lang.Refund') }}
                                </button>

                                @if($event_user->getKey() != $viewer->getKey() && core()->isNodeEnabled() && Module::has('Inbox') && Module::find('Inbox')->isEnabled() && $event_user->allowedMessaging())
                                    <button class="btn btn-link dropdown-item text-dark py-2"
                                            onclick="javascript:whchat.openConversation(event, null, 'widget', this, {{ $event_user->getKey() }});">
                                        <icon-image data-icon="chat_bubble_outline"></icon-image><span>{{ __('users::lang.MESSAGES') }}</span>
                                    </button>
                                @endif

                                @if(!empty($order->note))
                                    <button type="button"
                                            class="btn btn-link dropdown-item text-dark py-2"
                                            data-url="{{ route('events.ticket_order.view_note', [$order->getKey()]) }}"
                                            data-toggle="modal"
                                            data-target="#modal-ticket-order-view-note">
                                        <icon-image data-icon="notes"></icon-image>
                                        {{ __('events::lang.Note') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endif

@if($refunded_tickets->count())
    <div class="items refunded-tickets">
        <h5 class="py-3">{{ __('events::lang.Refunds') }}</h5>
        @foreach($refunded_tickets as $order)
            <div class="item w-100 d-inline-block mb-3">
                <div class="item-user w-50">
                    @php
                        $event_user = $order->buyer;
                    @endphp
                    {!! $event_user->getAvatar(['class' => 'image img-thumbnail-sm'], true) !!}
                    <div class="info pl-3">
                        <a href="{{ $event_user->getUrl() }}" target="_blank" class="name">{{ $event_user->getName() }}</a>
                        <div class="date">
                            <span class="text-muted">{{core()->time()->format(core()->time()->localize($event_user->created_at), 'short') }}</span>
                        </div>
                    </div>
                </div>
                <div class="item-count pl-4">
                    {{ $order->count }} {{ trans_choice('events::lang.Tickets', $order->count) }}
                </div>
                <div class="item-status pl-4">
                    <span class="badge badge-pill badge-{{ $statuses[$order->status]['badge'] }} p-2">{{ $statuses[$order->status]['label'] }}</span>
                </div>
                <div class="item-price pl-4 left-0">
                    @if($order->transactions)
                        {{ $order->transactions->first()->amount }} {{ strtoupper($order->transactions->first()->currency) }}
                    @else
                        {{ __('events::lang.Transaction not found') }}
                    @endif
                </div>
                <div class="item-options text-right float-right">
                    <div class="dropdown dropdown-options ml-auto">
                        <button class="btn btn-link p-0" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <icon-image data-icon="more_horiz"></icon-image>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="wc-settings">

                            @if($event_user->getKey() != $viewer->getKey() && core()->isNodeEnabled() && Module::has('Inbox') && Module::find('Inbox')->isEnabled() && $event_user->allowedMessaging())
                                <button class="btn btn-link dropdown-item text-dark py-2"
                                        onclick="javascript:whchat.openConversation(event, null, 'widget', this, {{ $event_user->getKey() }});">
                                    <icon-image data-icon="chat_bubble_outline"></icon-image><span>{{ __('users::lang.MESSAGES') }}</span>
                                </button>
                            @endif

                            @if(!empty($order->note))
                                <button type="button"
                                        class="btn btn-link dropdown-item text-dark py-2"
                                        data-url="{{ route('events.ticket_order.view_note', [$order->getKey()]) }}"
                                        data-toggle="modal"
                                        data-target="#modal-ticket-order-view-note">
                                    <icon-image data-icon="notes"></icon-image>
                                    {{ __('events::lang.Note') }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif