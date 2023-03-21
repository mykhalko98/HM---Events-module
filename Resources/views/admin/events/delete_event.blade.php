<div class="modal fade modal-confirm" id="modal-delete-confirmation" tabindex="-1" role="dialog" aria-labelledby="modal-delete-label"
     aria-hidden="true">
    <div class="modal-dialog modal-md
     modal-dialog-centered" role="document">

        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h3>{{ __('events::lang_admin.CONFIRM_MODAL_TITLE') }}
                        "{{ $event_title }}"
                    </h3>
                </div>
            </div>           
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="confirmAjax.delete(this)" data-url="{{ route('events.admin.event.delete', ['id' => $event_id])}}">{{ __('events::lang_admin.DELETE_BUTTON') }}</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('events::lang_admin.CANCEL_BUTTON') }}</button>
            </div>
        </div>

    </div>
</div>
