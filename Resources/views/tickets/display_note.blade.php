<div class="modal fade" id="modal-ticket-order-view-note" tabindex="-1" role="dialog" aria-labelledby="modal-ticket-order-view-note-label" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="text">{{ __('Purchase note') }}</h3>
            </div>
            <div class="modal-body row justify-content-center">
                <div class="col-12 buy-ticket">
                    <div class="content">
                        <div class="mb-3">
                            <textarea class="form-control" name="note" rows="5" readonly>{{ $ticket_order->note }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link" data-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>