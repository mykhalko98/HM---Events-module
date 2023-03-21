<div class="event-dates-row">
    <div class="left">
        <icon-image data-icon="schedule" title="{{ __('events::lang.Schedule') }}"></icon-image>
    </div>
    <div class="right">
        <div class="row no-gutters">
            <div class="date-start">
                {{ core()->time()->localize($event->start_time)->format('M d, Y g:i A') }}
            </div>
            <div class="divider"> - </div>
            <div class="date-end">
                {{ core()->time()->localize($event->end_time)->format('M d, Y g:i A') }}
            </div>
        </div>
    </div>
</div>