<div class="event-author-row row">
    <div class="col-auto">
        {!! $event->author ? $event->author->getAvatar(['class' => 'image img-thumbnail-sm'], true) : users()->getDefaultAvatar(['class' => 'image img-thumbnail-sm']) !!}
    </div>
    <div class="col pl-0">
        <div class="event-author-name">
            {{ __('events::lang.POSTED_BY') }}
            @if($event->author)
                <a href="{{ $event->author->getUrl() }}" class="name"><strong>{!! __($event->author->getName()) !!}</strong></a>
            @else
                {{ __('Deleted') }}
            @endif
        </div>
        <div class="event-author-date">
            @if(isset($event->created_at))
                {{ core()->time()->format(core()->time()->localize($event->created_at), 'short') }}
            @endif
        </div>
    </div>
</div>