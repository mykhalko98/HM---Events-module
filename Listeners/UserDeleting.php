<?php

namespace Modules\Events\Listeners;

use Modules\Events\Entities\EventEvents;

class UserDeleting
{
    public function handle($event)
    {
        $user = $event->user;
        if (!$user) {
            $event->errors[] = 'User not found.';
            return FALSE;
        }

        if ($event->deleting_type == 'force') {
            EventEvents::where('author_type', get_class($user))->where('author_id', '=', $user->getKey())->get()->each(function($item) {
                $item->forceDelete();
            });
        } else {
            EventEvents::where('author_type', get_class($user))->where('author_id', '=', $user->getKey())->get()->each(function($item) {
                $item->delete();
            });
        }
    }
}
