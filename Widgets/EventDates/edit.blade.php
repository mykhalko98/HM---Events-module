<div data-edit-type="simple_fields">
    <div class="event-dates-row">
        <div class="row mx-0 w-100">
            <div class="col px-0">
                <div class="input-group mb-3">
                    <label for="start_time" class="w-100 d-block">{{ __('events::lang.Date Start') }}</label>
                    <input type="text" name="start_time" value="{{ isset($event)? core()->time()->localize($event->start_time)->format('m/d/Y g:i A') : '' }}" class="form-control value_field datetimepicker" >
                </div>
            </div>
        </div>
    </div>
    <div class="event-dates-row">
        <div class="row mx-0 w-100">
            <div class="col px-0">
                <div class="input-group mb-3">
                    <label for="end_time" class="w-100 d-block">{{ __('events::lang.Date End') }}</label>
                    <input type="text" name="end_time" value="{{ isset($event)? core()->time()->localize($event->end_time)->format('m/d/Y g:i A') : '' }}" class="form-control value_field datetimepicker" >
                </div>
            </div>
        </div>
    </div>
    <div class="wcm-group d-none">
        <button type="submit" class="btn btn-secondary btn-edit-save" data-editable-element>{{ __('events::lang.PUBLISH_BUTTON') }}</button>
        <button type="submit" class="btn btn-secondary btn-edit-draft" data-editable-element>{{ __('events::lang.SAVE_TO_DRAFT_BUTTON') }}</button>
        <button type="button" class="btn btn-primary btn-edit-delete" data-url="needRoute" data-toggle="modal" data-target="#modal-delete-confirmation" onclick="frontAdministration.deleteWidget(this)">{{ __('events::lang.DELETE_BUTTON') }}</button>
    </div>
</div>