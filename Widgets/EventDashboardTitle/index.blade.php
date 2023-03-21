<div class="dashboard-title mb-3">
    <a href="{{ $event->getUrl() ? $event->getUrl() : 'javascript:history.back()' }}" class="btn btn-back"><icon-image data-icon="chevron_left"></icon-image> {{ __('Back to event') }}</a>
    <div class="inner">
        <a href="{{ $event->getUrl() }}" class="event-link event-link-img">
            <img src="{!! $event->getCover(860, 484) ?: ($media_placeholder ?? media()->get(settings()->get('hubmachine.media.placeholder'))->getThumbnail(860, 484)) !!}" class="img-fluid-200 border-20" alt="{{ $event->getTitle() }}" loading="lazy">
        </a>
        <h1 class="event-title page-title">
            <a href="{{ $event->getUrl() }}" class="event-link">{{ $event->getTitle() }}</a>
        </h1>
        <div class="dropdown dropdown-options">
            <button class="btn btn-light" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <icon-image data-icon="more_horiz"></icon-image>
            </button>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="wc-settings">
                <a href="{{ route('events.event.edit', [$event->getLink()]) }}" class="dropdown-item">
                    <icon-image data-icon="edit"></icon-image>
                    {{ __('events::lang.EDIT_BUTTON') }}
                </a>
            </div>
        </div>
    </div>
    
    {{-- <div class="row mb-3">
        <div class="col-6">
            <a href="{{ $event->getUrl() }}" class="btn btn-link pl-0"><icon-image data-icon="west"></icon-image> {{ __('Back to Event') }}</a>
            <h5 class="d-inline-block text-truncate w-100" title="{{ $event->title }}"><strong>{!! $event->title !!}</strong></h5>
        </div>
        <div class="col-6">
            <a href="{{ route('reviewqueue.lobby', ['event' => $event->getKey()]) }}" class="btn btn-link float-right">{{ __('Lobby') }}</a>
            <a href="{{ route('events.admin.main', ['event_id' => $event->getKey()]) }}" class="btn btn-link float-right" target="_blank">{{ __('Admin') }}</a>
            <a href="{{ route('reviewqueue.admin.event', ['event_id' => $event->getKey()]) }}" class="btn btn-link float-right {{ !$queue_enabled ? ' d-none' : ''}}" target="_blank" id="active_queue_btn" data-id="{{ $event->getKey() }}">{{ __('Active Queue') }}</a>
        </div>
    </div> --}}
</div>