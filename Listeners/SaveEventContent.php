<?php

namespace Modules\Events\Listeners;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

use Hubmachine\Core\Models\Layout\Widgets;
use Hubmachine\Media\Models\MediaFile;
use Illuminate\Validation\Rule;
use Modules\Events\Entities\EventCategories;
use Modules\Events\Entities\EventEvents;
use Modules\Events\Entities\EventEventsCategories;
use Modules\Events\Entities\EventEventsTags;
use Modules\Events\Entities\EventTags;
use Modules\Events\Entities\EventTicketOrders;
use Modules\Events\Entities\EventTickets;
use Modules\Events\Entities\EventWidgetsContent;

use Module;
use Auth;

class SaveEventContent
{
	public function handle($listener_event)
	{
        $is_event_editing = (0 === strpos(URL::previous(), URL::to('/' . settings()->get('hubmachine.events.prefix') . '/event/')));
        if (!$is_event_editing) {
            return TRUE;
        }

        /*Get event*/
        $uri = str_replace(url('/'), '', URL::previous());
        try {
            $route = app('router')->getRoutes()->match(app('request')->create($uri));
        } catch (\Throwable $th) {
            return FALSE;
        }
        $event_link = $route->parameters()['link'];
        $event = EventEvents::where('link', $event_link)->first();
        /*END*/

        $listener_event->prevent = true; // preventing a widget saving for exclude case of storing a event's data into a widget because it would be shown for the next user on creating a event


        $widgets = $listener_event->request->get('widgets');
        $featured = $listener_event->request->get('featured') == 'true';
        /*Get publish at*/
        if ($listener_event->status == 'public') {
            if ($publish_at = $listener_event->request->get('publishAt')) {
                $publish_at = Carbon::createFromFormat('D M d Y H:i:s e+', $publish_at)->setTimezone('UTC');
                if ($publish_at < Carbon::now()->subMinutes(1)) {
                    $listener_event->errors[] = 'Event must have publish at more current date.';
                } elseif ($publish_at > Carbon::now()->addMinutes(1)) {
                    $listener_event->status = 'draft';
                }
            } else {
                $publish_at = Carbon::now();
            }
        }
        /*END*/

        $send_notification = false;

        DB::beginTransaction();
        try {
            $event->__set('featured', $featured);
            $event->__set('publish_at', isset($publish_at) ? $publish_at : null);
            ($listener_event->status == 'public' &&  $event->status == 'draft') && $send_notification = true;
            $event->__set('status', $listener_event->status);

            $must_have_widgets = ['events_event_title', 'events_event_dates', 'events_event_location'];
            $exists_widgets = [];

            foreach($widgets as $widget) {
                $widget_id   = $widget['widgetId'];
                $widget_type = Widgets::find($widget_id)->name;
                $widget_data = $widget['data'];
                $exists_widgets[] = $widget_type;

                $create_widget_content = true;

                switch($widget_type) {
                    case 'events_event_categories':
                        $create_widget_content = false;
                        $current_categories = $event->categories()->pluck('name', 'category_id')->all();
                        if ((!isset($widget['category']) && count($current_categories))
                            || (isset($widget['category'])
                                && (count($current_categories) !== count($widget['category'])
                                    || !empty(array_diff($current_categories, $widget['category']))))) {
                            $send_notification = true;
                        }
                        EventEventsCategories::where('event_id', '=', $event->getKey())->delete();
                        if (isset($widget['category'])) {
                            foreach ($widget['category'] as $ind => $category_name) {
                                $category_name = trim(strip_tags($category_name));
                                $_category = EventCategories::where('name', '=', $category_name)->first();
                                if ($_category) {
                                    $event_category_link = new EventEventsCategories(['event_id' => $event->getKey(), 'category_id' => $_category->getKey()]);
                                    $event_category_link->save();
                                }
                            }
                        }
                        break;
                    case 'events_event_tags':
                        $create_widget_content = false;
                        EventEventsTags::where('event_id', '=', $event->getKey())->delete();
                        if (isset($widget['tag'])) {
                            foreach ($widget['tag'] as $ind => $tag_name) {
                                $tag_name = trim(strip_tags($tag_name));
                                $_tag = EventTags::where('tag', '=', $tag_name)->first();
                                if ($_tag) {
                                    $event_tag = new EventEventsTags(['event_id' => $event->getKey(), 'tag_id' => $_tag->getKey()]);
                                    $event_tag->save();
                                }
                            }
                        }
                        break;
                    case 'events_event_title' :
                        $widget_data = strip_tags(html_entity_decode(trim(str_replace('&nbsp;', '', $widget_data))));
                        if (EventEvents::where('title', $widget_data)->where('id', '!=', $event->getKey())->count()) {
                            $listener_event->errors[] = 'This event title already engaged. Use another one.';
                        } elseif (!empty($widget_data)) {
                            $event->__set('title', $widget_data);
                            $event->__set('link', Str::slug($widget_data));
                        } else {
                            $listener_event->errors[] = 'Title can\'t be empty.';
                        }
                        break;
                    case 'events_event_dates':
                        $create_widget_content = false;

                        if (isset($widget_data['timezone']) && !in_array($widget_data['timezone'], [array_keys(core()->time()->getTimezoneList())])) {
                            $timezone = $widget_data['timezone'];
                            $event->__set('timezone', $timezone);
                        } else {
                            $timezone = settings()->get('hubmachine.general.site.timezone', 'Europe/London');
                        }

                        if (empty($widget_data['start_time']) || empty($widget_data['end_time'])) {
                            $listener_event->errors[] = 'Date can\'t be empty.';
                        } else {
                            $start_time = Carbon::parse($widget_data['start_time'].' '.$timezone);
                            $end_time   = Carbon::parse($widget_data['end_time'].' '.$timezone);
                        }

                        if (isset($start_time) && isset($end_time)) {
                            if ($start_time->timestamp >= $end_time->timestamp) {
                                $listener_event->errors[] = 'Start date can\'t be less or equal end date.';
                            }
                            $event->__set('start_time', $start_time->timestamp);
                            $event->__set('end_time', $end_time->timestamp);
                        }
                        break;
                    case (strpos($widget_type, 'events_event_tickets') === 0):
                        $create_widget_content = false;
                        $ticket_errors = [];

                        $validator = Validator::make($widget_data, [
                            'ticket_type' => ['required', Rule::in(['free', 'paid', 'combined'])],
                            'ticket_url'  => 'nullable|url',
                        ]);
                        if ($validator->fails()) {
                            $ticket_errors = array_merge($ticket_errors ?: [], $validator->errors()->all());
                            $listener_event->errors = array_merge($listener_event->errors ?: [], $validator->errors()->all());
                        }
                        if ($event->ticket_type !== $widget_data['ticket_type']) {
                            $orders = EventTicketOrders::where('event_id', '=', $event->getKey())
                                ->where('status', 'LIKE', 'succeeded')
                                ->count();
                            if ($orders) {
                                $listener_event->errors[] = $ticket_errors[] = 'You cannot change the ticket type because there have already been successful purchases.';
                            }
                        }
                        if ($widget_data['ticket_type'] == 'paid' || $widget_data['ticket_type'] == 'combined') {
                            switch ($widget_data['ticket_type']) {
                                case 'paid':
                                    $validator = Validator::make($widget_data, [
                                        'ticket_price'            => 'required|numeric',
                                        'ticket_quantity'         => 'nullable|numeric',
                                        'ticket_count_per_person' => 'required_with:ticket_quantity|nullable|numeric|min:0|lte:ticket_quantity'
                                    ], ['ticket_count_per_person.lte' => 'Count per person must be less or equal than Quantity']);
                                    if ($validator->fails()) {
                                        $ticket_errors = array_merge($ticket_errors ?: [], $validator->errors()->all());
                                        $listener_event->errors = array_merge($listener_event->errors ?: [], $validator->errors()->all());
                                    }

                                    if (empty($ticket_errors)) {
                                        EventTickets::where('event_id', '=', $event->getKey())->whereNotNull('ticket_name')->delete();
                                        $event_ticket = EventTickets::where('event_id', '=', $event->getKey())->first();
                                        if (!$event_ticket) {
                                            $event_ticket = new EventTickets([
                                                'event_id'         => $event->getKey(),
                                                'price'            => $widget_data['ticket_price'],
                                                'quantity'         => $widget_data['ticket_quantity'] ?? NULL,
                                                'count_per_person' => $widget_data['ticket_count_per_person'] ?? NULL
                                            ]);
                                        } else {
                                            $event_ticket->__set('price',              $widget_data['ticket_price']);
                                            $event_ticket->__set('quantity',           $widget_data['ticket_quantity'] ?? NULL);
                                            $event_ticket->__set('count_per_person',   $widget_data['ticket_count_per_person'] ?? NULL);
                                            $event_ticket->__set('name',               NULL);
                                            $event_ticket->__set('details',            NULL);
                                            $event_ticket->__set('early_price',        NULL);
                                            $event_ticket->__set('early_price_expiry', NULL);
                                        }
                                        $event_ticket->save();
                                    }
                                    break;
                                case 'combined':
                                    $validator_rules = [
                                        'ticket_name'               => 'required|string',
                                        'ticket_details'            => 'required|string',
                                        'ticket_price'              => 'required|numeric|min:1',
                                        'ticket_early_price'        => 'required|numeric',
                                        'ticket_early_price_expiry' => 'required|string',
                                        'ticket_quantity'           => 'nullable|numeric|min:0',
                                        'ticket_count_per_person'   => 'required_with:ticket_quantity|nullable|numeric|min:0|lte:ticket_quantity',
                                    ];
                                    $enclosed_ticket_keys = preg_grep('/enclosed_ticket_/', array_keys($widget_data));
                                    foreach ($enclosed_ticket_keys as $enclosed_ticket_key) {
                                        $values = $widget_data[$enclosed_ticket_key];
                                        $validator = Validator::make($values, $validator_rules, ['ticket_count_per_person.lte' => 'Count per person must be less or equal than Quantity']);
                                        $widget_data[$enclosed_ticket_key]['valid'] = true;
                                        if ($validator->fails()) {
                                            $widget_data[$enclosed_ticket_key]['valid'] = false;
                                            if ($enclosed_ticket_key == 'enclosed_ticket_0' || !empty($values['ticket_name'])) {
                                                $_errors = array_map(fn($item) => str_replace('_', ' ', ucfirst($enclosed_ticket_key)) . ': ' . $item, $validator->errors()->all());
                                                $ticket_errors = array_merge($ticket_errors ?: [], $_errors);
                                                $listener_event->errors = array_merge($listener_event->errors ?: [], $_errors);
                                            }
                                        }
                                    }

                                    if (empty($ticket_errors)) {
                                        $timezone = $event->timezone ?? settings()->get('hubmachine.general.site.timezone', 'Europe/London');
                                        EventTickets::where('event_id', '=', $event->getKey())->whereNull('name')->delete();
                                        foreach ($enclosed_ticket_keys as $enclosed_ticket_key) {
                                            $values = $widget_data[$enclosed_ticket_key];
                                            if (!$values['valid']) {
                                                continue;
                                            }
                                            $early_price_expiry = Carbon::parse($values['ticket_early_price_expiry'].' '.$timezone);
                                            $event_ticket = EventTickets::where('event_id', '=', $event->getKey())->where('name', '=', $values['ticket_name'])->first();
                                            if (!$event_ticket) {
                                                $event_ticket = new EventTickets([
                                                    'event_id'           => $event->getKey(),
                                                    'price'              => $values['ticket_price'],
                                                    'quantity'           => $values['ticket_quantity'] ?? NULL,
                                                    'count_per_person'   => $values['ticket_count_per_person'] ?? NULL,
                                                    'name'               => $values['ticket_name'] ?? NULL,
                                                    'details'            => $values['ticket_details'] ?? NULL,
                                                    'early_price'        => $values['ticket_early_price'] ?? NULL,
                                                    'early_price_expiry' => $early_price_expiry,
                                                ]);
                                            } else {
                                                $event_ticket->__set('price',              $values['ticket_price']);
                                                $event_ticket->__set('quantity',           $values['ticket_quantity'] ?? NULL);
                                                $event_ticket->__set('count_per_person',   $values['ticket_count_per_person'] ?? NULL);
                                                $event_ticket->__set('name',               $values['ticket_name'] ?? NULL);
                                                $event_ticket->__set('details',            $values['ticket_details'] ?? NULL);
                                                $event_ticket->__set('early_price',        $values['ticket_early_price']);
                                                $event_ticket->__set('early_price_expiry', $early_price_expiry);
                                            }
                                            $event_ticket->save();
                                        }
                                    }
                                    break;
                                default:
                                    $listener_event->errors[] = 'Incorrect ticket type.';
                                    break;
                            }
                        }
                        if ($event->ticket_type !== $widget_data['ticket_type']) {
                            $send_notification = true;
                        }
                        $event->__set('ticket_type', $widget_data['ticket_type']);
                        $event->__set('ticket_url', $widget_data['ticket_url']);
                        break;
                    case 'events_event_location':
                        $create_widget_content = false;
                        if (!isset($widget_data['location']) || empty($widget_data['location'])) {
                            $listener_event->errors[] = 'Location can\'t bew empty.';
                        }
                        $widget_data['location'] = strip_tags($widget_data['location']);
                        if ($event->location !== $widget_data['location']) {
                            $send_notification = true;
                        }
                        $event->__set('location', $widget_data['location']);
                        break;
                    case 'events_event_privacy':
                        $create_widget_content = false;
                        if (!in_array($widget_data['privacy'], ['public', 'private'])) {
                            $listener_event->errors[] = 'Select privacy from dropdown.';
                        }
                        $event->__set('privacy', $widget_data['privacy']);
                        break;
                    case 'events_event_cover':
                        if (isset($widget['video']) && isset($widget['poster'])) {
                            $widget_data_json = [
                                "video"  => $widget['video'],
                                "poster" => $widget['poster']
                            ];

                            $event_widget = EventWidgetsContent::where('widget_type', 'LIKE', $widget_type . '_json')->where('event_id', '=', $event->getKey())->first();
                            if ($event_widget) {
                                $event_widget->__set('content', json_encode($widget_data_json));
                            } else {
                                $event_widget = new EventWidgetsContent(['widget_type' => $widget_type . '_json', 'event_id' => $event->getKey(), 'content' => json_encode($widget_data_json)]);
                            }
                            $event_widget->save();

                            //remove old media files
                            $event_images = media()->filter(['resource_id' => ['=' => $event->getKey()], 'type' => ['like' => "{$widget_type}_%"]])->get();
                            foreach ($event_images as $event_image) {
                                $event_image->forceDelete() && $event_image->deleteFile();
                            }

                            $content = "data:image/jpeg;base64," . base64_encode(file_get_contents($widget_data_json['poster']));
                            $media_file = media()->save_from_base64($content, $event, $event->author, null, ['type' => "{$widget_type}_original"]);
                            $media_file = media()->save_from_base64($content, $event, $event->author, null, ['parent_id' => $media_file->getKey(), 'type' => "{$widget_type}_cropped"]);
                        } elseif (!empty($widget['imageId'])) {
                            $create_widget_content = false;

                            $original = MediaFile::find((int)$widget['imageId']);
                            if (!$original->resource_id) {
                                $original_old = MediaFile::select()->where('resource_id', '=', $event->getKey())->where('type', '=', "{$widget_type}_original")->first();
                                if ($original_old) {
                                    $cropped_old = $original_old->getChildren()->first();
                                    $original_old->forceDelete() && $original_old->deleteFile();
                                    $cropped_old->forceDelete() && $cropped_old->deleteFile();
                                }
                                $original = MediaFile::find((int)$widget['imageId']);
                                $cropped = $original->getChildren()->first();
                                media()->update($original, null, $event);
                                media()->update($cropped, null, $event);
                            }
                            //remove old posters
                            EventWidgetsContent::where('widget_type', 'LIKE', $widget_type.'%')->where('event_id', '=', $event->getKey())->delete();
                        }
                        break;
                    case 'events_event_content':
                        if (strlen($widget_data) > 0) {
                            $event->__set('teaser', EventEvents::get_caption_text($widget_data));
                        }
                        break;
                    default:
                        if (is_array($widget_data)) {
                            $widget_data = json_encode($widget_data);
                        }
                        break;
                }

                if ($create_widget_content) {
                    $event_widget = EventWidgetsContent::where('widget_type', $widget_type)->where('event_id', $event->getKey())->first();
                    if ($event_widget) {
                        $event_widget->__set('content', $widget_data);
                    } else {
                        $event_widget = new EventWidgetsContent(['widget_type' => $widget_type, 'event_id' => $event->getKey(), 'content' => $widget_data]);
                    }
                    $event_widget->save();
                }
            }

            if ($event->ticket_type == 'combined' && isset($event_ticket)) {
                if ($event->start_time <= $event_ticket->early_price_expiry) {
                    $listener_event->errors[] = 'Early price expiry date must be less than event start date.' . ' - ' . $event->start_time . ' - ' . $event_ticket->early_price_expiry;
                }
            }
            foreach ($must_have_widgets as $must_have_widget) {
                if (!in_array($must_have_widget, $exists_widgets)) {
                    $listener_event->errors[] = 'Event must have the "'. ucfirst(str_replace('_', ' ', $must_have_widget)) .'" widget.';
                }
            }
            if (!empty($listener_event->errors)) {
                DB::rollback();
                return FALSE;
            }
            $event->save();

            $listener_event->response_data = ['redirect' => TRUE, 'url' => route('events.event.show', ['link' => Str::slug($event->__get('title'))])];
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
        DB::commit();

        if ($send_notification) {
            $viewer = Auth::user();
            if (!$viewer->shadow_banned) {
                notifications()->notifyViaJob($event->getFollowers(), 'send_notification', new \Modules\Events\Notifications\EventEdited(['subject' => $event, 'author' => $viewer]));
            }
        }
	}
}
