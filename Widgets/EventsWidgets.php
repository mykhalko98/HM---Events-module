<?php


namespace Modules\Events\Widgets;

use Hubmachine\Widgets\Widget;

abstract class EventsWidgets extends Widget
{
    const TICKET_STATUSES = [
        'succeeded'      => ['badge' => 'success',   'label' => 'PAID'],
        'pending'        => ['badge' => 'info',      'label' => 'PENDING'],
        'failed'         => ['badge' => 'danger',    'label' => 'FAILED'],
        'refund_request' => ['badge' => 'secondary', 'label' => 'REFUND REQUEST'],
        'refunding'      => ['badge' => 'secondary', 'label' => 'REFUNDING'],
        'refunded'       => ['badge' => 'warning',   'label' => 'REFUNDED'],
    ];
}