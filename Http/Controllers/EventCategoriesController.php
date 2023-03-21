<?php

namespace Modules\Events\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Hubmachine\Users\Models\User;
use Hubmachine\Core\Models\Pages\Pages;
use Modules\Events\Entities\EventEvents;
use Modules\Events\Entities\EventCategories;
use View;

class EventCategoriesController extends Controller
{
    /**
     * Displays a list of categories.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\Factory
     */
    public function index(Request $request)
    {
        $viewer = \Auth::user();
        $categories_select = EventCategories::select(['event_categories.name', 'event_categories.slug', \DB::raw('COUNT(event_events_categories.id) as events_number')])
            ->leftJoin('event_events_categories', 'category_id', '=', 'event_categories.id')
            ->leftJoin('event_events', 'event_events.id', '=', 'event_events_categories.event_id')
            ->whereNotNull('event_events.id')
            ->whereNull('event_events.deleted_at')
            ->where('event_events.status', '=', 'public')
            ->groupBy('event_categories.id');

        $categories_select->leftJoin('users', function ($join) {
            $join->on('users.id', '=', 'event_events.author_id')
                ->where('event_events.author_type', User::class);
        });
        $categories_select->where(function($query) {
            $query->where('users.banned', '=', 0)
                ->orWhereNull('users.id');
        });
        if (!$viewer || !$viewer->isAdmin()) {
            $categories_select->where(function($query) use($viewer) {
                $query->where('users.shadow_banned', '=', 0);
                $viewer && $query->orWhere('users.id', '=', $viewer->getKey());
                $query->orWhereNull('users.id');
            });
            $categories_select->where('users.shadow_banned', '=', 0);
        }
        $categories = $categories_select->get();
        View::share('categories', $categories);

        $page_id = Pages::select()->where('route', 'events.categories')->pluck('id')->first();
        return Pages::view($page_id);
    }

    /**
     * Get event categories.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function eventCategories(Request $request, $id)
    {
        $event = EventEvents::find($id);
        if (empty($event)) {
            return response()->json(['status' => false, 'errors' => ['Event not found.']]);
        }

        $categories = $event->categories;

        return response()->json(['status' => false, 'data' => $categories]);
    }

    /**
     * Store new category.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (!access()->isAllowed('events', 'edit_categories')) {
            return response()->json(['status' => false, 'errors' => ['Permission denied.']]);
        }

        $category_name = trim(strip_tags($request->get('name')));
        if (empty($category_name)) {
            return response()->json(['status' => false, 'errors' => ['Enter a category name.']]);
        }

        if (EventCategories::where('name', $category_name)->exists()) {
            return response()->json(['status' => false, 'errors' => ['The category already exists.']]);
        }

        $category = (new EventCategories(['name' => $category_name, 'slug' => \Illuminate\Support\Str::slug($category_name)]));
        $category->save();
        return response()->json(['status' => true, 'category' => [
            'id'           => $category->getKey(),
            'name'         => $category->getName(),
            'slug'         => $category->slug,
            'posts_number' => 0,
            'date'         => 'Created: ' . core()->time()->localize($category->__get('created_at'), true) . '<br>' . 'Updated: ' . core()->time()->localize($category->__get('updated_at'), true),
        ]]);
    }

    /**
     * Update category.
     *
     * @param Request $request
     * @param $category_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $category_id)
    {
        if (!access()->isAllowed('events', 'edit_categories')) {
            return response()->json(['status' => false, 'errors' => ['Permission denied.']]);
        }

        $category = EventCategories::find($category_id);
        if (empty($category)) {
            return response()->json(['status' => false, 'errors' => ['Category you want to edit not found.']]);
        }

        $new_category = trim(strip_tags($request->get('name')));
        if (empty($new_category)) {
            return response()->json(['status' => false, 'errors' => ['Enter a category.']]);
        }

        if (EventCategories::where('id', '!=', $category_id)->where('name', $new_category)->exists()) {
            return response()->json(['status' => false, 'errors' => ['The category already exists.']]);
        }

        $category->__set('name', $new_category);
        $category->__set('slug', \Illuminate\Support\Str::slug($new_category));
        $category->save();

        return response()->json(['status' => true]);
    }

    /**
     * Destroy category.
     *
     * @param Request $request
     * @param $category_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $category_id)
    {
        if (!access()->isAllowed('events', 'edit_categories')) {
            return response()->json(['status' => false, 'errors' => ['Permission denied.']]);
        }

        $category = EventCategories::find($category_id);
        if (empty($category)) {
            return response()->json(['status' => false, 'errors' => ['Category you want to delete not found.']]);
        }

        $category->delete();

        return response()->json(['status' => true]);
    }
}
