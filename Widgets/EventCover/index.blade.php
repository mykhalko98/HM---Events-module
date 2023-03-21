<div class="widget-cover-media">
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
    @endif
</div>