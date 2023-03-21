<?php

namespace Modules\Events\Entities;

use Illuminate\Database\Eloquent\Model;

class EventEventsTags extends Model
{
    protected $table = 'event_events_tags';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['event_id', 'tag_id'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];
}
