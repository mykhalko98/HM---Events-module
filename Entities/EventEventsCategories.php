<?php

namespace Modules\Events\Entities;

use Illuminate\Database\Eloquent\Model;

class EventEventsCategories extends Model
{
    protected $table = 'event_events_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['category_id', 'event_id'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];
}
