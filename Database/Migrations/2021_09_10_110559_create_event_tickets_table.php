<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->enum('combine_type', ['basic', 'pro', 'individual_pro'])->nullable(true)->default(NULL);
            $table->unsignedDecimal('price', 16);
            $table->unsignedInteger('quantity')->nullable(true)->default(NULL);
            $table->integer('count_per_person')->nullable(true);
            $table->string('name', 255)->nullable(true)->default(NULL);
            $table->text('details')->nullable(true)->default(NULL);
            $table->unsignedDecimal('early_price', 16)->nullable(true)->default(NULL);
            $table->timestamp('early_price_expiry')->nullable(true)->default(NULL);
            $table->timestamps();

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
        Schema::dropIfExists('event_tickets');
    }
}
