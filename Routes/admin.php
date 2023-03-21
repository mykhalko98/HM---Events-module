<?php
/*
|--------------------------------------------------------------------------
| Admin Events Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "admin", "web" middleware group.
|
*/

Route::group([], function()
{
    //Module Settings
    Route::get('/main-settings', 'IndexController@getFormSettings')->name('events.form_settings');
    Route::post('/main-settings', 'IndexController@updateSettings')->name('events.update_settings');

    Route::get('/', 'EventEventsController@index')->name('events.admin.main');
	Route::get('/settings/', 'EventEventsController@settings')->name('events.admin.settings');
	Route::post('/settings/categories/create', 'EventEventsController@createCategory')->name('events.admin.categories.create');
    Route::post('/settings/tags/create', 'EventEventsController@createTag')->name('events.admin.tags.create');
	Route::post('/settings/category/{id}/edit', 'EventEventsController@editCategory')->name('events.admin.categories.edit');
    Route::post('/settings/tag/{id}/edit', 'EventEventsController@editTag')->name('events.admin.tags.edit');
	Route::delete('/settings/category/{id}/delete', 'EventEventsController@deleteCategory')->name('events.admin.categories.delete');
    Route::delete('/settings/tag/{id}/delete', 'EventEventsController@deleteTag')->name('events.admin.tags.delete');
    Route::get('/event/{id}/delete/confirmation', 'EventEventsController@delete_confirmation')->name('events.admin.event.delete.confirmation');
    Route::delete('/event/{id}/delete','EventEventsController@delete_event')->name('events.admin.event.delete');
    Route::get('/{filter_by?}/{filter_value?}', 'EventEventsController@index')->name('events.admin.events');
});