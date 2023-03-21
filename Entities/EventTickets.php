<?php

namespace Modules\Events\Entities;

use Illuminate\Database\Eloquent\Model;

class EventTickets extends Model
{
    protected $table = 'event_tickets';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['event_id', 'price', 'quantity', 'count_per_person', 'name', 'details', 'early_price', 'early_price_expiry'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'early_price_expiry'];

    /**
     * Get special resource.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function event()
    {
        return $this->hasOne(EventEvents::class, 'id', 'event_id');
    }

    /**
     * How many tickets have been sold.
     *
     */
    public function sold()
    {
        return $this->hasMany(\Modules\Events\Entities\EventTicketOrders::class, 'ticket_id', 'id')->whereIn('status', ['succeeded', 'refund_request'])->count();
    }

    /**
     * Get count bought tickets (which have status succeeded OR refund_request, but exclude refunded tickets).
     *
     * @param $user
     * @return int
     */
    public function getBoughtTickets($user = null)
    {
        if ($user) {
            return (int)EventTicketOrders::select(\DB::raw('SUM(count) as total_tickets'))
                ->where('event_id', '=', $this->event->getKey())
                ->where('buyer_type', get_class($user))
                ->where('buyer_id', '=', $user->getKey())
                ->whereIn('status', ['succeeded', 'refund_request'])
                ->first()
                ->total_tickets;
        } else {
            return (int)EventTicketOrders::select(\DB::raw('SUM(count) as total_tickets'))
                ->where('event_id', '=', $this->event->getKey())
                ->whereIn('status', ['succeeded', 'refund_request'])
                ->first()
                ->total_tickets;
        }

    }
}
