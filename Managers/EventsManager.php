<?php

namespace Modules\Events\Managers;

use Illuminate\Support\Facades\URL;
use Hubmachine\Core\Models\Pages\Pages;
use Modules\Events\Entities\EventCategories;
use Modules\Events\Entities\EventEvents;
use Modules\Events\Entities\EventTags;

class EventsManager
{
    public function __construct()
    {
    }

    public function getSitemap()
    {
        $links = [];

        $events_list_events_page = Pages::where('name', 'LIKE', 'events_list_events')->first();
        if ($events_list_events_page && $events_list_events_page->indexing) {
            $links['events'][] = route($events_list_events_page->route);
        }

        $events_categories_page = Pages::where('name', 'LIKE', 'events_categories')->first();
        if ($events_categories_page && $events_categories_page->indexing) {
            $links['events-category'][] = route($events_categories_page->route);
        }
        $events_list_events_by_category_page = Pages::where('name', 'LIKE', 'events_list_events_by_category')->first();
        if ($events_list_events_by_category_page && $events_list_events_by_category_page->indexing) {
            $event_categories = (new EventCategories)->getEventCategoriesSelect()->get();
            foreach ($event_categories as $post_category) {
                $links['events-category'][] = route($events_list_events_by_category_page->route, ['filter_value' => $post_category->id]);
            }
        }
        $events_tags_page = Pages::where('name', 'LIKE', 'events_tags')->first();
        if ($events_tags_page && $events_tags_page->indexing) {
            $links['events-tag'][] = route($events_tags_page->route);
        }
        $events_list_events_by_tag_page = Pages::where('name', 'LIKE', 'events_list_events_by_tag')->first();
        if ($events_list_events_by_tag_page && $events_list_events_by_tag_page->indexing) {
            $event_tags = (new EventTags)->getEventTagsSelect()->get();
            foreach ($event_tags as $post_category) {
                $links['events-tag'][] = route($events_list_events_by_tag_page->route, ['filter_value' => $post_category->__get('tag')]);
            }
        }

        $event_events = (new EventEvents)->getEventEventsSelect(['only_indexing_formats' => true])->get();
        foreach ($event_events as $event) {
            $links['events'][] = route('events.event.show', $event->link);
        }

        return $links;
    }

    public function getEventFromUrlParams()
    {
        $uri = str_replace(url('/'), '', request()->url());
        try {
            $route = app('router')->getRoutes()->match(app('request')->create($uri));
        } catch (\Throwable $th) {
            return null;
        }
        $event_link = $route->parameters()['link'] ?? null;
        if (!$event_link) {
            return null;
        }
        return EventEvents::where('link', $event_link)->first();
    }
    /**
     * Include resources to page.
     */
    public function resources()
    {
        core()->resources()->includeResources([
            ['path' => 'js/events/events.js',   'stack' => 'footer_scripts'],
            ['path' => 'js/events/payments.js', 'stack' => 'footer_scripts'],
            ['path' => 'https://js.stripe.com/v3/',   'stack' => 'header_scripts', 'source' => 'remote'],
            ['path' => 'css/events/events.css', 'stack' => 'header_styles'],
        ]);
    }

    public function admin_resources()
    {
        core()->resources()->includeResources([
            ['path' => 'js/events/admin/events.js',   'stack' => 'admin_footer_scripts'],
            ['path' => 'css/events/admin/events.css', 'stack' => 'admin_header_styles'],
        ]);
    }
}
