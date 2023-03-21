<?php

namespace Modules\Events\Observers;

use App\Models\Comment;
use App\Models\Like;
use Hubmachine\Friendships\Models\Follow;
use Hubmachine\Media\Models\FilesLog;
use Hubmachine\Media\Models\MediaFile;
use Hubmachine\Notifications\Models\Notifications;
use Modules\Events\Entities\EventEvents;
use Modules\Events\Entities\EventEventsCategories;
use Modules\Events\Entities\EventEventsTags;
use Modules\Events\Entities\EventTicketOrders;
use Modules\Events\Entities\EventTickets;
use Modules\Events\Entities\EventWidgetsContent;

class EventsObserver
{
    /**
     * Handle the Events "deleting" event.
     *
     * @param EventEvents $event
     * @return void
     */
    public function deleting(EventEvents $event)
    {
        $viewer = \Auth::user();
        if ($event->isForceDeleting()) {
            Comment::where('commentable_type', get_class($event))->where('commentable_id', '=', $event->getKey())->forceDelete();
            Like::where('likeable_type', get_class($event))->where('likeable_id', '=', $event->getKey())->forceDelete();
            Follow::where('recipient_type', get_class($event))->where('recipient_id', '=', $event->getKey())->delete();
            EventWidgetsContent::where('event_id', '=', $event->getKey())->delete();
            EventEventsCategories::where('event_id', '=', $event->getKey())->delete();
            EventEventsTags::where('event_id', '=', $event->getKey())->delete();
            MediaFile::where('resource_type', get_class($event))->where('resource_id', '=', $event->getKey())->get()->each(function($file) use($viewer) {
                $file->deleteFile();
                $note = $file->original_name;
                $log = new FilesLog(['file_id' => $file->getKey(), 'author_type' => get_class($viewer), 'author_id' => $viewer->getKey(), 'action' => 'force_delete', 'note' => $note]);
                $log->save();
                $file->forceDelete();
            });
            EventTicketOrders::where('event_id', '=', $event->getKey())->delete();
            EventTickets::where('event_id', '=', $event->getKey())->delete();
//            Notifications::where('subject_type', get_class($event))->where('subject_id', '=', $event->getKey())->delete();
        } else {
            Comment::where('commentable_type', get_class($event))->where('commentable_id', '=', $event->getKey())->delete();
            Like::where('likeable_type', get_class($event))->where('likeable_id', '=', $event->getKey())->delete();
            MediaFile::where('resource_type', get_class($event))->where('resource_id', '=', $event->getKey())->get()->each(function($file) use($viewer) {
                $note = $file->original_name;
                $log = new FilesLog(['file_id' => $file->getKey(), 'author_type' => get_class($viewer), 'author_id' => $viewer->getKey(), 'action' => 'soft_delete', 'note' => $note]);
                $log->save();
                $file->delete();
            });
        }
    }

    /**
     * Handle the Events "restored" event.
     *
     * @param EventEvents $event
     * @return void
     */
    public function restored(EventEvents $event)
    {
        $viewer = \Auth::user();
        Comment::onlyTrashed()->where('commentable_type', get_class($event))->where('commentable_id', '=', $event->getKey())->restore();
        Like::onlyTrashed()->where('likeable_type', get_class($event))->where('likeable_id', '=', $event->getKey())->restore();
        MediaFile::onlyTrashed()->where('resource_type', get_class($event))->where('resource_id', '=', $event->getKey())->get()->each(function($file) use($viewer) {
            $file->restore();
            $note = $file->original_name;
            $log = new FilesLog(['file_id' => $file->getKey(), 'author_type' => get_class($viewer), 'author_id' => $viewer->getKey(), 'action' => 'restore', 'note' => $note]);
            $log->save();
        });
    }
}
