<div data-edit-type="simple_fields">
    <div class="event-tickets-row">
        <div class="row mx-0 w-100">
            <div class="col px-0">
                <div class="input-group mb-3">
                    <label for="ticket_type" class="w-100 d-block">{{ __('events::lang.Ticket type') }}</label>
                    <select name="ticket_type" class="form-control value_field" onchange="hm.events.toggleTicketType($(this))">
                        <option value="free" {{ isset($event) && $event->ticket_type == 'free' ? 'selected' : '' }}>{{ __('events::lang.Free') }}</option>
                        <option value="paid" {{ isset($event) && $event->ticket_type == 'paid' ? 'selected' : '' }}>{{ __('events::lang.Paid') }}</option>
                        <option value="combined" {{ isset($event) && $event->ticket_type == 'combined' ? 'selected' : '' }}>{{ __('events::lang.Combined') }}</option>
                    </select>
                </div>
            </div>
        </div>
        <div id="event-paid-ticket-details" {{ isset($event) && $event->ticket_type == 'paid' ? '' : 'style=display:none;' }}>
            @php $hidden = isset($event) && $event->ticket_type == 'paid' ? '' : ' hidden'; @endphp
            <div class="row mx-0 w-100">
                <div class="col px-0">
                    <div class="input-group mb-3">
                        <label for="ticket_price" class="w-100 d-block">{{ __('events::lang.Price') }}</label>
                        <input type="number" class="form-control value_field{{ $hidden }}" name="ticket_price" value="{{ $event->ticket->price ?? '' }}" min="1" aria-describedby="ticket_price_addon">
                        <div class="input-group-append">
                            <span class="input-group-text" id="ticket_price_addon">{{ settings()->get('hubmachine.api.stripe.currency', 'USD') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mx-0 w-100">
                <div class="col px-0">
                    <div class="input-group mb-3">
                        <label for="ticket_quantity" class="w-100 d-block">{{ __('events::lang.Quantity') }}</label>
                        <input type="number" class="form-control value_field{{ $hidden }}" name="ticket_quantity" value="{{ $event->ticket->quantity ?? '' }}" min="0">
                    </div>
                </div>
                <div class="col px-0">
                    <div class="input-group mb-3">
                        <label for="ticket_count_per_person" class="w-100 d-block">{{ __('events::lang.Count per person') }}</label>
                        <input type="number" class="form-control value_field{{ $hidden }}" name="ticket_count_per_person" value="{{ $event->ticket->count_per_person ?? '' }}" min="0">
                    </div>
                </div>
            </div>
        </div>
        <div id="event-combined-ticket-details" {{ isset($event) && $event->ticket_type == 'combined' ? '' : 'style=display:none;' }}>
            @php $hidden = isset($event) && $event->ticket_type == 'combined' ? '' : ' hidden'; @endphp
            @php $tickets = isset($event) ? $event->tickets()->get() : null; @endphp
            <ul class="nav mt-3 mb-2" id="combinedTicketTab" role="tablist">
                @if ($tickets != NULL && $tickets->count() > 0)
                    @foreach ($tickets as $ticket)
                        <li class="nav-item mb-2">
                            <a class="nav-link btn btn-primary mx-1 px-3 py-0 {{ $loop->index == 0 ? 'active' : ''}}" id="link-ticket-{{ $ticket ? $ticket->id : '' }}" data-target=".ticket-target-{{ $loop->index }}" @if($loop->index == 0) aria-selected="true" @endif data-toggle="tab" role="tab" data-ticket-name-inject="{{ $ticket->name }}">{{ $ticket->name }}</a>
                        </li>
                    @endforeach
                @endif
                <li class="nav-item mb-2">
                    <button class="btn btn-primary mx-1 px-3 py-0 btn-add-ticket" onclick="window.hm.events.createEnclosedTicket($(this))" data-url="{{ route('events.create_new_enclosed_ticket') }}">+</button>
                </li>
            </ul>
            <div class="tab-content" id="combinedTicketTabContent">
                @if ($tickets != NULL && $tickets->count() > 0)
                    @foreach ($tickets as $ticket)
                            @include('widgets.events::EventTickets.edit_enclosed_ticket')
                    @endforeach
                @endif
            </div>
        </div>
    </div>
    <div class="event-tickets-row">
        <div class="row mx-0 w-100">
            <div class="col px-0">
                <div class="input-group mb-3">
                    <label for="ticket_url" class="w-100 d-block">{{ __('events::lang.Ticket URL') }}</label>
                    <input type="text" value="{{ isset($event) ? $event->ticket_url : '' }}" name="ticket_url" class="form-control value_field" />
                </div>
            </div>
        </div>
    </div>
    <div class="wcm-group d-none">
        <button type="submit" class="btn btn-secondary btn-edit-save" data-editable-element>{{ __('events::lang.PUBLISH_BUTTON') }}</button>
        <button type="submit" class="btn btn-secondary btn-edit-draft" data-editable-element>{{ __('events::lang.SAVE_TO_DRAFT_BUTTON') }}</button>
        <button type="button" class="btn btn-primary btn-edit-delete" data-url="needRoute" data-toggle="modal" data-target="#modal-delete-confirmation" onclick="frontAdministration.deleteWidget(this)">{{ __('events::lang.DELETE_BUTTON') }}</button>
    </div>
</div>
