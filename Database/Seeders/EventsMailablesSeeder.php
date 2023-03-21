<?php

namespace Modules\Events\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Hubmachine\Notifications\Models\Mailables;

class EventsMailablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // EventEdited
        if (!Mailables::where('class', '=', 'Modules\Events\Notifications\EventEdited')->exists()) {
            $mailable_id = DB::table('mailables')->insertGetId([
                'subject'    => 'Event edited',
                'class'      => 'Modules\Events\Notifications\EventEdited',
                'view'       => 'event_edited',
                'module'     => 'events',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $layout_id = DB::table('layouts')->insertGetId([
                'object_type' => 'mailable',
                'object_id'   => $mailable_id,
                'data'        => json_encode(['content' => "<h1>Hello!</h1>\n<p>{!! \$user_name !!}edited {!! \$subject_name !!}.</p>\n@component('mail::button', ['url' => '{!! \$subject_url !!}']) See {!! \$subject_name !!} @endcomponent"]),
                'modified_by' => 1,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s')
            ]);
            mailbuilder()->generateViewFile($mailable_id, $layout_id);
        }

        // EventSoonStart
        if (!Mailables::where('class', '=', 'Modules\Events\Notifications\EventSoonStart')->exists()) {
            $mailable_id = DB::table('mailables')->insertGetId([
                'subject'    => 'Event soon start',
                'class'      => 'Modules\Events\Notifications\EventSoonStart',
                'view'       => 'event_soon_start',
                'module'     => 'events',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $layout_id = DB::table('layouts')->insertGetId([
                'object_type' => 'mailable',
                'object_id'   => $mailable_id,
                'data'        => json_encode(['content' => "<h1>Hello!</h1>\n<p>Event starts in {!! \$time !!}.</p>\n@component('mail::button', ['url' => '{!! \$subject_url !!}']) See {!! \$subject_name !!} @endcomponent"]),
                'modified_by' => 1,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s')
            ]);
            mailbuilder()->generateViewFile($mailable_id, $layout_id);
        }

        // Event Ticket Refund Request
        if (!Mailables::where('class', '=', 'Modules\Events\Notifications\RefundRequest')->exists()) {
            $mailable_id = DB::table('mailables')->insertGetId([
                'subject'    => 'Event Ticket Refund Request',
                'class'      => 'Modules\Events\Notifications\RefundRequest',
                'view'       => 'event_ticket_refund_request',
                'module'     => 'events',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $layout_id = DB::table('layouts')->insertGetId([
                'object_type' => 'mailable',
                'object_id'   => $mailable_id,
                'data'        => json_encode(['content' => "<p>{!! \$user_name !!} sent <a href='{!! \$subject_url !!}'>event</a> ticket refund request.</p>{!! \$description !!}\n@component('mail::button', ['url' => '{!! \$refund_link !!}']) Go to refund @endcomponent"]),
                'modified_by' => 1,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s')
            ]);
            mailbuilder()->generateViewFile($mailable_id, $layout_id);
        }

        // Event Ticket Successfully issued
        if (!Mailables::where('class', '=', 'Modules\Events\Notifications\TicketSuccessfullyIssued')->exists()) {
            $mailable_id = DB::table('mailables')->insertGetId([
                'subject'    => 'Ticket successfully issued',
                'class'      => 'Modules\Events\Notifications\TicketSuccessfullyIssued',
                'view'       => 'event_ticket_successfully_issued',
                'module'     => 'events',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $layout_id = DB::table('layouts')->insertGetId([
                'object_type' => 'mailable',
                'object_id'   => $mailable_id,
                'data'        => json_encode(['content' => "<p>Your ticket on the <a href='{!! \$subject_url !!}'>event</a> has been successfully issued.</p><p>Order Number: {!! \$order_number !!}</p>\n@component('mail::button', ['url' => '{!! \$mytickets_url !!}']) Go to my tickets @endcomponent"]),
                'modified_by' => 1,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s')
            ]);
            mailbuilder()->generateViewFile($mailable_id, $layout_id);
        }

        // Event Ticket Refund Request Approved
        if (!Mailables::where('class', '=', 'Modules\Events\Notifications\RefundRequestApproved')->exists()) {
            $mailable_id = DB::table('mailables')->insertGetId([
                'subject'    => 'Ticket refund request successfully approved',
                'class'      => 'Modules\Events\Notifications\RefundRequestApproved',
                'view'       => 'event_ticket_refund_request_approved',
                'module'     => 'events',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $layout_id = DB::table('layouts')->insertGetId([
                'object_type' => 'mailable',
                'object_id'   => $mailable_id,
                'data'        => json_encode(['content' => "<p>Your request for a refund ticket on the  <a href='{!! \$subject_url !!}'>event</a> has been successfully approved.</p>\n@component('mail::button', ['url' => '{!! \$mytickets_url !!}']) Go to my tickets @endcomponent"]),
                'modified_by' => 1,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s')
            ]);
            mailbuilder()->generateViewFile($mailable_id, $layout_id);
        }
    }
}
