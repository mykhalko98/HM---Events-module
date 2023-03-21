<h1>{{__('events::lang.TAGS_TITLE')}}</h1>
<div class="row mb-2">
	<div class="col-md-12">
		@if(count($tags))
			@foreach($tags as $tag)
				<a target="_blank" class="badge badge-primary" href="{{ route('events.tag.events', ['filter_value' => $tag['name']]) }}"> {{ $tag['translated'] }}
					<span class="badge badge-light">{{ $tag['events_number'] }}</span>
				</a>
			@endforeach
		@else
			<span>{{__('events::lang.NO_EVENTS_IN_TAG_ALERT')}}</span>
		@endif
	</div>
</div>