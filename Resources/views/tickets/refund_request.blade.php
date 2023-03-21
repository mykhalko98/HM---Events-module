<div class="modal fade" id="modal-ticket-order-refund-request" tabindex="-1" role="dialog" aria-labelledby="modal-ticket-order-refund-request-label" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="text">{{ __('Refund Request') }}</h3>
            </div>
            <div class="modal-body row justify-content-center">
                <div class="col-12 buy-ticket">
                    <div class="content">
                        <form action="{{ route('events.ticket_order.send_refund_request', [$ticket_order->getKey()]) }}" onsubmit="hm.events_payment.refundRequest(event, $(this));return false;">
                            <label for="description">Description</label>
                            <textarea class="form-control" name="description" aria-label="With textarea" rows="10"></textarea>
                            <small class="form-text text-muted">{{ __('Describe the reason') }}</small>
                            <button type="submit" class="btn btn-primary">{{ __('Confirm') }}</button>
                            <button type="button" class="btn btn-link" data-dismiss="modal">{{ __('Close') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>