<div class="redactor-buttons-edit" data-type="page-buttons">
    <button class="btn btn-primary btn-edit-save-all mr-1" onclick="frontAdministration.save(this);" data-url="{{route('layout.admin.widget.save_content', ['public'])}}">{{ __('events::lang.PUBLISH_BUTTON') }}</button>
    <button class="btn btn-secondary btn-edit-draft-all mr-1" onclick="frontAdministration.draft(this);" data-url="{{route('layout.admin.widget.save_content', ['draft'])}}">{{ __('events::lang.SAVE_TO_DRAFT_BUTTON') }}</button>

    <button class="btn btn-link text-light px-2 mr-1" onclick="frontAdministration.cancel(this)">{{ __('events::lang.CANCEL_BUTTON') }}</button>
    <div class="d-flex justify-content-between mt-1">
        <div class="rbe-date">
	        {{ __('events::lang.PUBLISH_LABEL') }}
            <input type="text" class="datetimepicker" value="" placeholder="{{ __('events::lang.PUBLISH_AT_PLACEHOLDER') }}">
        </div>
        <div class="rbe-featured">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="rbe-featured-checkbox">
                <label class="custom-control-label" for="rbe-featured-checkbox">{{ __('events::lang.FEATURED_LABEL') }}</label>
            </div>
        </div>
    </div>
</div>