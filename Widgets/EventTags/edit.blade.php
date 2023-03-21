<div class="widget-event-tags" data-edit-type="category">
	<div class="inner d-inline-block">
		@if($tags->count())
			@foreach($tags as $key => $tag)
				<span class="badge badge-primary"><a href="{{ route('events.tag.events', ['filter_value' => $tag->slug]) }}" class="text-reset">{{ $tag->getName() }}</a></span>
			@endforeach
		@endif
	</div>
	<form action="#" class="d-inline-block" data-creating="{{access()->isAllowed('events', 'edit_tags') ? 'true' : 'false'}}">
		<select class="selectpicker-tag selectpicker" multiple="1" title="{{__('Edit tags')}}" data-live-search="true" data-url="{{ route('events.tags') }}" name="tag">
			@if($all_tags->count())
				@foreach($all_tags as $key => $tag)
					<option value="{{ $tag->tag }}">{{ $tag->tag }}</option>
				@endforeach
			@else
				<option value="" disabled>{{__('events::lang.NO_TAGS')}}</option>
			@endif
		</select>
	</form>
	
	<div class="wcm-group d-none">
		<button type="submit" class="btn btn-secondary btn-edit-save" data-editable-element>{{ __('events::lang.PUBLISH_BUTTON') }}</button>
		<button type="submit" class="btn btn-secondary btn-edit-draft" data-editable-element>{{ __('events::lang.SAVE_TO_DRAFT_BUTTON') }}</button>
		<button type="button" class="btn btn-primary btn-edit-delete" data-url="needRoute" data-toggle="modal" data-target="#modal-delete-confirmation" onclick="frontAdministration.deleteWidget(this)">{{ __('events::lang.DELETE_BUTTON') }}</button>
	</div>
</div>
