<?php

namespace Modules\Events\Entities;

use Hubmachine\Users\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Hubmachine\Core\Models\Pages\Pages;
use Hubmachine\Friendships\Traits\Friendable;
use Hubmachine\Search\Traits\Searchable;

use Modules\Events\Entities\EventCategories;
use Modules\Events\Entities\EventEventsCategories;
use Modules\Events\Entities\EventTickets;

use App\Traits\Fileable;
use App\Traits\HasLikes;
use App\Traits\HasComments;

/**
 * @OA\Schema(
 *     schema="EventEvents",
 *     title="EventEvents",
 *     description="EventEvents object",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="link", type="string"),
 *     @OA\Property(property="teaser", type="string"),
 *     @OA\Property(property="ticket_url", type="string"),
 *     @OA\Property(property="ticket_type", type="string", enum={"free", "paid", "combined"}),
 *     @OA\Property(property="start_time", type="string"),
 *     @OA\Property(property="end_time", type="string"),
 *     @OA\Property(property="location", type="string"),
 *     @OA\Property(property="author_type", type="string"),
 *     @OA\Property(property="author_id", type="integer"),
 *     @OA\Property(property="privacy", type="string", enum={"public", "private"}),
 *     @OA\Property(property="status", type="string", enum={"public", "draft"}),
 *     @OA\Property(property="featured", type="boolean"),
 *     @OA\Property(property="event_format_id", type="integer"),
 *     @OA\Property(property="publish_at", type="string"),
 *     @OA\Property(property="created_at", type="string"),
 *     @OA\Property(property="updated_at", type="string"),
 *     @OA\Property(property="deleted_at", type="string", default=null),
 * )
 */
class EventEvents extends Model
{
    use SoftDeletes;
    use Fileable;
    use Searchable;
    use Friendable;
    use HasLikes;
    use HasComments;

    protected $table = 'event_events';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'link', 'teaser', 'ticket_url', 'ticket_type', 'start_time', 'end_time', 'location', 'author_type', 'author_id', 'privacy', 'status', 'featured', 'event_format_id', 'publish_at'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['start_time', 'end_time', 'publish_at', 'created_at', 'updated_at', 'deleted_at'];


    /**
     * Elasticsearch variables.
     */
    protected $searchable_fields = ['title', 'link', 'teaser', 'ticket_type', 'author_type', 'author_id', 'privacy', 'status', 'events_event_content', 'shadow_banned'];
    protected $searchable_exclude_select = ['events_event_content', 'shadow_banned'];
    protected $searchable_field_types = ['ticket_type' => 'keyword', 'author_type' => 'keyword', 'author_id' => 'keyword', 'privacy' => 'keyword', 'status' => 'keyword', 'shadow_banned' => 'boolean'];
    protected $searchable_displayname = "Events";

    /**
     * Add document to model's index
     */
    public function searchableFieldsModifier($values)
    {
        $event_widgets_content = EventWidgetsContent::select()
            ->where('event_id', '=', $values['id'])
            ->whereIn('widget_type', ['events_event_content'])
            ->orderBy('updated_at', 'ASC')
            ->pluck('content', 'widget_type');
        foreach ($event_widgets_content as $name => $content) {
            $values['widget_' . $name] = $content;
        }

        if (isset($values['author_type']) && $values['author_type'] === User::class) {
            if ($author = User::find($values['author_id'])) {
                $values['shadow_banned'] = $author->shadow_banned ? 'true' : 'false';
            } else {
                $values['shadow_banned'] = 'false';
            }
        }

        $values['photo'] = [];
        $event_image = media()->filter([
            'resource_id' => ['=' => $values['id']],
            'resource_type' => ['=' => get_class($this)],
            'type' => ['LIKE' => 'events_event_cover_cropped']
        ])->first();
        if ($event_image) {
            $photo_sizes = [
                'small'  => [160, 90],
                'medium' => [320, 180],
                'large'  => [640, 360],
            ];
            foreach ($photo_sizes as $key => $sizes) {
                $values['photo'][$key] = $event_image->getThumbnail($sizes[0], $sizes[1]);
            }
        }

        return $values;
    }

    /**
     * Searchable results.
     *
     */
    public function searchableFormatResults($hit)
    {
        $fields = $hit['_source'];
        $highlights = isset($hit['highlight']) ? $hit['highlight'] : [];

        $url = route('events.event.show', [$fields['link']]);

        $display = $fields['title'];
        if (count($highlights) > 0) {
            if (isset($highlights['title.ngram'])) {
                $display = $highlights['title.ngram'][0];
            } elseif (isset($highlights['title'])) {
                $display = $highlights['title'][0];
            }
//            else {
//                $display = strip_tags(array_shift($highlights)[0], '<em><a>');
//            }
        }

        $photo = '';
        if (isset($fields['photo']['medium'])) {
            $photo = $fields['photo']['medium'];
        }
        $cover = '';
        if ($photo) {
            $cover = "<img class='bd-placeholder-img mr-3' src='{$photo}' width='160'>";
        }

        return "<li class=\"media my-3 position-relative\">{$cover}<div class=\"media-body align-self-center\"><a class=\"stretched-link\" target=\"_blank\" href=\"{$url}\"><div class=\"title mt-0\">{$display}</div></a></div></li>";
    }

    /**
     * Get event author.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function author()
    {
        return $this->morphTo();
    }

    /**
     * Get event format.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function format()
    {
        return $this->hasOne(Pages::class, 'id', 'event_format_id');
    }

    /**
     * Get event category
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function category()
    {
        return $this->hasOne(EventEventsCategories::class, 'id', 'category_id');
    }

    /**
     * Get event categories.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories()
    {
        return $this->belongsToMany(EventCategories::class, 'event_events_categories', 'event_id', 'category_id');
    }

    /**
     * Get event tags.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany(EventTags::class, 'event_events_tags', 'event_id', 'tag_id');
    }

    /**
     * Get event title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->__get('title');
    }

    /**
     * Get event title.
     *
     * @return string
     */
    public function getTeaser()
    {
        return $this->__get('teaser');
    }

    public function getCover($w = 300, $h = 375)
    {
        $event_image = media()->filter([
            'resource_id' => ['=' => $this->getKey()],
            'resource_type' => ['=' => get_class($this)],
            'type' => ['LIKE' => 'events_event_cover_cropped']
        ])->first();
        if ($event_image) {
            return $event_image->getThumbnail($w, $h);
        }

        $event_cover_json = EventWidgetsContent::select()
            ->where('event_id', '=', $this->getKey())
            ->where('widget_type', '=', 'events_event_cover_json')
            ->first();
        if ($event_cover_json) {
            return json_decode($event_cover_json->content)->poster;
        }

        return null;
    }

    public function getPhoto($w = 300, $h = 375)
    {
        return $this->getCover($w, $h);
    }

    /**
     * Get content for feed post.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    public function getFeedContent()
    {
        return view('events::feed_content', ['event' => $this]);
    }

    /**
     * Get event content. Data stored in event_widgets_content table.
     *
     * @return mixed
     */
    public function getContent()
    {
        return EventWidgetsContent::where('event_id', $this->getKey())->where('widget_type', 'event_content')->pluck('content')->first();
    }

    /**
     * Get event widgets data.
     *
     * @return mixed
     */
    public function getContents()
    {
        return EventWidgetsContent::where('event_id', $this->getKey())->pluck('content', 'widget_type');
    }

    /**
     * Returns link of the event
     *
     * @return string
     */
    public function getLink()
    {
        return $this->__get('link');
    }

    /**
     * Returns URL
     *
     * @return string (url)
     */
    public function getUrl()
    {
        return route('events.event.show', [$this->getLink()]);
    }

    /**
     * Return Name
     *
     * @return string
     */
    public function getName()
    {
        return 'Event';
    }

    /**
     * Get ticket_type.
     *
     * @return mixed
     */
    public function getTicketType()
    {
        return $this->__get('ticket_type');
    }

    /**
     * Get ticket_type for the front-end.
     * free = free
     * paid & combined = paid
     *
     * @return string
     */
    public function getFrontTicketType()
    {
        return $this->__get('ticket_type') == 'free'
            ? __('Free')
            : __('Paid');
    }

    /**
     * Get ticket
     */
    public function ticket()
    {
        return $this->hasOne(EventTickets::class, 'event_id', 'id');
    }

    /**
     * Get tickets
     */
    public function tickets()
    {
        return $this->hasMany(EventTickets::class, 'event_id', 'id');
    }

    /**
     * Check if user can but ticket.
     *
     * @param User $user
     * @param EventTickets $ticket
     * @return array
     */
    public function canBuyTicket(User $user, EventTickets $ticket)
    {
        $message = '';
        $status  = false;
        if (EventTicketOrders::where('event_id', '=', $this->getKey())->where('buyer_id', '=', $user->getKey())->where('status', 'LIKE', 'pending')->exists()) {
            $message = '<div class="text-center font-weight-bold alert alert-info">'.__('events::lang.Your purchase is still being processed').'</div>';
        } else {
            if ($ticket->getBoughtTickets($user)) {
                $message = '<div class="text-center font-weight-bold alert alert-info">'.__('events::lang.You already have purchased tickets').'</div>';
            } elseif ($ticket->quantity && $ticket->getBoughtTickets() >= $ticket->quantity) {
                $message = '<div class="text-center font-weight-bold alert alert-info">'.__('events::lang.All tickets sold out').'</div>';
            } else {
                $status = true;
            }
        }

        return ['message' => $message, 'status' => $status];
    }

    /**
     * Cuts up to 200 chars from the first three non-empty lines, ending with end of sentence or word.
     *
     * @param string $text
     * @param int $max_length Maximum allowed length of returned text
     * @param int $length_to_end_with_end Allowed length to end the text with end of sentence or word.
     *
     * @return string
     */
    public static function get_caption_text(string $text, int $max_length = 200, int $length_to_end_with_end = 170)
    {
        $text = trim($text);
        $text = html_entity_decode($text);
        $text = strip_tags($text);
        $text = preg_replace('/^[ \t]*[\r\n]+/m', ' ', $text); // remove empty lines from the string
        $text = preg_split('/\r\n|\r|\n/', $text); // split the string by line breaks
        $text = array_slice($text, 0, 3); // get the first three lines
        $text = implode("\n", $text); // convert the array we got to the string
        $text = wordwrap($text, $max_length, '~~get_first_{$max_length}_chars~~'); // split the string by each $max_length characters
        $text = explode('~~get_first_{$max_length}_chars~~', $text); // convert the string to an array
        $text = reset($text); // get the first $max_length characters of the string
        $dot_pos = strlen($text) <= $length_to_end_with_end ? false : self::multi_strrpos($text, ['.', '!', '?'], $length_to_end_with_end); // get text till the end of last sentence but at least $length_to_end_with_end characters
        $dot_pos === false || ($text = substr($text, 0, ++$dot_pos));
        $text = substr($text, 0, 200);
        if (substr($text, -1) != '.' && substr($text, -1) != '!' && substr($text, -1) != '?') {
            if (strlen($text) > 197) {
                $text = substr($text, 0, 197) . '...';
            } else {
                $text = $text . '...';
            }
        }
        return $text;
    }

    private static function multi_strrpos($haystack, $needles, $offset = 0)
    {
        foreach ($needles as $needle) {
            if (strpos($haystack, $needle, $offset) !== false) {
                return strrpos($haystack, $needle, $offset);
            }
        }
        return false;
    }

    /**
     * Create select for events getting.
     *
     * @param array $params
     * @return mixed
     */
    public function getEventEventsSelect($params = array())
    {
        $event_events_table = $this->getTable();

        $select = $this::select("{$event_events_table}.*");
        $user_table_joined = false;

        // select events with ids
        if (isset($params['ids']) && is_array($params['ids'])) {
            $select->whereIn("{$event_events_table}.id", $params['ids']);
        }

        // events with status
        if (isset($params['status']) && !empty($params['status'])) {
            is_array($params['status'])
                ? $select->whereIn("{$event_events_table}.status", $params['status'])
                : $select->where("{$event_events_table}.status", 'LIKE', $params['status']);
        }

        // exclude events of shadow banner users
        if (!isset($params['include_shadow_banned_users']) || $params['include_shadow_banned_users'] !== true) {
            if (!$user_table_joined) {
                $user_table_joined = true;
                $select->leftJoin('users', function ($join) use ($event_events_table) {
                    $join->on("users.id", "{$event_events_table}.author_id")
                        ->where('author_type', '=', \Hubmachine\Users\Models\User::class);
                });
            }
            $select->where(function ($q) use ($params) {
                $q->where('users.shadow_banned', '=', 0);
                if (isset($params['include_shadow_banned_users']) && is_array($params['include_shadow_banned_users'])) {
                    $q->orWhereIn('users.id', $params['include_shadow_banned_users']);
                }
                $q->orWhereNull('users.id');
            });
        }

        // exclude events of banned users
        if (!isset($params['include_banned_users']) || $params['include_banned_users'] !== true) {
            if (!$user_table_joined) {
                $user_table_joined = true;
                $select->leftJoin('users', function ($join) use ($event_events_table) {
                    $join->on('users.id', "{$event_events_table}.author_id")
                        ->where('author_type', '=', \Hubmachine\Users\Models\User::class);
                });
            }
            $select->where(function($query) {
                $query->where('users.banned', '=', 0)
                    ->orWhereNull('users.id');
            });
        }

        // exclude non indexing formats
        if (isset($params['only_indexing_formats']) && $params['only_indexing_formats'] === true) {
            $indexing_event_formats = Pages::where('name', 'LIKE', 'events_event_format%')->where('indexing', '=', 1)->pluck('id')->toArray();
            $select->whereIn("{$event_events_table}.event_format_id", $indexing_event_formats);
        }

        return $select;
    }
}