<?php

namespace Modules\Events\Entities;

use Hubmachine\Widgets\Facades\Widgets;
use Illuminate\Database\Eloquent\Model;

class EventWidgetsContent extends Model
{

    protected $table = 'event_widgets_content';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['event_id', 'widget_type', 'content'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function event()
    {
        return $this->hasOne(EventEvents::class, 'id', 'event_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function widgets()
    {
        return $this->hasMany(Widgets::class, 'id', 'widget_type');
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->__get('content');
    }
}
