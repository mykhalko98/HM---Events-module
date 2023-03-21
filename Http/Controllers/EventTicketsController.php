<?php

namespace Modules\Events\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use Modules\Events\Entities\EventEvents;
use Modules\Events\Entities\EventTickets;
use Modules\Events\Entities\EventTicketOrders;

use Hubmachine\Payments\Models\PaymentTransactions;

use Carbon\Carbon;
use Auth;

class EventTicketsController extends Controller
{

    /**
     * Displays a modal window for buying a ticket.
     *
     * @param Request $request
     * @param $link
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     * @throws \Throwable
     */
    public function buyTicketModal(Request $request, $link, $ticket)
    {
        $viewer = Auth::user();
        if ($viewer && !access()->isAllowed('events', 'view_events')) {
            return response()->json(['status' => false, 'errors' => ['Permission denied']]);
        }
        $event = EventEvents::where('link', $link)->first();
        if (!$event) {
            return response()->json(['status' => false, 'errors' => ['Event not found'], 'reload' => true]);
        }
        $ticket = EventTickets::find($ticket);
        if (!$ticket || $ticket->event_id !== $event->getKey()) {
            return response()->json(['status' => false, 'errors' => ['Ticket not found'], 'reload' => true]);
        }
        // Check if order in process
        if (EventTicketOrders::where('ticket_id', '=', $ticket->getKey())->where('buyer_id', '=', $viewer->getKey())->where('status', 'LIKE', 'pending')->exists()) {
            return response()->json(['status' => false, 'errors' => ['Your purchase is still being processed']]);
        }
        // Check ticket limits
        $total_bought_tickets = $ticket->getBoughtTickets();
        if ($ticket->getBoughtTickets($viewer)) {
            return response()->json(['status' => false, 'errors' => ['You already have purchased tickets'], 'reload' => true]);
        } elseif ($ticket->quantity && $total_bought_tickets >= $ticket->quantity) {
            return response()->json(['status' => false, 'errors' => ['All tickets sold out'], 'reload' => true]);
        }
        $available_tickets = $ticket->quantity ? $ticket->quantity - $total_bought_tickets : null;

        $content = view('events::tickets.buy_ticket_modal', ['viewer' => $viewer, 'event' => $event, 'ticket' => $ticket, 'available_tickets' => $available_tickets])->render();
        return response()->json(['status' => true, 'data' => $content]);
    }

    /**
     * Process ticket payment.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Stripe\Exception\ApiErrorException
     * @throws \Throwable
     */
    public function payment(Request $request, $id)
    {
        $viewer = Auth::user();
        if ($viewer && !access()->isAllowed('events', 'view_events')) {
            return response()->json(['status' => false, 'errors' => ['Permission denied']]);
        }

        $ticket = EventTickets::find($id);
        if (!$ticket) {
            return response()->json(['status' => false, 'errors' => ['Ticket not found']]);
        }
        // Check if order in process
        if (EventTicketOrders::where('ticket_id', '=', $ticket->getKey())->where('buyer_id', '=', $viewer->getKey())->where('status', 'LIKE', 'pending')->exists()) {
            return response()->json(['status' => false, 'errors' => ['Your purchase is still being processed']]);
        }
        // Check ticket limits
        $count = (int)$request->get('count') ?? 1;
        $total_bought_tickets = $ticket->getBoughtTickets();
        if ($ticket->getBoughtTickets($viewer)) {
            return response()->json(['status' => false, 'errors' => ['You already have purchased tickets'], 'reload' => true]);
        } elseif ($ticket->quantity && $total_bought_tickets >= $ticket->quantity) {
            return response()->json(['status' => false, 'errors' => ['All tickets sold out'], 'reload' => true]);
        } elseif ($ticket->quantity && $ticket->quantity - $total_bought_tickets < $count) {
            return response()->json(['status' => false, 'errors' => ["you can't buy more than " . $ticket->quantity - $total_bought_tickets], 'reload' => true]);
        }

        $available_tickets = $ticket->quantity ? $ticket->quantity - $total_bought_tickets : null;
        $validator = \Validator::make($request->all(), [
            'ticket' => 'required|numeric|in:'.$ticket->getKey(),
            'email'  => 'required|email',
            'name'   => 'required|string',
            'count'  => [Rule::requiredIf($ticket->count_per_person !== 1), 'numeric', 'min:1']+($available_tickets > 0 && $ticket->count_per_person > 0 ? ['max:'.min($available_tickets, $ticket->count_per_person)] : []),
            'amount' => 'required|numeric',
        ], ['in' => 'You cannot change read-only data']);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()->all()]);
        }

        $event = $ticket->event;
        $price = $ticket->price*100;
        $note  = NULL;
        if ($event->ticket_type == 'combined' && Carbon::now()->timestamp < $ticket->early_price_expiry->timestamp) {
            $price = $ticket->early_price*100;
            $note  = 'Early price';
        }
        if ($count > 1) {
            $price *= $count;
        }
        if ((int)$request->get('amount') !== (int)$price) {
            return response()->json(['status' => false, 'errors' => ['Invalid price, the page will be reloaded'], 'reload' => true]);
        }

        DB::BeginTransaction();
        try {
            $order = new EventTicketOrders([
                'ticket_id'  => $ticket->getKey(),
                'event_id'   => $event->getKey(),
                'count'      => $count,
                'status'     => 'pending',
                'buyer_type' => get_class($viewer),
                'buyer_id'   => $viewer->getKey(),
                'note'       => $note
            ]);
            $order->save();

            if (config('services.stripe.mode') == 'test') {
                \Stripe\Stripe::setApiKey(config('services.stripe.test_secret'));
            } else {
                \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            }

            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $price,
                'currency' => strtolower(settings()->get('hubmachine.api.stripe.currency', 'USD')),
                'description' => 'Payment for a ticket to the "' . $event->getTitle() . '" event.',
                'receipt_email' => $viewer->email,
                'metadata' => [
                    'object_type' => EventTickets::class,
                    'object_id'   => $ticket->getKey(),
                    'order_type'  => get_class($order),
                    'order_id'    => $order->getKey(),
                ],
            ]);
            if (!$paymentIntent) {
                DB::rollback();
                \Log::error('ERROR PaymentIntent: Intent not created. User id - ' . $viewer->getKey() . '; Ticket id - ' . $ticket->getKey());
                return response()->json(['status' => false, 'errors' => ['Error payment']]);
            }
        } catch (\Throwable $th) {
            DB::rollback();
            \Log::error($th->getMessage() . "\n" . $th->getFile() . ': ' . $th->getLine() . "\n" . $th->getTraceAsString());
            return response()->json(['status' => false, 'errors' => [$th->getMessage()]]);
        }
        DB::commit();

        return response()->json([
            'status'       => true,
            'clientSecret' => $paymentIntent->client_secret,
            'redirect'     => route('events.event.show', $event->getLink()),
        ]);
    }

    /**
     * Open modal for refund confirmation.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function refundConfirmation(Request $request, $id)
    {
        $ticket_order = EventTicketOrders::find($id);
        if (!$ticket_order) {
            return response()->json(['status' => false, 'errors' => ['Order not found']]);
        }

        if ($ticket_order->status == 'refunded') {
            return response()->json(['status' => false, 'errors' => ['Charge has already been refunded'], 'redirect' => route('events.event.dashboard', [$ticket_order->event->getLink()])]);
        }

        $content = view('events::tickets.refund_confirmation', ['ticket_order' => $ticket_order])->render();
        return response()->json(['status' => true, 'data' => $content]);
    }

    /**
     * Refund process.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function refund(Request $request, $id)
    {
        $ticket_order = EventTicketOrders::find($id);
        if (!$ticket_order) {
            return response()->json(['status' => false, 'errors' => ['Order not found']]);
        }

        $event = $ticket_order->event;
        if (!$event) {
            return response()->json(['status' => false, 'errors' => ['Event not found']]);
        }

        $viewer = Auth::user();
        if (!$viewer->isAdmin() && $viewer->getKey() !== $event->author->getKey()) {
            return response()->json(['status' => false, 'errors' => ['Permission denied'], 'reload' => true]);
        }

        $transaction = PaymentTransactions::where('order_type', get_class($ticket_order))
            ->where('order_id', '=', $ticket_order->getKey())
            ->where('status', 'LIKE', 'succeeded')
            ->where('type', 'LIKE', 'payment_intent.succeeded')
            ->first();
        if (!$transaction) {
            return response()->json(['status' => false, 'errors' => ['Transaction not found']]);
        }

        if (config('services.stripe.mode') == 'test') {
            \Stripe\Stripe::setApiKey(config('services.stripe.test_secret'));
        } else {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        }

        DB::beginTransaction();
        try {
            $intent = \Stripe\Refund::create([
                'payment_intent' => $transaction->payment_id,
                'metadata' => [
                    'object_type' => EventTickets::class,
                    'object_id'   => $ticket_order->ticket_id,
                    'order_type'  => get_class($ticket_order),
                    'order_id'    => $ticket_order->getKey(),
                    'author_type' => get_class($viewer),
                    'author_id'   => $viewer->getKey(),
                    'is_refund_request' => $ticket_order->status == 'refund_request'
                ],
            ]);
            if (!$intent) {
                \Log::error('ERROR Refund ticket order: Order id - ' . $ticket_order->getKey());
                return response()->json(['status' => false, 'errors' => ['Error refund']]);
            }

            $ticket_order->status = 'refunding';
            $ticket_order->save();

        } catch (\Throwable $th) {
            DB::rollback();
            \Log::error($th->getMessage() . "\n" . $th->getFile() . ': ' . $th->getLine() . "\n" . $th->getTraceAsString());
            if ($th instanceof \Stripe\Exception\InvalidRequestException) {
                switch ($th->getStripeCode()) {
                    case 'charge_already_refunded':
                        return response()->json(['status' => false, 'errors' => ['Charge has already been refunded'], 'redirect' => route('events.event.dashboard', [$event->getLink()])]);
                    default:
                        return response()->json(['status' => false, 'errors' => [$th->getMessage()]]);
                }
            } else {
                return response()->json(['status' => false, 'errors' => [$th->getMessage()]]);
            }
        }
        DB::commit();

        return response()->json(['status' => true, 'message' => __('Refund was created.'), 'redirect' => route('events.event.dashboard', [$event->getLink()])]);
    }

    /**
     * Send refund request.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function refundRequest(Request $request, $id)
    {
        $ticket_order = EventTicketOrders::find($id);
        if (!$ticket_order) {
            return response()->json(['status' => false, 'errors' => ['Order not found']]);
        }

        $event = $ticket_order->event;
        if (!$event) {
            return response()->json(['status' => false, 'errors' => ['Event not found']]);
        }

        $content = view('events::tickets.refund_request', ['ticket_order' => $ticket_order])->render();
        return response()->json(['status' => true, 'data' => $content]);
    }

    /**
     * Send refund request.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendRefundRequest(Request $request, $id)
    {
        $ticket_order = EventTicketOrders::find($id);
        if (!$ticket_order) {
            return response()->json(['status' => false, 'errors' => ['Order not found']]);
        }

        $viewer = \Auth::user();

        if ($viewer->getKey() !== $ticket_order->buyer_id) {
            return response()->json(['status' => false, 'errors' => ['Permission denied']]);
        }

        $event = $ticket_order->event;
        if (!$event) {
            return response()->json(['status' => false, 'errors' => ['Event not found']]);
        }

        $description = $request->get('description') ? trim(strip_tags($request->get('description'), '<a><br>')) : NULL;
        $ticket_order->status = 'refund_request';
        $ticket_order->note =$description;
        $ticket_order->save();

        notifications()->notifyViaJob($event->author, 'send_notification', 'refund_request', [
            'subject'     => $event,
            'author'      => $viewer,
            'object'      => $ticket_order,
            'description' => $description,
            'refund_link' => route('events.event.dashboard', ['link' => $event->getLink(), 'order' => $ticket_order->getKey()])]);

        return response()->json(['status' => true, 'message' => 'Refund request successfully sent', 'reload' => true]);
    }

    public function viewNote(Request $request, $id)
    {
        $ticket_order = EventTicketOrders::find($id);
        if (!$ticket_order) {
            return response()->json(['status' => false, 'errors' => ['Order not found']]);
        }

        $viewer = \Auth::user();

        if (!$viewer->isAdmin() && $viewer->getKey() !== $ticket_order->buyer_id) {
            return response()->json(['status' => false, 'errors' => ['Permission denied'], 'reload' => true]);
        }

        $content = view('events::tickets.display_note', ['ticket_order' => $ticket_order])->render();
        return response()->json(['status' => true, 'data' => $content]);
    }

    public function createNewEnclosedTicket()
    {
        return response()->json(['status' => true, 'data' => view('widgets.events::EventTickets.edit_enclosed_ticket', ['ticket' => null, 'hidden' => null])->render()]);
    }

    public function deleteTicket(Request $request, $id)
    {
        $ticket = EventTickets::find($id);

        if ($ticket === NULL) {
            return response()->json(['status' => false, 'errors' => [__('Ticket not found')]]);
        }

        if (EventTicketOrders::select()->where('ticket_id', '=', $id)->exists()) {
            return response()->json(['status' => false, 'errors' => [__('You cannot delete a ticket that has already been purchased by someone else')]]);
        }

        $ticket->delete();
        return response()->json(['status' => true]);
    }
}
