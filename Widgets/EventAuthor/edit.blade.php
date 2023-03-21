<div class="event-author-row row">
    <div class="col-auto">
        @if(isset($event))
            {!! $event->author ? $event->author->getAvatar(['class' => 'image img-thumbnail-sm'], true) : users()->getDefaultAvatar(['class' => 'image img-thumbnail-sm']) !!}
        @else
            {!! auth()->user()->getAvatar(['class' => 'image img-thumbnail-sm'], true) !!}
        @endif
    </div>
    <div class="col pl-0">
        <div class="event-author-name">
            {{ __('events::lang.POSTED_BY') }}
            @if(isset($event))
                @if($event->author)
                    <a href="{{ $event->author->getUrl() }}" class="name"><strong>{!! __($event->author->getName()) !!}</strong></a>
                @else
                    {{ __('Deleted') }}
                @endif
            @else
                <a href="{{ auth()->user()->getUrl() }}" class="name"><strong>{!! __(auth()->user()->getName()) !!}</strong></a>
            @endif
        </div>
        <div class="event-author-date">
            @if(isset($event) && isset($event->created_at))
                {{ core()->time()->format(core()->time()->localize($event->created_at), 'short') }}
            @else
                {{ date('d M Y') }}
            @endif
        </div>
    </div>
</div>