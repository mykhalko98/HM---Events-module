<h1 class="event-title page-title" data-edit-type="redactor" placeholder="{{ __('Title') }}" data-url="{{ route('layout.admin.widget.save_content') }}" data-image-delete="{{ route('admin.media.destroy') }}" data-image-url="{{ route('layout.admin.widgets.image.save') }}">
    {!! empty($content) ? '' : $content !!}
</h1>