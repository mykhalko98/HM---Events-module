<div class="event-privacy-row" data-edit-type="simple_fields">
    <div class="col">
        <div class="d-flex justify-content-center">
            <h5>Privacy</h5>
        </div>
        <div class="d-flex justify-content-center">
            <div class="col-2">
                <div class="form-group">
                    <div class='input-group' id="privacy">
                        <select name="privacy" class="form-control value_field">
                            <option value="public" {{ isset($event) && $event->privacy == 'public' ? 'selected' : '' }}>{{ __('Public') }}</option>
                            <option value="private" {{ isset($event) && $event->privacy == 'private' ? 'selected' : '' }}>{{ __('Private') }}</option>
                        </select>
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
</div>