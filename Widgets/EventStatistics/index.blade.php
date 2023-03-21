<h2>{{ __('Statistics') }}</h2>
<div class="items">
    <div class="item border-bottom py-2 px-1">
        <h6 class="font-weight-bold">{{ __('Attendees') }}</h6>
        <span><icon-image data-icon="people"></icon-image> {{ $event_users->count() ?? __('Not found') }}</span>
        <br>
        <span>{{ __('People who are going to event') }}</span>
    </div>
    @if(!is_null($refunds))
        <div class="item border-bottom py-2 px-1">
            <h6 class="font-weight-bold">{{ __('Refunds') }}</h6>
            <span><icon-image data-icon="undo"></icon-image> {{ $refunds }}</span>
            <br>
            <span>{{ __('People who refunded ticket') }}</span>
        </div>
    @endif
</div>