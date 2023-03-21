<div class="widget-cover-media" data-edit-type="cover-media" data-min-height="250px">
    <div class="widget-cover-media-buttons">
        <button type="button" class="btn btn-sm bg-primary btn-toggle-cover" title="{{ __('events::lang.COLLAPSE') }}" data-title="{{ __('events::lang.EXPAND') }}" onclick="frontAdministration.coverMedia.toggle(event)">
            <icon-image data-icon="expand_more"></icon-image>
        </button>
    </div>
    <form action="https://jsonplaceholder.typicode.com/posts" onsubmit="return frontAdministration.coverMedia.save(this)" class="wcm-form">
        <div class="wcm-group wcm-group-browse">
            @if(!empty($event_cover_video))
                <input type="text" name="video" value="{{ $event_cover_video }}" placeholder="{{ __('Add URL from YouTube') }}" class="form-control mb-1 wcm-video" oninput="frontAdministration.coverMedia.video(this);">
            @else
                <input type="text" name="video" value="" placeholder="{{ __('Add URL from YouTube') }}" class="form-control mb-1 wcm-video" oninput="frontAdministration.coverMedia.video(this);">
            @endif
            <div class="widget-cover-media-divider text-center"> {{ __('-OR-') }} </div>
            <div class="custom-file">
                <input type="file" name="image" id="customFile" data-url="{{ route('layout.admin.widgets.image.save') }}" class="wcm-image" onchange="frontAdministration.coverMedia.crop(this);">
                <label class="custom-file-label" data-placeholder="{{ __('Choose image') }}" data-placeholder-button="{{ __('Browse') }}" for="customFile"></label>
            </div>
        </div>
        <div class="wcm-crop-buttons text-center d-none" id="wcm-crop-buttons">
            <button type="button" class="btn btn-primary need-check" onclick="frontAdministration.coverMedia.image($(this).closest('.widget-cover-media').find('.custom-file [type=file]') );">{{ __('Save') }}</button>
            <button type="button" class="btn btn-secondary" onclick="frontAdministration.coverMedia.cropDestroy($(this).closest('.widget-cover-media').find('.custom-file [type=file]'));">{{ __('Cancel') }}</button>
        </div>
        {{-- user can't change proportion (in PB only) --}}
        <div class="row form-group my-3 d-none">
            <div class="col-md-6">
                <input type="text" name="width" placeholder="{{ __('Cover width in pixel') }}" class="form-control" oninput="frontAdministration.coverMedia.resize(this)" value="{{$config->fields->width->value}}">
            </div>
            <div class="col-md-6">
                <input type="text" name="height" placeholder="{{ __('Cover height in pixel') }}" class="form-control" oninput="frontAdministration.coverMedia.resize(this)" value="{{$config->fields->height->value}}">
            </div>
            <div class="col-md-6">
                <label for="required_size">Required Size</label>
                <input type="checkbox" name="required_size" {{ $config->fields->required_size->value ? 'checked' : '' }}>
            </div>
        </div>

        <div class="wcm-group d-none">
            <button type="submit" class="btn btn-secondary btn-edit-save" data-editable-element>{{ __('events::lang.SAVE_BUTTON') }}</button>
            <button type="submit" class="btn btn-secondary btn-edit-draft" data-editable-element>{{ __('events::lang.DRAFT_BUTTON') }}</button>
            <button type="button" class="btn btn-primary btn-edit-delete" data-url="needRoute" data-toggle="modal" data-target="#modal-delete-confirmation" onclick="frontAdministration.deleteWidget(this)">{{ __('feed::lang.DELETE_BUTTON') }}</button>
        </div>
        <button type="button" class="btn btn-remove-cover" title="{{ __('events::lang.REMOVE_COVER') }}" onclick="frontAdministration.coverMedia.removeCover(event)">
            <icon-image data-icon="cancel"></icon-image>
        </button>
    </form>
    @if($content)
        @if(isset($is_iframe) && $is_iframe)
            <div class="widget-cover-media-inner my-3 mw-100">
                {!! $content !!}
            </div>
        @else
            <div class="widget-cover-media-inner my-3 mw-100">
                <img src="{!! $content !!}" class="wcm-image" name="cover_media" data-image-id="{{ $image_id }}">
            </div>
        @endif
    @else
        <div class="widget-cover-media-inner my-3 mw-100"></div>
    @endif
</div>