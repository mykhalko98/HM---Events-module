<div class="modal fade" id="modal-event-unfollow-confirmation" tabindex="-1" role="dialog" aria-labelledby="modal-event-unfollow-confirmation-label" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="text">{{ $user_id ? __('events::lang.Delete user') : __('events::lang.Delete ticket') }}</h3>
            </div>
            <div class="modal-body row justify-content-center">
                <div class="col-12 buy-ticket">
                    <div class="content">
                        <h5 class="text-center">{{ $user_id ? __('events::lang.Are you sure you want to delete this user?') : __('events::lang.Are you sure you want to delete this ticket?') }}</h5>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button"
                        class="btn btn-primary"
                        data-url="{{ route('events.event.unfollow', [$event->getKey(), 'user_id' => $user_id ?? '']) }}"
                        onclick="javascript: return hm.events.unfollow($(this));">{{ __('Confirm') }}</button>
                <button type="button" class="btn btn-link" data-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>