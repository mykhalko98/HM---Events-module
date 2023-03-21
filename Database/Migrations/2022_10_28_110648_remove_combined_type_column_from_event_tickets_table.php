<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveCombinedTypeColumnFromEventTicketsTable extends Migration
{
    public function up()
    {
        Schema::table('event_tickets', function($table) {
            $table->dropColumn('combined_type');
        });
    }

    public function down()
    {
        Schema::table('artevent_ticketsicles', function($table) {
            $table->enum('combine_type', ['basic', 'pro', 'individual_pro'])->nullable(true)->default(NULL);
        });
    }
}
