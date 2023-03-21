@if(in_array($filter_by, ['category', 'tag']))
    <h3 class="mb-3">{{__('events::lang.OTHER_EVENTS_TITLE')}}, {{ ['tag' => 'Tag', 'category' => 'Category'][$filter_by] }}: {{ $filter_value }}</h3>
@else
    <h3 class="mb-3">{{__('events::lang.OTHER_EVENTS_TITLE')}}</h3>
@endif
@if(count($events))
    <div class="row mb-2">
    {{-- todo: filter, reverse only for test --}}
    @foreach($events->reverse() as $key => $event)
        <div class="col-md-4">
            <div class="card">
                <a href="{{ $event->getUrl() }}" class="card-link">
                    <img src="{!! $event->getCover(860, 484) ?: ($media_placeholder ?? media()->get(settings()->get('hubmachine.media.placeholder'))->getThumbnail(860, 484)) !!}" class="bd-placeholder-img bd-placeholder-img-lg w-100 img-fluid" alt="{{ $event->getTitle() }}" loading="lazy">
                    @if(isset($event->ticket_type))
                        <span class="badge badge-white">{{ $event->getFrontTicketType() }}</span>
                    @endif
                </a>

                <div class="card-body">
                    <div class="card-categories mb-2">
                        @foreach($event ? $event->categories : collect([]) as $key => $category)
                            <span class="badge badge-light"><a href="{{ route('events.category.events', ['filter_value' => $category->slug]) }}">{{ $category->getName() }}</a></span>
                        @endforeach
                    </div>

                    <div class="card-date">{{ core()->time()->format(core()->time()->localize($event->start_time), 'medium') }}</div>
                    <h3 class="card-title"><a href="{{ $event->getUrl() }}">{{ $event->getTitle() }}</a></h3>
                    <p class="card-text">{!! $event->getTeaser() !!}</p>
                    @if(isset($event->location))                        
                        <div class="card-location">{{ $event->location }}</div>
                    @endif

                    <div class="card-tags mb-2">
                        @foreach($event ? $event->tags : collect([]) as $key => $tag)
                            <span class="badge badge-light"><a href="{{ route('events.tag.events', ['filter_value' => $tag->slug]) }}">{{ $tag->getName() }}</a></span>
                        @endforeach
                    </div>

                    <div class="card-author d-none">
                        @if($event->author)
                            {!! __('events::lang.BY_AUTHOR', ['author' => $event->author->getName()]) !!}
                        @else
                            {{ __('Deleted') }}
                        @endif
                    </div>
                    <a href="{{ $event->getUrl() }}" class="btn btn-link p-0 card-more d-none">{{__('events::lang.CONTINUE_READING_BUTTON')}}</a>
                </div>

            </div>
        </div>
    @endforeach
    </div>
@else
    @switch($filter_by)
        @case('category')
            <span>{{trans_choice('events::lang.NO_EVENTS_IN_CATEGORY_ALERT', (int)($filter_value === 'all'))}}</span>
        @break
        @default
            <span>{{__('events::lang.NO_PUBLISHED_EVENTS_ALERT')}}</span>
    @endswitch
@endif