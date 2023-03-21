<div class="redactor-buttons-edit" data-type="page-buttons">
    @if($event_status == 'draft')
        <button class="btn btn-primary btn-edit-save-all mr-1" onclick="frontAdministration.save(this);" data-url="{{route('layout.admin.widget.save_content', ['public'])}}">{{ __('events::lang.PUBLISH_BUTTON') }}</button>
        <button class="btn btn-primary btn-edit-draft-all mr-1" onclick="frontAdministration.draft(this);" data-url="{{route('layout.admin.widget.save_content', ['draft'])}}">{{ __('events::lang.UPDATE_BUTTON') }}</button>
    @elseif($event_status == 'public')
        <button class="btn btn-primary btn-edit-save-all mr-1" onclick="frontAdministration.save(this);" data-url="{{route('layout.admin.widget.save_content', ['public'])}}">{{ __('events::lang.UPDATE_BUTTON') }}</button>
        <button class="btn btn-secondary btn-edit-draft-all mr-1" onclick="frontAdministration.draft(this);" data-url="{{route('layout.admin.widget.save_content', ['draft'])}}">{{ __('events::lang.MOVE_TO_DRAFT_BUTTON') }}</button>
    @endif
    <button class="btn btn-link text-light px-2 mr-1" onclick="frontAdministration.cancel(this)">{{ __('events::lang.CANCEL_BUTTON') }}</button>
    <button class="btn btn-link text-light px-2" data-url="{{route('events.event.delete.confirmation', [$event_id])}}" data-toggle="modal" data-target="#modal-delete-confirmation" onclick="frontAdministration.deleteWidget(this)">{{ __('events::lang.DELETE_BUTTON') }}</button>
    <div class="d-flex justify-content-between mt-1">
        @if($event_status == 'draft')
            <div class="rbe-date">
	            {{ __('events::lang.PUBLISH_LABEL') }}
                <input type="text" class="datetimepicker" value="{{ $publish_at }}" placeholder="{{ __('events::lang.PUBLISH_AT_PLACEHOLDER') }}">
            </div>
        @endif
        <div class="rbe-featured">
            <div class="custom-control custom-checkbox">
                @if(!$featured)
                    <input type="checkbox"  class="custom-control-input" id="rbe-featured-checkbox">
                @else
                    <input type="checkbox" class="custom-control-input" id="rbe-featured-checkbox" checked="">
                @endif
                <label class="custom-control-label" for="rbe-featured-checkbox">{{ __('events::lang.FEATURED_LABEL') }}</label>
            </div>
        </div>
    </div>
</div>