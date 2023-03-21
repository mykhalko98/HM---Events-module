<?php

namespace Modules\Events\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Hubmachine\Users\Models\User;
use Hubmachine\Core\Models\Pages\Pages;
use Modules\Events\Entities\EventTags;
use Modules\Events\Entities\EventEvents;
use View;

class EventTagsController extends Controller
{

	/**
	 * Displays a list of tags.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse
	 */
    public function index(Request $request)
    {
        $viewer = \Auth::user();
        $tags_select = EventTags::select(['event_tags.tag', 'event_tags.slug', \DB::raw('COUNT(event_events_tags.id) as events_number')])
            ->leftJoin('event_events_tags', 'event_events_tags.tag_id', '=', 'event_tags.id')
            ->leftJoin('event_events', 'event_events.id', '=', 'event_events_tags.event_id')
            ->whereNotNull('event_events.id')
            ->whereNull('event_events.deleted_at')
            ->where('event_events.status', '=', 'public')
            ->groupBy('event_tags.id');

        $tags_select->leftJoin('users', function ($join) {
            $join->on('users.id', '=', 'event_events.author_id')
                ->where('event_events.author_type', User::class);
        });
        $tags_select->where(function($query) {
            $query->where('users.banned', '=', 0)
                ->orWhereNull('users.id');
        });
        if (!$viewer || !$viewer->isAdmin()) {
            $tags_select->where(function($query) use($viewer) {
                $query->where('users.shadow_banned', '=', 0);
                $viewer && $query->orWhere('users.id', '=', $viewer->getKey());
                $query->orWhereNull('users.id');
            });
            $tags_select->where('users.shadow_banned', '=', 0);
        }
        $tags = $tags_select->get();
        $_tags = [];
        foreach ($tags as $tag) {
            $_tags[$tag->__get('tag')] = ['name' => $tag->getName($translated = FALSE), 'translated' => $tag->getName(), 'events_number' => $tag->__get('events_number')];
        }

        if ($request->ajax()) {
            return response()->json(['status' => true, 'data' => $_tags]);
        }

        View::share('tags', $_tags);

        $page_id = Pages::select()->where('route', 'events.tags')->pluck('id')->first();
        return Pages::view($page_id);
    }

    /**
     * Get event tags.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function eventTags(Request $request, $id)
    {
        $event = EventEvents::find($id);
        if (empty($event)) {
            return response()->json(['status' => false, 'errors' => ['Event not found.']]);
        }

        $tags = $event->tags;

        return response()->json(['status' => false, 'data' => $tags]);
    }

    /**
     * Store new tag.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (!access()->isAllowed('events', 'edit_tags')) {
            return response()->json(['status' => false, 'errors' => ['Permission denied.']]);
        }

        $tag = trim(strip_tags($request->get('tag')));
        if (empty($tag)) {
            return response()->json(['status' => false, 'errors' => ['Enter a tag.']]);
        }

        if (EventTags::where('tag', $tag)->exists()) {
            return response()->json(['status' => false, 'errors' => ['The tag already exists.']]);
        }

        $tag = new EventTags(['tag' => $tag, 'slug' => \Illuminate\Support\Str::slug($tag)]);
        $tag->save();

        return response()->json(['status' => true, 'tag' => [
            'id'           => $tag->getKey(),
            'tag'          => $tag->getName(),
            'slug'         => $tag->slug,
            'posts_number' => 0,
            'date'         => 'Created: ' . core()->time()->localize($tag->__get('created_at'), true) . '<br>' . 'Updated: ' . core()->time()->localize($tag->__get('updated_at'), true),
        ]]);
    }

    /**
     * Update tag.
     *
     * @param Request $request
     * @param $tag_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $tag_id)
    {
        if (!access()->isAllowed('events', 'edit_tags')) {
            return response()->json(['status' => false, 'errors' => ['Permission denied.']]);
        }

        $tag = EventTags::find($tag_id);
        if (empty($tag)) {
            return response()->json(['status' => false, 'errors' => ['Tag you want to edit not found.']]);
        }

        $new_tag = trim(strip_tags($request->get('tag')));
        if (empty($new_tag)) {
            return response()->json(['status' => false, 'errors' => ['Enter a tag.']]);
        }

        if (EventTags::where('id', '!=', $tag_id)->where('tag', $new_tag)->exists()) {
            return response()->json(['status' => false, 'errors' => ['The tag already exists.']]);
        }

        $tag->__set('tag', $new_tag);
        $tag->__set('slug', \Illuminate\Support\Str::slug($new_tag));
        $tag->save();

        return response()->json(['status' => true]);
    }

    /**
     * Destroy tag.
     *
     * @param Request $request
     * @param $tag_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $tag_id)
    {
        if (!access()->isAllowed('events', 'edit_tags')) {
            return response()->json(['status' => false, 'errors' => ['Permission denied.']]);
        }

        $tag = EventTags::find($tag_id);
        if (empty($tag)) {
            return response()->json(['status' => false, 'errors' => ['Tag you want to delete not found.']]);
        }

        $tag->delete();

        return response()->json(['status' => true]);
    }
}
