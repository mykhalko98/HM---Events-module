<?php

namespace Modules\Events\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Hubmachine\Core\FiltersManager;
use Hubmachine\Core\Models\Pages\Pages;
use Modules\Events\Entities\EventCategories;
use Modules\Events\Entities\EventEvents;
use Modules\Events\Entities\EventEventsCategories;
use Modules\Events\Entities\EventEventsTags;
use Modules\Events\Entities\EventTags;
use Modules\Events\Entities\EventWidgetsContent;
use Hubmachine\Users\Models\User;

use App\Helpers\Html;
use Auth;
use View;

class EventEventsController extends Controller
{
    /**
     * Displays a list of events.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $filter_by
     * @param string $filter_value
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request, $filter_by = 'events', $filter_value = 'all')
    {
        $viewer = Auth::user();
        if ($viewer && !access()->isAllowed('events', 'view_events')) {
            return redirect($viewer->getUrl());
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
                $page_id = Pages::select()->where('route', 'events.category.events')->pluck('id')->first();
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
                $page_id = Pages::select()->where('route', 'events.tag.events')->pluck('id')->first();
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

        if (settings()->get('hubmachine.sharing.og.open.graph')) {
            core()->filters()->add_filter('meta_tags', function ($meta_tags) use ($filter_by, $filter_value) {
                $filter_by != 'events' && $filter_value != 'all' && $meta_tags['property_url'] = Html::meta_tag(['property' => 'og:url', 'content' => url("/" . settings()->get('hubmachine.events.prefix') . "/item/{$filter_by}/{$filter_value}")]);
                return $meta_tags;
            }, FiltersManager::HIGH_PRIORITY);
        }

        $media_placeholder = media()->get(settings()->get('hubmachine.media.placeholder'))->getThumbnail(860, 484);

        View::share('events', $events);
        View::share('media_placeholder', $media_placeholder);
        View::share('filter_by', $filter_by);
        View::share('filter_value', $filter_value);
        View::share('filter_value_title', isset($filter_value_title) ? $filter_value_title : null);

        if (!isset($page_id)) {
            $page_id = Pages::select()->where('route', 'events.events')->pluck('id')->first();
        }
        return Pages::view($page_id);
    }

    /**
     * Displays a list of a user's events
     *
     * @param \Illuminate\Http\Request $request
     * @param string $filter_by
     * @param string $filter_value
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse
     */
    public function myevents(Request $request, $filter_by = 'events', $filter_value = 'all')
    {
        $viewer = Auth::user();
        if ($viewer && !access()->isAllowed('events', 'view_events')) {
            return redirect($viewer->getUrl());
        }

        $params = [];
        switch ($filter_by) {
            case 'category':
                if ($filter_value === 'all') {
                    $event_ids = EventEventsCategories::select()->pluck('event_id')->toArray();
                } else {
                    $category = EventCategories::where('id', '=', (int)$filter_value)->first();
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
                    $tag_id = EventTags::where('tag', '=', $filter_value)->pluck('id')->first();
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

        $params['author'] = $viewer;
        $events = (new EventEvents)->getEventEventsSelect($params)->orderBy('created_at', 'DESC')->paginate(20)->keyBy('id');

        if (settings()->get('hubmachine.sharing.og.open.graph')) {
            core()->filters()->add_filter('meta_tags', function ($meta_tags) use ($filter_by, $filter_value) {
                $filter_by != 'events' && $filter_value != 'all' && $meta_tags['property_url'] = Html::meta_tag(['property' => 'og:url', 'content' => url("/" . settings()->get('hubmachine.events.prefix') . "/item/{$filter_by}/{$filter_value}")]);
                return $meta_tags;
            }, FiltersManager::HIGH_PRIORITY);
        }

        $media_placeholder = media()->get(settings()->get('hubmachine.media.placeholder'))->getThumbnail(860, 484);

        View::share('events', $events);
        View::share('media_placeholder', $media_placeholder);
        View::share('filter_by', $filter_by);
        View::share('filter_value', $filter_value);
        View::share('filter_value_title', isset($filter_value_title) ? $filter_value_title : null);

        $page_id = Pages::where('route', 'LIKE', 'events.myevents')->pluck('id')->first();
        
        return Pages::view($page_id);
    }

    /**
     * Displays a list of a tickets.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $filter_by
     * @param string $filter_value
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse
     */
    public function mytickets(Request $request)
    {
        $viewer = Auth::user();
        if ($viewer && !access()->isAllowed('events', 'view_events')) {
            return redirect($viewer->getUrl());
        }

        $page_id = Pages::where('route', 'LIKE', 'events.mytickets')->pluck('id')->first();
        return Pages::view($page_id);
    }

    /**
     * Displays event dashboard.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Http\Request $request
     * @param                          $link
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Throwable
     */
    public function dashboard(Request $request, $link)
    {
        $viewer = Auth::user();
        if ($viewer && !access()->isAllowed('events', 'view_events')) {
            return redirect($viewer->getUrl());
        }
        $event = EventEvents::where('link', $link)->first();
        if (!$event || !$viewer || (!$event->author->getKey() !== $viewer->getKey() && !$viewer->isAdmin())) {
            abort(404);
        }
        if ($event->status == 'draft' && (!$viewer || ($viewer->getKey() !== $event->author->getKey() && !$viewer->isAdmin()))) {
            abort(404);
        }
        
        $media_placeholder = media()->get(settings()->get('hubmachine.media.placeholder'))->getThumbnail(860, 484);

        View::share('event', $event);
        View::share('viewer', $viewer);
        View::share('media_placeholder', $media_placeholder);

        $page_id = Pages::select()->where('route', 'events.event.dashboard')->pluck('id')->first();
        
        return Pages::view($page_id);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @param $page_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse
     * @throws \Throwable
     */
    public function create(Request $request, $event_format_id = null)
    {
        if (!access()->isAllowed('events', 'create_events')) {
            return redirect(\Auth::user()->getUrl());
        }

        if ($event_format_id) {
            $page_id = $event_format_id;
        } else {
            $page = Pages::where('module', '=', 'events')->where('route', '=', 'events.event.create')->orderBy('created_at', 'ASC')->first();
            if (!$page) {
                abort(404);
            }
            $page_id = $page->getKey();
        }

        core()->filters()->add_filter('body_attrs', function ($attrs) {
            $attrs['save-widgets-url'] = route('layout.admin.widget.save_content', ['public']);
            $attrs['save-widgets-draft-url'] = route('layout.admin.widget.save_content', ['draft']);
            $attrs['publish-btn-text'] = __('Publish');
            $attrs['draft-btn-text'] = __('Save to Draft');
            $attrs['cancel-btn-text'] = __('Cancel');
            $attrs['delete-btn-text'] = __('Delete');
            $attrs['is-publish-at'] = true;
            $attrs['page-editable'] = 'true';
            return $attrs;
        });
        core()->filters()->add_filter('event_teaser_widget_show', function ($show) {
            return false;
        });

        $content = view('events::create', [])->render();
        core()->filters()->add_filter('additional_page_content', function ($page_content) use ($content) {
            $page_content['events-event_create'] = $content;
            return $page_content;
        }, FiltersManager::HIGH_PRIORITY);

        View::share('widgets_state', 'edit');


        return Pages::view($page_id);
    }

    /**
     * Show the specified resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param                          $link
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Throwable
     */
    public function show(Request $request, $link, $ticket = null)
    {
        $viewer = Auth::user();
        if ($viewer && !access()->isAllowed('events', 'view_events')) {
            return redirect($viewer->getUrl());
        }
        $event = EventEvents::where('link', $link)->first();
        if (empty($event) || ($event->author && $event->author->shadow_banned && (!$viewer || $event->author->getKey() !== $viewer->getKey()) && (!$viewer || !$viewer->isAdmin()))) {
            return abort(404);
        }
        if ($event->status == 'draft' && (!$viewer || ($viewer->getKey() !== $event->author->getKey() && !$viewer->isAdmin()))) {
            return abort(404);
        }
        View::share('event', $event);

        $filters = core()->filters();
        $filters->add_filter('widget_view_vars', $this->_change_widget_content($event));

        $event_images = media()->filter([
            'resource_id' => ['=' => $event->getKey()],
            'resource_type' => ['=' => EventEvents::class],
            'type' => ['like' => 'events_event_cover_%']
        ])->get();
        $event_url_og_image = null;

        if (!$event_images->isEmpty()) {
            $event_url_og_image = $event_images->first()->getThumbnail(1200, 627);
        } else {
            $event_cover_json = EventWidgetsContent::where('event_id', '=', $event->getKey())->where('widget_type', 'LIKE', 'events_event_cover_json')->first();
            if (!empty($event_cover_json)) {
                $event_url_og_image = json_decode($event_cover_json->content)->poster;
            } else {
                $event_url_og_image = media()->get(settings()->get('hubmachine.sharing.og.image'), settings()->get('hubmachine.sharing.og.image.default'))->getThumbnail(1200, 627);
            }
        }

        $filters->add_filter('meta_tags', function ($meta_tags) use ($filters, $event) {
            $event->teaser && $meta_tags['description'] = Html::meta_tag(['name' => 'description', 'content' => $event->teaser]);
            return $meta_tags;
        }, FiltersManager::HIGH_PRIORITY);

        if (settings()->get('hubmachine.sharing.og.open.graph')) {
            core()->filters()->add_filter('meta_tags', function ($meta_tags) use ($event, $event_url_og_image) {
                $event->title && $meta_tags['property_title'] = Html::meta_tag(['property' => 'og:title', 'content' => $event->title . ' — ' . settings()->get('hubmachine.general.site.title')]);
                $event->teaser && $meta_tags['property_description'] = Html::meta_tag(['property' => 'og:description', 'content' => $event->teaser]);
                $event_url_og_image && $meta_tags['property_image'] = Html::meta_tag(['property' => 'og:image', 'content' => $event_url_og_image]);
                $event->link && $meta_tags['property_url'] = Html::meta_tag(['property' => 'og:url', 'content' => url("/" . settings()->get('hubmachine.events.prefix') . "/item/{$event->link}")]);
                return $meta_tags;
            }, FiltersManager::HIGH_PRIORITY);
        }

        $filters->add_filter('page_title', function ($page_title) use ($event) {
            return $event->getTitle() . ' — ' . settings()->get('hubmachine.general.site.title');
        }, FiltersManager::HIGH_PRIORITY);

        if (access()->isAllowed('events', 'edit_events') || (access()->isAllowed('events', 'create_events') && $viewer && $viewer->id === $event->author->id)) {
            $content = view('events::button_edit', ['route' => route('events.event.edit', [$link])])->render();
            $filters->add_filter('additional_page_content', function ($page_content) use ($content) {
                $page_content['events-event_view'] = $content;
                return $page_content;
            }, FiltersManager::HIGH_PRIORITY);
        }

        /**
         * Modals
         */
        if (\Module::has('Comments') && \Module::find('Comments')->isEnabled()) {
            $modals = [
                'events.event.show.likes' => ['route' => route('comments.likes.authors.get', [$event->getKey(), get_class($event)]), 'uri' => 'likes'],
            ];
        }
        if ($ticket) {
            empty($modals) && $modals = [];
            $modals += [
                'events.event.buy_ticket' => ['route' => route('events.event.buy_ticket_modal', [$event->getLink(), $ticket]), 'uri' => 'buy-ticket', 'from' => route('events.event.show', [$event->getLink()])],
            ];
        }

        $route_name = $request->route()->getName();
        if (isset($modals[$route_name])) {
            core()->filters()->add_filter('body_attrs', function ($attrs) use ($modals, $route_name) {
                $attrs['data-modal-load']      = $modals[$route_name]['route'];
                $attrs['data-modal-url']       = $modals[$route_name]['uri'];
                isset($modals[$route_name]['from']) && $attrs['data-modal-load-from'] = $modals[$route_name]['from'];
                return $attrs;
            });
        }
        /**
         * End modals
         */

        $page_id = $event->format()->pluck('id')->first();

        $request->attributes->add(['object_type' => EventEvents::class, 'object_id' => $event->getKey()]);

        return Pages::view($page_id);
    }

    /**
     * Follow to event.
     *
     * @param Request $request
     * @param $event_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function follow(Request $request, $event_id)
    {
        $viewer = Auth::user();
        if ($viewer && !access()->isAllowed('events', 'view_events')) {
            return response()->json(['status' => false, 'errors' => ['Permission denied']]);
        }

        $event = EventEvents::find($event_id);
        if (!$event) {
            return response()->json(['status' => false, 'errors' => ['Event not found']]);
        }

        if ($event->ticket_type !== 'free') {
            return response()->json(['status' => false, 'errors' => ['This event is not free']]);
        }

        if (!$viewer->isFollows($event)) {
            $viewer->follow($event, false);
            return response()->json(['status' => true, 'message' => __('events::lang.You have registered for the event')]);
        } else {
            $viewer->unfollow($event);
            return response()->json(['status' => true, 'message' => __('events::lang.You have unregistered for the event')]);
        }
    }

    /**
     * Confirm unfollowing from the event (this is "deleting a ticket")
     *
     * @param Request $request
     * @param $event_id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function unfollowConfirmation(Request $request, $event_id, $user_id = null)
    {
        $viewer = Auth::user();
        if (!$viewer || !access()->isAllowed('events', 'view_events')) {
            return response()->json(['status' => false, 'errors' => ['Permission denied']]);
        }

        $event = EventEvents::find($event_id);
        if (!$event) {
            return response()->json(['status' => false, 'errors' => ['Event not found']]);
        }

        if ($event->ticket_type !== 'free') {
            return response()->json(['status' => false, 'errors' => ['This event is not free']]);
        }

        $content = view('events::events.unfollow_confirmation', ['event' => $event, 'user_id' => $user_id])->render();
        return response()->json(['status' => true, 'data' => $content]);
    }

    /**
     * Unsubscribe from the event (this is "deleting a ticket")
     *
     * @param Request $request
     * @param $event_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function unfollow(Request $request, $event_id, $user_id = null)
    {
        $user = Auth::user();
        if (!$user || !access()->isAllowed('events', 'view_events')) {
            return response()->json(['status' => false, 'errors' => ['Permission denied']]);
        }

        $event = EventEvents::find($event_id);
        if (!$event) {
            return response()->json(['status' => false, 'errors' => ['Event not found']]);
        }

        if ($event->ticket_type !== 'free') {
            return response()->json(['status' => false, 'errors' => ['This event is not free']]);
        }

        $user_id && $user = User::find($user_id);
        if ($user->isFollows($event)) {
            $data_id = $event->getFollower($user)->getKey();
            $user->unfollow($event);
            return response()->json(['status' => true, 'data_id' => $data_id]);
        } else {
            return response()->json(['status' => false, 'errors' => [$user_id ? __('events::lang.Ticket already deleted') : __('events::lang.User already deleted')], 'reload' => true]);
        }
    }

    /**
     * Edit the specified resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param                          $link
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Throwable
     */
    public function edit(Request $request, $link)
    {
        $event = EventEvents::where('link', $link)->first();
        if (empty($event)) {
            return abort(404);
        }

        $viewer = Auth::user();
        if (!access()->isAllowed('events', 'edit_events') && (!access()->isAllowed('events', 'create_events') || !$viewer || $viewer->id !== $event->author->id)) {
            return redirect()->route('events.event.show', ['link' => $link]);
        }

        View::share('event', $event);
        View::share('widgets_state', 'edit');

        $filters = core()->filters();

        $filters->add_filter('body_attrs', function ($attrs) {
            $attrs['page-editable'] = 'true';
            return $attrs;
        });

        $filters->add_filter('widget_view_vars', $this->_change_widget_content($event));

        $event_images = media()->filter([
            'resource_id' => ['=' => $event->getKey()],
            'resource_type' => ['=' => EventEvents::class],
            'type' => ['like' => 'events_event_cover_%']
        ])->get();
        $event_url_og_image = null;

        if (!$event_images->isEmpty()) {
            $event_url_og_image = $event_images->first()->getThumbnail(1200, 627);
        } else {
            $event_cover_json = EventWidgetsContent::where('event_id', '=', $event->getKey())->where('widget_type', 'LIKE', 'events_event_cover_json')->first();
            if (!empty($event_cover_json)) {
                $event_url_og_image = json_decode($event_cover_json->content)->poster;
            } else {
                $event_url_og_image = media()->get(settings()->get('hubmachine.sharing.og.image'), settings()->get('hubmachine.sharing.og.image.default'))->getThumbnail(1200, 627);
            }
        }

        $filters->add_filter('meta_tags', function ($meta_tags) use ($filters, $event) {
            $event->teaser && $meta_tags['description'] = Html::meta_tag(['name' => 'description', 'content' => $event->teaser]);
            return $meta_tags;
        }, FiltersManager::HIGH_PRIORITY);

        if (settings()->get('hubmachine.sharing.og.open.graph')) {
            $filters->add_filter('meta_tags', function ($meta_tags) use ($event, $event_url_og_image) {
                $event->title && $meta_tags['property_title'] = Html::meta_tag(['property' => 'og:title', 'content' => $event->title . ' — ' . settings()->get('hubmachine.general.site.title')]);
                $event->teaser && $meta_tags['property_description'] = Html::meta_tag(['property' => 'og:description', 'content' => $event->teaser]);
                $event_url_og_image && $meta_tags['property_image'] = Html::meta_tag(['property' => 'og:image', 'content' => $event_url_og_image]);
                $event->link && $meta_tags['property_url'] = Html::meta_tag(['property' => 'og:url', 'content' => url("/" . settings()->get('hubmachine.events.prefix') . "/item/{$event->link}")]);
                return $meta_tags;
            }, FiltersManager::HIGH_PRIORITY);
        }

        $filters->add_filter('page_title', function ($page_title) use ($event) {
            return $event->getTitle() . ' — ' . settings()->get('hubmachine.general.site.title');
        }, FiltersManager::HIGH_PRIORITY);

        $publish_at = $event->publish_at ? core()->time()->localize($event->publish_at)->format('m/d/Y h:i A') : null;
        $content = view('events::edit', ['event_id' => $event->id, 'event_status' => $event->status, 'publish_at' => $publish_at, 'featured' => $event->featured])->render();
        $filters->add_filter('additional_page_content', function ($page_content) use ($content) {
            $page_content['events-event_edit'] = $content;
            return $page_content;
        }, FiltersManager::HIGH_PRIORITY);

        $page_id = $event->format()->pluck('id')->first();
        return Pages::view($page_id);
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
        if (!access()->isAllowed('events', 'edit_events') && (!access()->isAllowed('events', 'create_events') || !$viewer || $viewer->id !== $event->author->id)) {
            return response()->json(['status' => false, 'errors' => ['Permissions denied']]);
        }

        $event_title = $event->getTitle();
        if (strlen($event_title) > 64) {
            $event_title = substr($event_title, 0, 64) . ' ...';
        }

        $content = view('events::events.delete_event', ['event_title' => $event_title, 'event_id' => $event->getKey()])->render();
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
        if (!access()->isAllowed('events', 'edit_events') && (!access()->isAllowed('events', 'create_events') || !$viewer || $viewer->id !== $event->author->id)) {
            return response()->json(['status' => false, 'errors' => ['Permissions denied.']]);
        }

        $event->forceDelete();

        return response()->json(['status' => true, 'redirect' => true, 'url' => route('events.events')]);
    }

    private function _change_widget_content($event)
    {
        return function ($view_vars) use ($event) {
            $view_vars['content'] = EventWidgetsContent::where('widget_type', $view_vars['config']->name)->where('event_id', $event->getKey())->pluck('content')->first();
            $view_vars['event_id'] = $event->getKey();
            return $view_vars;
        };
    }
}
