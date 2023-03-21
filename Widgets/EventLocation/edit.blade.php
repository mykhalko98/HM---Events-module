<div class="event-location-row" data-edit-type="simple_fields">
    <div class="row w-100 mx-0">
        <div class="col px-0">
            <div class="input-group mb-3">
                <label for="location" class="w-100 d-block">{{ __('events::lang.Location') }}</label>
                <input type="text" value="{{ isset($event) ? $event->location: '' }}" name="location" class="form-control value_field input-places-autocomplete" />
            </div>
        </div>
    </div>
    <div class="wcm-group d-none">
        <button type="submit" class="btn btn-secondary btn-edit-save" data-editable-element>{{ __('events::lang.PUBLISH_BUTTON') }}</button>
        <button type="submit" class="btn btn-secondary btn-edit-draft" data-editable-element>{{ __('events::lang.SAVE_TO_DRAFT_BUTTON') }}</button>
        <button type="button" class="btn btn-primary btn-edit-delete" data-url="needRoute" data-toggle="modal" data-target="#modal-delete-confirmation" onclick="frontAdministration.deleteWidget(this)">{{ __('events::lang.DELETE_BUTTON') }}</button>
    </div>
</div>