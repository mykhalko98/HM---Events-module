<?php

namespace Modules\Events\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Lang;

class EventTags extends Model
{
    protected $table = 'event_tags';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['tag', 'slug'];

    /**
     * The attributes that should be mutated to dated.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    public function events()
    {
        return $this->belongsToMany(EventEvents::class, 'event_events_tags', 'tag_id', 'event_id');
    }

    /**
     * Get tag name with translation or without.
     *
     * @param bool $translated
     * @return string
     */
    public function getName($translated = true)
    {
        if(Lang::has("events::tags_names.{$this->__get('tag')}") && $translated) {
            return __("events::tags_names.{$this->__get('tag')}");
        } else {
            return $this->__get('tag');
        }
    }

    /**
     * Create select for event tags getting.
     *
     * @param array $params
     * @return mixed
     */
    public function getEventTagsSelect($params = [])
    {
        return $this::select();
    }
}
