@if($tags->count())
	@foreach($tags as $key => $tag)
		<span class="badge badge-primary"><a href="{{ route('events.tag.events', ['filter_value' => $tag->slug]) }}" class="text-reset">{{ $tag->getName() }}</a></span>
	@endforeach
@endif