<div class="event-tickets-row d-inline-flex w-100">
    @php $display_buy_button = !$viewer ? false : (isset($config->fields->buy_button) ? filter_var($config->fields->buy_button->value, FILTER_VALIDATE_BOOLEAN) : true);  @endphp
    <div class="left">
        <icon-image data-icon="confirmation_number" title="{{ __('events::lang.Ticket type') }}"></icon-image>
    </div>
    <div class="right w-100">
        @if($event->ticket_type == 'free')
            <div class="row w-100">
                <div class="col">
                    @if($display_buy_button)
                        @if(!$viewer->isFollows($event))
                            <button type="button"
                                    class="btn btn-primary btn-follow mb-3 d-block"
                                    data-url="{{ route('events.event.follow', ['event_id' => $event->getKey()]) }}"
                                    onclick="hm.events.follow($(this))">
                                {{ __('events::lang.Get a free ticket')  }}
                            </button>
                        @else
                            <div class="text-center font-weight-bold alert alert-info">{{ __('events::lang.You have already registered for the event') }}</div>
                        @endif
                    @endif
                </div>
            </div>
        @elseif($event->ticket_type == 'paid' && $event->ticket)
            <div class="row w-100">
                <div class="col pr-1">
                    <div class="input-group">
                        <div class="square-group active mb-1">
                            <p class="item-title">{{ __('events::lang.Price') }}</p>
                            <p class="item-price">{{ $event->ticket->price }} {{ settings()->get('hubmachine.api.stripe.currency', 'USD') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row w-100">
                <div class="col">
                    @if($display_buy_button)
                        @php $can_buy = $event->canBuyTicket($viewer, $event->ticket); @endphp
                        @if($can_buy['status'])
                            <button type="button"
                                    class="btn btn-primary d-block mx-auto"
                                    id="events-buy-ticket-button"
                                    data-url="{{ route('events.event.buy_ticket_modal', [$event->getLink(), 'ticket' => $event->ticket->getKey()]) }}"
                                    data-toggle="modal"
                                    data-target="#modal-buy-ticket"
                                    data-modal-url="ticket/{{ $event->ticket->getKey() }}/buy-ticket"
                            >{{ __('events::lang.Buy a ticket') }}</button>
                        @else
                            {!! $can_buy['message'] ?? 'n\a' !!}
                        @endif
                    @endif
                </div>
            </div>
        @elseif($event->ticket_type == 'combined' && $event->tickets)
            @php $combined_tickets = $event->tickets()->whereNotNull('name')->get(); @endphp
            <ul class="nav" id="combinedTicketTab" role="tablist">
                @foreach($combined_tickets as $combined_ticket)
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary mx-1 px-3 py-0 {{ $loop->index == 0 ? ' active' : ''}}" id="{{ $combined_ticket->name }}-tab" data-toggle="tab" data-target="#ticket-{{ $loop->index }}" @if($loop->index == 0) aria-selected="true" @endif href="#{{ $combined_ticket->name }}" role="tab" aria-controls="{{ $combined_ticket->name }}">{{ str_replace('_', ' ', ucfirst($combined_ticket->name)) }}</a>
                    </li>
                @endforeach
            </ul>
            <div class="tab-content" id="combinedTicketTabContent">
                @php $time = time(); @endphp
                @foreach($combined_tickets as $combined_ticket)
                    @php
                        $event_is_active = $event->end_time->getTimestamp() > $time;
                        $early_price_is_active = $combined_ticket->early_price_expiry->getTimestamp() > $time;
                    @endphp
                    <div class="tab-pane fade {{ $loop->index == 0 ? 'show active' : '' }}" data-ticket-id="{{ $loop->index }}" role="tabpanel" id="ticket-{{ $loop->index }}">
                        <div id="{{ $combined_ticket->name }}" aria-labelledby="{{ $combined_ticket->name }}-tab">
                            <div class="row w-100">
                                <div class="col">
                                    <icon-image data-icon="description"></icon-image>
                                    <p class="ml-2">{{ $combined_ticket->details }}</p>
                                </div>
                            </div>
                            <div class="row w-100">
                                <div class="col pr-1">
                                    <div class="input-group">
                                        <div class="square-group {{ !$early_price_is_active && $event_is_active  ? 'active' : 'faded' }} mb-1">
                                            <p class="item-title">{{ __('events::lang.Price') }}</p>
                                            <p class="item-price">{{ $combined_ticket->price }} {{ settings()->get('hubmachine.api.stripe.currency', 'USD') }}</p>
                                        </div>
                                        <div class="pl-1">
                                            <div class="square-group {{ $early_price_is_active && $event_is_active ? 'active' : 'faded' }} mb-1">
                                                <p class="item-title">{{ __('events::lang.Price') }}</p>
                                                <p class="item-price">{{ $combined_ticket->early_price }} {{ settings()->get('hubmachine.api.stripe.currency', 'USD') }}</p>
                                            </div>
                                            <p class="item-date">
                                                {{ core()->time()->localize($combined_ticket->early_price_expiry)->format('M d, Y') }}
                                                {{ $early_price_is_active && $event_is_active ? 'active' : '(expired)' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row w-100">
                                <div class="col-12">
                                    @if($event_is_active && $display_buy_button)
                                        @php $can_buy = $event->canBuyTicket($viewer, $combined_ticket); @endphp
                                        @if($can_buy['status'])
                                            <button type="button"
                                                    class="btn btn-primary d-block mx-0"
                                                    id="events-buy-ticket-button"
                                                    data-url="{{ route('events.event.buy_ticket_modal', [$event->getLink(), 'ticket' => $combined_ticket->getKey()]) }}"
                                                    data-toggle="modal"
                                                    data-target="#modal-buy-ticket"
                                                    data-modal-url="ticket/{{ $combined_ticket->getKey() }}/buy-ticket"
                                            >{{ __('events::lang.Buy a ticket') }}</button>
                                        @else
                                            {!! $can_buy['message'] ?? 'n\a' !!}
                                        @endif
                                    @endif
                                </div>
                                <div class="col-12">
                                    @if (!empty($event->ticket_url))
                                        <div class="event-tickets-row d-inline-flex">
                                            <div class="left">
                                                <icon-image data-icon="language" title="{{ __('events::lang.Ticket URL') }}"></icon-image>
                                            </div>
                                            <div class="right">
                                                <a href="{{ $event->ticket_url }}" class="text-break mx-0">{{ $event->ticket_url }}</a>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
