<div class="tab-pane fade" data-ticket-target role="tabpanel" id="{{ $ticket ? $ticket->id : '' }}" aria-labelledby="{{ $ticket ? $ticket->name.'-tab' : '' }}" data-grouping="enclosed_ticket">
    <div class="row mx-0 w-100 mx-0">
        <div class="col px-0">
            <div class="input-group mb-3">
                <label for="ticket_name" class="w-100 d-block">{{ __('events::lang.Ticket Name') }}</label>
                <input type="text" class="form-control value_field{{ $hidden }}" name="ticket_name[]" value="{{ $ticket ? $ticket->name : '' }}">
                @if ($ticket)
                    <button class="btn mx-1 p-0 align-self-center bg-transparent border-0 color-dark" onclick="window.hm.events.deleteTicket($(this))" data-url="{{ route('events.ticket.delete', ['id' => $ticket->id]) }}"><icon-image class="icon-basket-remove" data-icon="delete_forever"></icon-image></button>
                @endif
            </div>
        </div>
    </div>
    <div class="row mx-0 w-100">
        <div class="col px-0">
            <div class="input-group mb-3">
                <label for="ticket_details" class="w-100 d-block">{{ __('events::lang.Details') }}</label>
                <textarea class="form-control value_field{{ $hidden }}" name="ticket_details[]">{{ $ticket ? $ticket->details : '' }}</textarea>
            </div>
        </div>
    </div>
    <div class="row mx-0 w-100">
        <div class="col col-md-12 col-xl-6 pl-0 pr-md-0 pr-xl-3">
            <div class="input-group mb-3">
                <label for="ticket_price" class="w-100 d-block">{{ __('events::lang.Price') }}</label>
                <input type="number" class="form-control value_field{{ $hidden }}" name="ticket_price[]" value="{{ $ticket ? $ticket->price : '' }}" min="1" aria-describedby="ticket_price_addon">
                <div class="input-group-append">
                    <span class="input-group-text rounded" id="ticket_price_addon">{{ settings()->get('hubmachine.api.stripe.currency', 'USD') }}</span>
                </div>
            </div>
        </div>
        <div class="col col-md-12 col-xl-6 pr-0 pl-md-0 pl-xl-3">
            <div class="input-group mb-3">
                <label for="ticket_early_price" class="w-100 d-block">{{ __('events::lang.Early Price') }}</label>
                <input type="number" class="form-control value_field{{ $hidden }}" name="ticket_early_price[]" value="{{ $ticket ? $ticket->early_price : '' }}" min="0" aria-describedby="ticket_early_price_addon">
                <div class="input-group-append">
                    <span class="input-group-text rounded" id="ticket_early_price_addon">{{ settings()->get('hubmachine.api.stripe.currency', 'USD') }}</span>
                </div>
                <small id="ticket_early_price_expiry" class="input-group w-100 pt-1 datetimepicker_min_current">
                    <span class="input-group-addon">
                        <icon-image data-icon="calendar_today"></icon-image>
                    </span>
                    <input type="text"
                           data-inject-date
                           name="ticket_early_price_expiry[]"
                           value="{{ $ticket ? Carbon\Carbon::parse($ticket->early_price_expiry)->format('m/d/Y g:i A') : '' }}"
                           class="form-control border-0 pl-1 p-0 h-auto value_field{{ $hidden }}">
                </small>
            </div>
        </div>
    </div>
    <div class="row mx-0 w-100">
        <div class="col pl-0 pr-0 pr-sm-0 pr-xl-3 col-md-12 col-xl-6">
            <div class="input-group input-h-equel h-100 mb-3">
                <label for="ticket_quantity" class="w-100 d-block">{{ __('events::lang.Quantity') }}</label>
                <input type="number" class="form-control value_field{{ $hidden }}" name="ticket_quantity[]" value="{{ $ticket ? $ticket->quantity : '' }}" min="0">
            </div>
        </div>
        <div class="col pr-0 pl-0 pl-sm-0 pl-xl-3 col-md-12 col-xl-6">
            <div class="input-group input-h-equel h-100 mb-3">
                <label for="ticket_count_per_person" class="w-100 d-block">{{ __('events::lang.Count per person') }}</label>
                <input type="number" class="form-control value_field{{ $hidden }}" name="ticket_count_per_person[]" value="{{ $ticket ? $ticket->count_per_person : '' }}" min="0">
            </div>
        </div>
    </div>
</div>
