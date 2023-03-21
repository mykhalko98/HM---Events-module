<?php

namespace Modules\Events\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Lang;

class EventCategories extends Model
{
    protected $table = 'event_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'slug'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];


    /**
     * Events by category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function events()
    {
        return $this->belongsToMany(EventEvents::class, 'event_events_categories', 'category_id', 'event_id');
    }

    /**
     * Get category name with translation or without.
     *
     * @param bool $translated
     * @return string
     */
    public function getName($translated = true)
    {
        if (Lang::has("events::category_names.{$this->__get('name')}") && $translated) {
            return __("events::category_names.{$this->__get('name')}");
        } else {
            return $this->__get('name');
        }
    }

    /**
     * Create select for event categories getting.
     *
     * @param array $params
     * @return mixed
     */
    public function getEventCategoriesSelect($params = array())
    {
        return $this::select();
    }
}
