<div class="modal fade" id="modal-buy-ticket" tabindex="-1" data-id="{{ $event->getKey() }}" role="dialog" aria-labelledby="modal-buy-ticket-label" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="text">{{ __('Buy a ticket') }}</h3>
            </div>
            <div class="modal-body row justify-content-center">
                <div class="col-12 buy-ticket">
                    <div class="content">
                        <div class="payment-form pt-1 col-10 mx-auto">
                            <form id="payment-form" action="{{ route('events.ticket.payment', [$ticket->getKey()]) }}" method="post">
                                <div class="form-group">
                                    <div id="payment-request-button">
                                        <!-- A Stripe Element will be inserted here. -->
                                    </div>
                                </div>
                                <hr>
                                <div class="form-group">
                                    <input type="hidden" name="ticket" value="{{ $ticket->getKey() }}" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="email">{{ __('events::lang.Email') }}</label>
                                    <input type="email" name="email" value="{{ $viewer->email }}" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="name">{{ __('events::lang.Cardholder name') }}</label>
                                    <input type="text" name="name" value="{!! $viewer->getName() !!}" class="form-control">
                                </div>
                                <div class="form-group {{ $ticket->count_per_person != 1 && $available_tickets !== 1 ? '' : 'd-none' }}">
                                    <label for="name">{{ __('events::lang.Number Of Tickets') }} {{ $available_tickets !== 1  && $ticket->count_per_person > 1 ? '('.__("events::lang.max: count", ['count' => $available_tickets ? min($available_tickets, $ticket->count_per_person) : $ticket->count_per_person]).')' : '' }}</label>
                                    <input type="number" name="count" value="1" min="1" max="{{ $available_tickets !== 1  && $ticket->count_per_person > 1 ? min($available_tickets, $ticket->count_per_person) : '' }}" class="form-control">
                                </div>
                                <div class="form-group d-none">
                                    <label for="amount">{{ __('events::lang.Amount') }}</label>
                                    @if($event->ticket_type == 'combined' && \Carbon\Carbon::now()->addSeconds(30)->timestamp < $ticket->early_price_expiry->timestamp)
                                        <input type="number" name="amount" value="{{ (int)($ticket->early_price*100) }}" class="form-control" readonly>
                                    @else
                                        <input type="number" name="amount" value="{{ $ticket->price*100 }}" class="form-control" readonly>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label for="card-element">{{ __('events::lang.Credit or debit card') }}</label>
                                    <div id="card-element" class="form-control form-control-st">
                                        <!-- A Stripe Element will be inserted here. -->
                                    </div>

                                    <!-- Used to display Element errors. -->
                                    <div id="card-errors" role="alert"></div>
                                </div>
                                <button id="payment-button" class="btn btn-secondary w-100 mt-3" disabled>
                                    @if($event->ticket_type == 'combined' && \Carbon\Carbon::now()->addSeconds(30)->timestamp < $ticket->early_price_expiry->timestamp)
                                        <span class="text-center my-1">Pay <span id="amount-to-pay">{{ $ticket->early_price }}</span> {{ settings()->get('hubmachine.api.stripe.currency', 'USD') }}</span>
                                    @else
                                        <span class="text-center my-1">Pay <span id="amount-to-pay">{{ $ticket->price }}</span> {{ settings()->get('hubmachine.api.stripe.currency', 'USD') }}</span>
                                    @endif
                                </button>
                            </form>
                        </div>
                    </div>
                    <script>
                        $(document).ready(function() {
                            hm.events_payment.initialize('{{ config('services.stripe.mode') == 'test' ? config('services.stripe.test_key') : config('services.stripe.key') }}');
                        });
                    </script>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-lg btn-link" data-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>