<?php

namespace Modules\Events\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Modules\Events\Entities\EventCategories;
use Modules\Events\Entities\EventEvents;
use Modules\Events\Entities\EventEventsCategories;
use Modules\Events\Entities\EventEventsTags;
use Modules\Events\Entities\EventTags;


/**
 * @OA/Tag(
 *     name="Events",
 *     description="Events"
 * )
 */
class EventsController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/events",
     *     operationId="eventsEvents",
     *     tags={"Events"},
     *     summary="Get list of events",
     *     description="Get list of events",
     *     security={{ "sanctum": {} }},
     *     @OA\Parameter(
     *         description="Filter by: category|tag",
     *         in="query",
     *         name="filter_by",
     *         @OA\Schema(
     *              type="string",
     *              enum={"tag", "category"},
     *              default="all",
     *           )
     *     ),
     *     @OA\Parameter(
     *         description="Filter value",
     *         in="query",
     *         name="filter_value",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="events",
     *                     type="array",
     *                     @OA\Items(
     *                         allOf={
     *                             @OA\Schema(ref="#/components/schemas/EventEvents"),
     *                             @OA\Schema(@OA\Property(property="cover", type="string"))
     *                         }
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     *
     * Get all events.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function events(Request $request)
    {
        $viewer = auth()->user();
        if ($viewer && !access()->isAllowed('events', 'view_events')) {
            return response()->json(['status' => false, 'errors' => ['you are not alloed to view events']]);
        }

        $filter_by = 'events';
        if ($request->get('filter_by')) {
            $filter_by = $request->get('filter_by');
        }
        $filter_value = 'all';
        if ($request->get('filter_value')) {
            $filter_value = $request->get('filter_value');
        }

        $params = [];
        switch ($filter_by) {
            case 'category':
                if ($filter_value === 'all') {
                    $event_ids = EventEventsCategories::select()->pluck('event_id')->toArray();
                } else {
                    $category = EventCategories::where('slug', 'LIKE', $filter_value)->first();
                    if (!$category) {
                        $filter_value_title = "missing category";
                    } else {
                        $event_ids = EventEventsCategories::where('category_id', '=', $category->getKey())->pluck('event_id')->toArray();
                        $filter_value_title = $category->name;
                    }
                }
                isset($event_ids) && $params['ids'] = $event_ids;
                break;
            case 'tag':
                if ($filter_value === 'all') {
                    $event_ids = EventEventsTags::select()->pluck('event_id')->toArray();
                } else {
                    $tag_id = EventTags::where('slug', 'LIKE', $filter_value)->pluck('id')->first();
                    if (!$tag_id) {
                        $filter_value = "missing tag";
                    } else {
                        $event_ids = EventEventsTags::where('tag_id', '=', $tag_id)->pluck('event_id')->toArray();
                    }
                }

                isset($event_ids) && $params['ids'] = $event_ids;
                break;
            default:
                break;
        }

        if ($viewer && $viewer->isAdmin()) {
            $params['include_shadow_banned_users'] = true;
        } elseif ($viewer) {
            $params['include_shadow_banned_users'] = [$viewer->getKey()];
        }

        $params['status'] = 'public';
        $events = (new EventEvents)->getEventEventsSelect($params)->orderBy('created_at', 'DESC')->paginate(20)->keyBy('id');

        $media_placeholder = media()->get(settings()->get('hubmachine.media.placeholder'))->getThumbnail(860, 484);

        foreach ($events as $event) {
            $event->cover = $event->getCover(860, 484);
        }

        $data = [
            'events' => $events->values(),
            'media_placeholder' => $media_placeholder,
            'filter_by' => $filter_by,
            'filter_value' => $filter_value
        ];

        return response()->json(['status' => true, 'data' => $data]);
    }

}