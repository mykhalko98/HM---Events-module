<?php

namespace Modules\Events\Entities;

use Illuminate\Database\Eloquent\Model;

class EventTicketOrders extends Model
{
    protected $table = 'event_ticket_orders';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['ticket_id', 'event_id', 'count', 'status', 'buyer_type', 'buyer_id', 'note'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * Get ticker.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ticket()
    {
        return $this->hasOne(EventTickets::class, 'id', 'ticket_id');
    }

    /**
     * Get buyer.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function buyer()
    {
        return $this->morphTo();
    }

    /**
     * Get event
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function event()
    {
        return $this->hasOne(EventEvents::class, 'id', 'event_id');
    }

    /**
     * Get active and deleted events.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function withTrashedEvent()
    {
        return $this->hasOne(EventEvents::class, 'id', 'event_id')->withTrashed();
    }

    /**
     * Get transactions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions()
    {
        return $this->hasMany(\Hubmachine\Payments\Models\PaymentTransactions::class, 'order_id', 'id')->orderBy('created_at', 'DESC');
    }
}
