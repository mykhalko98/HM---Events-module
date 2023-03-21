<?php
// this routes are generated automatically (from pages db table)
Route::group(['middleware' => ['web', App\Http\Middleware\PageVisibility::class], 'namespace' => 'Modules\Events\Http\Controllers'], function(){
    Route::get('/events/{filter_by?}/{filter_value?}', 'EventEventsController@index')->name('events.events');
    Route::get('/events/category/{filter_value?}', 'EventEventsController@index')->name('events.category.events');
    Route::get('/events/tag/{filter_value?}', 'EventEventsController@index')->name('events.tag.events');
});