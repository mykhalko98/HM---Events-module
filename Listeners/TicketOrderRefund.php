<?php

namespace Modules\Events\Listeners;

use Modules\Events\Entities\EventTickets;

class TicketOrderRefund
{
    public function handle($event)
    {
        $metadata = $event->metadata;
        if ($metadata->object_type !== EventTickets::class) {
            return true;
        }

        $order = $metadata->order_type::find($metadata->order_id);
        if (!$order) {
            $event->errors[] = 'Order not found';
            $event->code = 400;
            return false;
        }

        $refund = $event->getRefund();
        if ($refund->status == 'succeeded') {
            if (filter_var($refund->metadata->is_refund_request, FILTER_VALIDATE_BOOLEAN)) {
                try {
                    $author = $refund->metadata->author_type::find($refund->metadata->author_id);
                    notifications()->notifyViaJob($order->buyer, 'send_notification', 'refund_request_approved', ['author' => $author, 'subject' => $order->event, 'object' => $order]);
                } catch (\Throwable $th) {
                    \Log::error($th->getMessage() . "\n" . $th->getFile() . ': ' . $th->getLine() . "\n" . $th->getTraceAsString());
                }
            }
            $order->status = 'refunded';
            $order->save();
            $buyer = $order->buyer;
            if ($buyer->isFollows($order->event)) {
                $buyer->unfollow($order->event);
            }
        }

        $event->code = 200;
        return true;
	}
}
