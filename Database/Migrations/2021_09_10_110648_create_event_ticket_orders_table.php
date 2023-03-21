<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventTicketOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_ticket_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('event_id');
            $table->enum('status', ['succeeded', 'pending', 'failed', 'refund_request', 'refunding', 'refunded'])->default('pending');
            $table->string('buyer_type', 255);
            $table->integer('buyer_id');
            $table->text('note')->nullable(true)->default(null);
            $table->timestamps();

            $table->foreign('ticket_id')
                ->references('id')
                ->on('event_tickets')
                ->onDelete('CASCADE');

            $table->foreign('event_id')
                ->references('id')
                ->on('event_events')
                ->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_ticket_orders');
    }
}
