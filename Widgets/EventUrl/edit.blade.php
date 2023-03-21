@if(isset($event))
    <div id="event-url" class="event-url page-url">
        <div class="col-auto"><icon-image data-icon="insert_link"></icon-image></div>
        <div class="col"><h6 data-edit-type="redactor" placeholder="{{ $event->__get('link') }}" data-url="{{ route('layout.admin.widget.save_content') }}">{{ $event->__get('link') }}</h6></div>
    </div>
@endif