<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSlugColumnToEventCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('event_categories', function (Blueprint $table) {
            $table->string('slug', 255)->nullable(false)->after('name');
        });
        $categories = \Modules\Events\Entities\EventCategories::all();
        foreach ($categories as $category) {
            $category->__set('slug', \Illuminate\Support\Str::slug($category->name));
            $category->save();
        }
        Schema::table('event_categories', function (Blueprint $table) {
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
        Schema::table('event_categories', function (Blueprint $table) {
            $table->dropColumn(['slug']);
        });
    }
}
