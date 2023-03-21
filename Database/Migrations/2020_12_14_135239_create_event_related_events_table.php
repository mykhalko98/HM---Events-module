<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventRelatedEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_related_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('event_id');
            $table->bigInteger('related_event_id');
            $table->integer('score_category')->default(0);
            $table->float('score_title')->default(0);
            $table->float('score_content')->default(0);
            $table->timestamps();

            $table->unique(['event_id', 'related_event_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_related_events');
    }
}
