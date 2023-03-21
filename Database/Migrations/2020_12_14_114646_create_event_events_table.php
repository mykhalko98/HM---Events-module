<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 255);
            $table->string('link', 255)->unique();
            $table->string('teaser', 200);
            $table->string('ticket_url', 255)->nullable(true);
            $table->enum('ticket_type', ['free', 'paid', 'combined'])->default('free');
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->string('location', 255);
            $table->morphs('author');
            $table->enum('privacy', ['public', 'private'])->default('public');
            $table->enum('status', ['public', 'draft'])->default('draft');
            $table->boolean('featured')->default(false);
            $table->bigInteger('event_format_id')->unsigned();
            $table->timestamp('publish_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('event_format_id')
                ->references('id')
                ->on('pages')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('event_events');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
