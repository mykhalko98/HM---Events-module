@if($categories->count())
	@foreach($categories as $key => $category)
		<span class="badge badge-primary"><a href="{{ route('events.category.events', ['filter_value' => $category->slug]) }}" class="text-reset">{{ $category->getName() }}</a></span>
	@endforeach
@endif