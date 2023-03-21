<?php

namespace Modules\Events\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

use Modules\Events\Entities\EventCategories;
use Modules\Events\Entities\EventEvents;
use Modules\Events\Entities\EventEventsCategories;
use Modules\Events\Entities\EventEventsTags;
use Modules\Events\Entities\EventTags;

use Auth;

class EventEventsController extends Controller
{
	/**
	 * Displays a list of events.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param string                   $filter_by
	 * @param string                   $filter_value
	 *
	 * @return \Illuminate\Http\RedirectResponse|Response|\Illuminate\View\View
	 */
    public function index(Request $request, $filter_by = 'category', $filter_value = 'all')
    {
        $viewer = Auth::user();
        if ($viewer && !access()->isAllowed('events', 'view_events')) {
            return redirect()->route('admin.dashboard');
        }

        $events_table  = (new EventEvents())->getTable();
        $events_select = EventEvents::select();
	    $filter_menu_items = [];

	    switch($filter_by) {
		    case 'category':
                $categories_table = (new EventCategories)->getTable();
                $events_categories_table = (new EventEventsCategories)->getTable();
                $all_categories = EventCategories::select("$categories_table.id", "$categories_table.name", DB::raw("COUNT($events_categories_table.event_id) as events_number"))
                    ->leftJoin($events_categories_table, "$events_categories_table.category_id", "$categories_table.id")
                    ->leftJoin($events_table, "$events_table.id", "$events_categories_table.event_id")
                    ->whereNull("$events_table.deleted_at")
                    ->groupBy("$categories_table.id")
                    ->orderBy("$categories_table.created_at", "DESC")
                    ->get();

                $posts_number = EventEvents::select()
                    ->leftJoin($events_categories_table, "$events_categories_table.event_id", "$events_table.id")
                    ->whereNotNull("$events_categories_table.event_id")
                    ->distinct("$events_table.id")
                    ->count();
                $filter_menu_items  = [['item_text' => 'All', 'item_value' => 'all', 'events_number' => $posts_number]];
                foreach($all_categories as $category) {
                    $filter_menu_items[] = ['item_text' => $category->name, 'item_value' => $category->getKey(), 'events_number' => $category->events_number];
                }

                if ($filter_value === 'all') {
                    $event_ids = EventEventsCategories::select()->pluck('event_id')->toArray();
                } else {
                    $category = EventCategories::where('id', '=', (int)$filter_value)->first();
                    if (!$category->getKey()) {
                        $filter_value = "missing category";
                    } else {
                        $event_ids = EventEventsCategories::where('category_id', '=', $category->getKey())->pluck('event_id')->toArray();
                    }
                }
                isset($event_ids) && $events_select->whereIn("{$events_table}.id", array_unique($event_ids));
			    break;
            case 'tag':
                $tags_table = (new EventTags())->getTable();
                $events_tags_table = (new EventEventsTags())->getTable();
                $all_tags = EventTags::select("$tags_table.id", "$tags_table.tag", DB::raw("COUNT($events_tags_table.event_id) as events_number"))
                    ->leftJoin($events_tags_table, "$events_tags_table.tag_id", "$tags_table.id")
                    ->leftJoin($events_table, "$events_table.id", "$events_tags_table.event_id")
                    ->whereNull("$events_table.deleted_at")
                    ->groupBy("$tags_table.id")
                    ->orderBy("$tags_table.created_at", "DESC")
                    ->get();

                $posts_number = EventEvents::select()
                    ->leftJoin($events_tags_table, "$events_tags_table.event_id", "$events_table.id")
                    ->whereNotNull("$events_tags_table.event_id")
                    ->distinct("$events_table.id")
                    ->count();
                $filter_menu_items  = [['item_text' => 'All', 'item_value' => 'all', 'events_number' => $posts_number]];
                foreach($all_tags as $tag) {
                    $filter_menu_items[] = ['item_text' => $tag->getName(), 'item_value' => $tag->getName(false), 'events_number' => $tag->events_number];
                }

                if ($filter_value === 'all') {
                    $event_ids = EventEventsTags::select()->pluck('event_id')->toArray();
                } else {
                    $tag_id = EventTags::where('tag', '=', $filter_value)->pluck('id')->first();
                    if (!$tag_id) {
                        $filter_value = "missing tag";
                    } else {
                        $event_ids = EventEventsTags::where('tag_id', '=', $tag_id)->pluck('event_id')->toArray();
                    }
                }
                isset($event_ids) && $events_select->whereIn("{$events_table}.id", array_unique($event_ids));
                break;
		    default : {
			    break;
		    }
		}

        $events = $events_select->orderBy("$events_table.created_at", 'DESC')->paginate(50);

        $table_fields = collect([
            'id'         => ['title' => 'ID',             'width' => 50,  'sticky' => false, 'disabled' => false],
            'title'      => ['title' => 'Title',          'width' => 200, 'sticky' => false, 'disabled' => false],
            'format'     => ['title' => 'Event Format',   'width' => 100, 'sticky' => false, 'disabled' => false],
            'categories' => ['title' => 'Categories',     'width' => 100, 'sticky' => false, 'disabled' => false],
            'tags'       => ['title' => 'Tags',           'width' => 100, 'sticky' => false, 'disabled' => false],
            'author'     => ['title' => 'Author',         'width' => 100, 'sticky' => false, 'disabled' => false],
            'date'       => ['title' => 'Date',           'width' => 200, 'sticky' => false, 'disabled' => false],
            'actions'    => ['title' => 'Actions',        'width' => 100,  'sticky' => false, 'disabled' => false]
        ]);

        return view('events::admin.events.index', ['fields' => $table_fields, 'events' => $events, 'filter_by' => $filter_by, 'filter_value' => $filter_value, 'filter_menu_items' => $filter_menu_items]);
    }

	/**
	 * Displays the settings popup.
	 *
	 * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
	 */
	public function settings()
	{
        $categories = EventCategories::select(['event_categories.*', DB::raw('COUNT(event_events_categories.id) as events_number')])
            ->leftJoin('event_events_categories', 'event_events_categories.category_id', '=', 'event_categories.id')
            ->leftJoin('event_events', 'event_events.id', '=', 'event_events_categories.event_id')
            ->whereNull('event_events.deleted_at')
            ->groupBy('event_categories.id')
            ->orderBy('events_number', 'DESC')
            ->get();

		$category_fields = collect([
		    'id'            => ['title' => 'ID',            'width' => 50,  'sticky' => false, 'disabled' => false],
		    'name'          => ['title' => 'Category Name', 'width' => 200, 'sticky' => false, 'disabled' => false],
		    'events_number' => ['title' => 'Events Number', 'width' => 100, 'sticky' => false, 'disabled' => false],
            'date'          => ['title' => 'Date',          'width' => 200, 'sticky' => false, 'disabled' => false],
		    'actions'       => ['title' => 'Actions',        'width' => 50, 'sticky' => false, 'disabled' => false]
		]);

        $tags = EventTags::select(['event_tags.id', 'tag', DB::raw('COUNT(event_events_tags.id) as events_number')])
            ->leftJoin('event_events_tags', 'tag_id', '=', 'event_tags.id')
            ->leftJoin('event_events', 'event_events.id', '=', 'event_events_tags.event_id')
            ->whereNull('event_events.deleted_at')
            ->groupBy('event_tags.id')
            ->orderBy('events_number', 'DESC')
            ->get();
        $tag_fields = collect([
            'tag'           => ['title' => 'Tag Name',      'width' => 200, 'sticky' => false, 'disabled' => false],
            'events_number' => ['title' => 'Events Number', 'width' => 100, 'sticky' => false, 'disabled' => false],
            'actions'       => ['title' => 'Actions',       'width' => 50,  'sticky' => false, 'disabled' => false]
        ]);

		$html = view('events::admin.events.settings', ['category_fields' => $category_fields, 'categories' => $categories, 'tag_fields' => $tag_fields, 'tags' => $tags])->render();
		return response()->json(['status' => true, 'data' => $html]);
	}

	/**
     * Create a new event category.
     *
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\JsonResponse|Response
	 */
	public function createCategory(Request $request)
	{
	    if (!access()->isAllowed('events', 'edit_categories')) {
            return response()->json(['status' => FALSE, 'errors' => ['Permission denied.']]);
        }

        $category_name = trim(strip_tags($request->get('name')));
        if (empty($category_name)) {
            return response()->json(['status' => FALSE, 'errors' => ['Enter a category name.']]);
        }

        if (EventCategories::where('name', $category_name)->exists()) {
            return response()->json(['status' => FALSE, 'errors' => ['The category already exists.']]);
        }

		$category = (new EventCategories(['name' => $category_name, 'slug' => \Illuminate\Support\Str::slug($category_name)]));
		$category->save();
		return response()->json(['status' => TRUE, 'category' => [
			'id'            => $category->getKey(),
			'name'          => $category->getName(),
            'slug'          => $category->slug,
			'events_number' => 0,
			'date'          => 'Created: ' . core()->time()->localize($category->__get('created_at'), true) . '<br>' . 'Updated: ' . core()->time()->localize($category->__get('updated_at'), true),
			'actions'       => '<a href="#"><icon-image data-icon="edit"></icon-image></a><a href="#"><icon-image data-icon="delete"></icon-image></a>'
		]]);
	}

    /**
     * Create tag.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createTag(Request $request)
    {
        if (!access()->isAllowed('events', 'edit_tags')) {
            return response()->json(['status' => FALSE, 'errors' => ['Permission denied.']]);
        }

        $tag = trim(strip_tags($request->get('tag')));
        if(empty($tag)) {
            return response()->json(['status' => FALSE, 'errors' => ['Enter a tag.']]);
        }

        if(EventTags::where('tag', $tag)->exists()) {
            return response()->json(['status' => FALSE, 'errors' => ['The tag already exists.']]);
        }

        $tag = (new EventTags(['tag' => $tag, 'slug' => \Illuminate\Support\Str::slug($tag)]));
        $tag->save();

        return response()->json(['status' => TRUE, 'tag' => [
            'id'            => $tag->getKey(),
            'name'          => $tag->getName(),
            'slug'          => $tag->slug,
            'events_number' => 0,
            'date'          => 'Created: ' . core()->time()->localize($tag->__get('created_at'), true) . '<br>' . 'Updated: ' . core()->time()->localize($tag->__get('updated_at'), true),
            'actions'       => '<a href="#"><icon-image data-icon="edit"></icon-image></a><a href="#"><icon-image data-icon="delete"></icon-image></a>'
        ]]);
    }

	/**
     * Edit category.
     *
	 * @param \Illuminate\Http\Request $request
	 * @param                          $category_id
	 * @return \Illuminate\Http\JsonResponse
     */
    public function editCategory(Request $request, $category_id)
    {
        if (!access()->isAllowed('events', 'edit_categories')) {
            return response()->json(['status' => FALSE, 'errors' => ['Permission denied.']]);
        }

        $category = EventCategories::find($category_id);
        if (empty($category)) {
            return response()->json(['status' => FALSE, 'errors' => ['Category you want to edit not found.']]);
        }

        $new_category = trim(strip_tags($request->get('name')));
        if (empty($new_category)) {
            return response()->json(['status' => FALSE, 'errors' => ['Enter a category.']]);
        }

        if (EventCategories::where('id', '!=', $category_id)->where('name', $new_category)->exists()) {
            return response()->json(['status' => FALSE, 'errors' => ['The category already exists.']]);
        }

        $category->__set('name', $new_category);
        $category->__set('slug', \Illuminate\Support\Str::slug($new_category));
        $category->save();

        return response()->json(['status' => TRUE]);
    }

    /**
     * Edit tag.
     *
     * @param \Illuminate\Http\Request $request
     * @param                          $tag_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function editTag(Request $request, $tag_id)
    {
        if (!access()->isAllowed('events', 'edit_tags')) {
            return response()->json(['status' => FALSE, 'errors' => ['Permission denied.']]);
        }

        $tag = EventTags::find($tag_id);
        if(empty($tag)) {
            return response()->json(['status' => FALSE, 'errors' => ['Tag you want to edit not found.']]);
        }

        $new_tag = trim(strip_tags($request->get('tag')));
        if(empty($new_tag)) {
            return response()->json(['status' => FALSE, 'errors' => ['Enter a tag.']]);
        }

        if(EventTags::where('id', '!=', $tag_id)->where('tag', $new_tag)->exists()) {
            return response()->json(['status' => FALSE, 'errors' => ['The tag already exists.']]);
        }

        $tag->__set('tag', $new_tag);
        $tag->__set('slug', \Illuminate\Support\Str::slug($new_tag));
        $tag->save();

        return response()->json(['status' => TRUE]);
    }

    /**
     * Delete category.
     *
     * @param \Illuminate\Http\Request $request
     * @param                          $category_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteCategory(Request $request, $category_id)
    {
        if (!access()->isAllowed('events', 'edit_categories')) {
            return response()->json(['status' => FALSE, 'errors' => ['Permission denied.']]);
        }

        $category = EventCategories::find($category_id);
        if (empty($category)) {
            return response()->json(['status' => FALSE, 'errors' => ['Category you want to delete not found.']]);
        }

        $category->delete();

        return response()->json(['status' => TRUE]);
    }

    /**
     * Delete tag.
     *
     * @param \Illuminate\Http\Request $request
     * @param                          $tag_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteTag(Request $request, $tag_id)
    {
        if (!access()->isAllowed('events', 'edit_tags')) {
            return response()->json(['status' => FALSE, 'errors' => ['Permission denied.']]);
        }

        $tag = EventTags::find($tag_id);
        if(empty($tag)) {
            return response()->json(['status' => FALSE, 'errors' => ['Tag you want to delete not found.']]);
        }

        $tag->delete();

        return response()->json(['status' => TRUE]);
    }

    /**
     *  Event delete confirmation.
     *
     * @param Request $request
     * @param null $id
     * @return \Illuminate\Http\JsonResponse;
     * @throws \Throwable
     */
    public function delete_confirmation(Request $request, $id = null)
    {
        $event = EventEvents::find($id);
        if (!$event) {
            return response()->json(['status' => false, 'errors' => ['Event not found']]);
        }

        $viewer = Auth::user();
        if (!$viewer->isAdmin()) {
            return response()->json(['status' => false, 'errors' => ['Permissions denied']]);
        }

        $event_title = $event->getTitle();
        if (strlen($event_title) > 64) {
            $event_title = substr($event_title, 0, 64) . ' ...';
        }

        $content = view('events::admin.events.delete_event', ['event_title' => $event_title, 'event_id' => $event->getKey()])->render();
        return response()->json(['status' => true, 'data' => $content]);
    }

    /**
     * Delete event.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete_event(Request $request, $id)
    {
        $event = EventEvents::find($id);
        if (!$event) {
            return response()->json(['status' => false, 'errors' => ['Event not found.']]);
        }

        $viewer = Auth::user();
        if (!$viewer->isAdmin()) {
            return response()->json(['status' => false, 'errors' => ['Permissions denied.']]);
        }

        $event->forceDelete();

        return response()->json(['status' => true, 'reload' => true]);
    }
}
