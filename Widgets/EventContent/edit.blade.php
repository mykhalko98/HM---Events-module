<h2 class="block-title">{{ __('events::lang.Details') }}</h2>
<div data-edit-type="redactor" data-url="{{ route('layout.admin.widget.save_content') }}" placeholder="{{ __('Start typing here...') }}" data-image-delete="{{ route('admin.media.destroy') }}" data-image-url="{{ route('layout.admin.widgets.image.save') }}">
    {!! empty($content) ? '' : $content !!}
</div>