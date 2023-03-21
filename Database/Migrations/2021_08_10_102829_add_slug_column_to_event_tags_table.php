<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSlugColumnToEventTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('event_tags', function (Blueprint $table) {
            $table->string('slug', 255)->nullable(false)->after('tag');
        });
        $tags = \Modules\Events\Entities\EventTags::all();
        foreach ($tags as $tag) {
            $tag->slug = \Illuminate\Support\Str::slug($tag->tag);
            $tag->save();
        }
        Schema::table('event_tags', function (Blueprint $table) {
            $table->string('slug', 255)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('event_tags', function (Blueprint $table) {
            $table->dropColumn(['slug']);
        });
    }
}
