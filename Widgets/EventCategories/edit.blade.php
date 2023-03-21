<div class="widget-event-category" data-edit-type="category">
    <div class="inner d-inline-block">
        @foreach($categories as $key => $category)
            <span class="badge badge-primary"><a href="{{ route('events.category.events', ['filter_value' => $category->slug]) }}" class="text-reset">{{ $category->getName() }}</a></span>
        @endforeach
    </div>
    <form action="#" class="d-inline-block" data-creating="{{access()->isAllowed('events', 'edit_categories') ? 'true' : 'false'}}">
        <select class="selectpicker-category selectpicker" multiple="1" title="{{ __('Edit category') }}" data-live-search="true" data-url="{{ route('events.categories') }}" name="category">
            @if($all_categories->count())
                @foreach($all_categories as $key => $category)
                    <option value="{{ $category->name }}" data-id="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            @else
                <option value="" disabled>{{__('events::lang.NO_CATEGORIES')}}</option>
            @endif
        </select>
    </form>

    <div class="wcm-group d-none">
        <button type="submit" class="btn btn-secondary btn-edit-save"
                data-editable-element>{{ __('events::lang.PUBLISH_BUTTON') }}</button>
        <button type="submit" class="btn btn-secondary btn-edit-draft"
                data-editable-element>{{ __('events::lang.SAVE_TO_DRAFT_BUTTON') }}</button>
        <button type="button" class="btn btn-primary btn-edit-delete" data-url="needRoute" data-toggle="modal"
                data-target="#modal-delete-confirmation"
                onclick="frontAdministration.deleteWidget(this)">{{ __('events::lang.DELETE_BUTTON') }}</button>
    </div>
</div>
