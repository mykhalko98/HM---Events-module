<h1>{{__('events::lang.CATEGORIES_TITLE')}}</h1>

@if(isset($categories) && $categories->count())
    <div class="row mb-2">
        <div class="col-md-12">
            @foreach($categories as $key => $category)
                <a target="_blank" class="btn btn-primary btn-sm mr-1" href="{{route('events.category.events', ['filter_value' => $category->slug])}}">
                    {{ $category->getName() }}
                    <span class="badge badge-light"> {{ $category->events_number }} </span>
                </a>
            @endforeach
        </div>
    </div>
@else
    <span>{{__('events::lang.NO_CATEGORIES')}}</span>
@endif