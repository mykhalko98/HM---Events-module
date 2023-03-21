@extends('layouts.admin')

@section('content')
    <div class="container">
	    <div class="row">
		    <div class="col-12">
	            <button class="btn btn-sm btn-secondary float-right"
                        data-url="{{route('events.admin.settings')}}"
                        data-toggle="modal"
                        data-target="#modal-events_admin_settings">{{ __('events::lang_admin.SETTINGS_BUTTON') }}</button>
	            <h1 class="page-title">{{ __('events::lang_admin.EVENTS_TITLE') }}</h1>
	        </div>
        </div>

	    <div class="row justify-content-center">
		    <aside id="aside-left" class="col-3">
			    <form action="index.blade.php" method="" accept-charset="utf-8">
                    <select class="form-control custom-select mb-3" onchange="window.location.href = this.value">
                        <option value="{{ route('events.admin.events', ['filter_by' => 'all']) }}">{{ __('events::lang_admin.All') }}</option>
                        <option value="{{ route('events.admin.events', ['filter_by' => 'category', 'filter_value' => 'all']) }}" {{ ['', 'selected'][$filter_by === 'category'] }}>{{ __('events::lang_admin.CATEGORY_OPTION') }}</option>
                        <option value="{{ route('events.admin.events', ['filter_by' => 'tag',      'filter_value' => 'all']) }}" {{ ['', 'selected'][$filter_by === 'tag'] }}>{{ __('events::lang_admin.TAG_OPTION') }}</option>
			    	</select>
			    </form>
                @if(count($filter_menu_items))
                <ul class="list-unstyled pl-15px">
                    @foreach($filter_menu_items as $menu_item)
                        <li {{ $menu_item['item_value'] == $filter_value ? 'class=active' : '' }}><a href="{{ route('events.admin.events', ['filter_by' => $filter_by, 'filter_value' => $menu_item['item_value']]) }}">{{ $menu_item['item_text'] }} ({{ $menu_item['events_number']}})</a></li>
                    @endforeach
                </ul>
                @endif
            </aside>

            <div id="main-content" class="col-9">
                @if($events->count())
                    <div class="hmtable-wrapper">
                        <div class="hmtable-container o-x-auto o-y-hidden mb-2">

                            <div class="text-center">
                                <div class="spinner-border" style="width: 3rem; height: 3rem;" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                            </div>

                            <table id="events_table" class="table d-none">
                                <thead class="hmtable-list-head noselect">
                                    <tr>
                                        @foreach ($fields as $field_name => $data)
                                            <th data-field-name="{{$field_name}}" class="{{ !empty($data['disabled']) ? 'column-disabled' : '' }} {{!empty($data['sticky']) ? 'sticky-enabled active' : ''}}">
                                                <div data-width="{{isset($data['width']) ? $data['width'] : ''}}" class="sticky-head {{ isset($data['width']) ? 'table-col-width-'.$data['width'] : ''}}">{{ isset($data['title']) ? $data['title'] : '' }}</div>
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach($events as $key => $event)
                                        <tr data-row-id="{{$key}}" data-confirm-item="{{$event->getKey()}}" class="item-confirm">
                                            @foreach ($fields as $field_name => $data)
                                                <td class="p-3 {{ !empty($data['disabled']) ? 'column-disabled' : '' }} {{!empty($data['sticky']) ? 'sticky-enabled active' : ''}}" data-field-name="{{ $field_name }}">
                                                    <span class="data-item editable-item">
                                                        <span>
                                                            @switch($field_name)
                                                                @case('title')
                                                                    {{ $event->getTitle() }}
																	<br>
                                                                    @if($event->__get('status') == 'draft')
                                                                    	<span class="badge bg-secondary">Draft</span>
                                                                    @endif
                                                                    
                                                                    @break
                                                                @case('format')
                                                                    {{ $event->format()->first()->title }}
                                                                    @break
                                                                @case('categories')
                                                                    {!! join(', ', array_map(function($value){ return "<span>$value</span>"; }, $event->categories()->get()->pluck('name')->toArray())) !!}
                                                                    @break
                                                                @case('tags')
                                                                    {!! join(', ', array_map(function($value){ return "<span>$value</span>"; }, $event->tags()->get()->pluck('tag')->toArray())) !!}
                                                                    @break
                                                                @case('author')
                                                                    @if($event->author)
                                                                        <a href="{{ $event->author->getUrl() }}" target="_blank">{!! __($event->author->getName()) !!}</a>
                                                                    @else
                                                                        {{ __('Deleted') }}
                                                                    @endif
                                                                    @break
                                                                @case('date')
                                                                @php $timezone = settings()->get('hubmachine.general.site.timezone');
                                                                @endphp
                                                                    Created: {{ core()->time()->localize($event->__get('created_at'), true) }}
                                                                    {{-- <br> --}}
                                                                    Updated: {{ core()->time()->localize($event->__get('updated_at'), true) }}
                                                                    @break
                                                                @case('actions')
                                                                    <a href="{{$event->getUrl()}}" title="{{ __('View') }}" target="_blank"><icon-image data-icon="visibility"></icon-image></a>
                                                                    <a href="{{ route('events.event.edit', [$event->link]) }}" title="{{ __('Edit') }}" target="_blank"><icon-image data-icon="edit"></icon-image></a>
                                                                    <a href="javascript:void(0);" title="Delete" data-url="{{ route('events.admin.event.delete.confirmation', [$event->getKey()]) }}" data-toggle="modal" data-target="#modal-delete-confirmation"><icon-image data-icon="delete"></icon-image></a>
                                                                    @break
                                                                @default
                                                                    {{ $event->__get($field_name) }}
                                                                    @break
                                                            @endswitch
                                                        </span>
                                                    </span>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="pagination-link d-none">
                                {{ $events->links() }}
                            </div>

                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    $('#events_table').hmtable();
                                });
                            </script>
                        </div>
                    </div>
                @else
                    @switch($filter_by)
                        @case('category')
                            <span>{{trans_choice('events::lang.NO_EVENTS_IN_CATEGORY_ALERT', (int)($filter_value === 'all'))}}</span>
                            @break
                        @case('tag')
                            <span>{{trans_choice('events::lang.NO_EVENTS_IN_TAG_ALERT', (int)($filter_value === 'all'))}}</span>
                            @break
                        @default
                            <span>{{__('events::lang.NO_PUBLISHED_EVENTS_ALERT')}}</span>
                    @endswitch
                @endif
            </div>
	    </div>
    </div>
@endsection