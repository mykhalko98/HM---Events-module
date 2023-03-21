<div class="modal fade" id="modal-ticket-order-refund-confirmation" tabindex="-1" role="dialog" aria-labelledby="modal-ticket-order-refund-confirmation-label" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="text">{{ __('Refund details') }}</h3>
            </div>
            <div class="modal-body row justify-content-center">
                <div class="col-12 buy-ticket">
                    <div class="content">
                        <div class="order-details pt-1 mx-auto">
                            <div class="item d-flex">
                                <div class="item-user w-100">
                                    @php $buyer = $ticket_order->buyer; @endphp
                                    {!! $buyer->getAvatar(['class' => 'image img-thumbnail-sm'], true) !!}
                                    <div class="info pl-3">
                                        <a href="{{ $buyer->getUrl() }}" target="_blank" class="name">{{ $buyer->getName() }}</a>
                                        <div class="date">
                                            <span class="text-muted">{{ core()->time()->format(core()->time()->localize($ticket_order->created_at), 'medium') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if($ticket_order->status == 'refund_request' && $ticket_order->note)
                                <div class="pt-5">
                                    <h4 class="text-center">{{ __('Asked for refund') }}</h4>
                                    <blockquote>{{ $ticket_order->note }}</blockquote>
                                </div>
                            @endif
                            <div class="pt-5">
                                <h4 class="text-center">{{ __('Refund') }}: {{ $ticket_order->ticket->price*$ticket_order->count }} {{ $ticket_order->transactions ? strtoupper($ticket_order->transactions->first()->currency) : settings()->get('hubmachine.api.stripe.currency', 'USD') }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button"
                        class="btn btn-primary"
                        data-url="{{ route('events.ticket_order.refund', [$ticket_order->getKey()]) }}"
                        onclick="hm.events_payment.refund(event, $(this))">{{ __('Confirm') }}</button>
                <button type="button" class="btn btn-link" data-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>