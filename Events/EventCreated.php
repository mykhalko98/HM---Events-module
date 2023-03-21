<?php

namespace Modules\Events\Events;

use Illuminate\Queue\SerializesModels;

class EventCreated
{
    use SerializesModels;

    public $request = NULL;
    public $event   = NULL;
    public $errors  = [];
    public $prevent = FALSE;

    public function __construct($request, $event)
    {
        $this->request = $request;
        $this->event   = $event;
    }

    public function broadcastOn()
    {
        return [];
    }
}