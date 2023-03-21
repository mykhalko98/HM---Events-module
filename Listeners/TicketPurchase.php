<?php

namespace Modules\Events\Listeners;

use Modules\Events\Entities\EventTickets;

class TicketPurchase
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

        $charge = $event->getCharge();
        $order->status = $charge->status ?? 'failed';
        $order->save();

        if ($order->status == 'succeeded') {
            $buyer = $order->buyer;
            if (!$buyer->isFollows($order->event)) {
                $buyer->follow($order->event, false);
            }
            try {
                notifications()->notifyViaJob($order->buyer, 'send_notification', 'ticket_successfully_issued', ['author' => $order->event, 'subject' => $order->event, 'object' => $order]);
            } catch (\Throwable $th) {
                \Log::error($th->getMessage() . "\n" . $th->getFile() . ': ' . $th->getLine() . "\n" . $th->getTraceAsString());
            }
        }

        $event->code = 200;
        return true;
	}
}
