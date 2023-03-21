<?php
/*
|--------------------------------------------------------------------------
| Web Events Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "web" middleware group.
|
*/

Route::group([], function()
{
    // ajax
    Route::get('/event/{id}/categories', 'EventCategoriesController@eventCategories')->name('events.event.categories');
    Route::get('/event/{id}/tags', 'EventTagsController@eventTags')->name('events.event.tags');

    Route::put('/categories', 'EventCategoriesController@store')->name('events.category.store')->middleware('auth');
    Route::put('/tags', 'EventTagsController@store')->name('events.tag.store')->middleware('auth');

    Route::post('/categories/{id}', 'EventCategoriesController@update')->name('events.category.update')->middleware('auth');
    Route::post('/tags/{id}', 'EventTagsController@update')->name('events.tag.update')->middleware('auth');

    Route::delete('/categories/{id}', 'EventCategoriesController@destroy')->name('events.category.destroy')->middleware('auth');
    Route::delete('/tags/{id}', 'EventTagsController@destroy')->name('events.tag.destroy')->middleware('auth');

    Route::post('/{event_id}/follow', 'EventEventsController@follow')->name('events.event.follow')->middleware('auth');
    Route::get('/{event_id}/unfollow-confirmation/{user_id?}', 'EventEventsController@unfollowConfirmation')->name('events.event.unfollow_confirmation')->middleware('auth');
    Route::post('/{event_id}/unfollow/{user_id?}', 'EventEventsController@unfollow')->name('events.event.unfollow')->middleware('auth');

    // UI
    Route::get('/create-event/{event_format_id?}', 'EventEventsController@create')->name('events.event.create')->middleware('auth');
    Route::get('/event/{link}', 'EventEventsController@show')->name('events.event.show');
    Route::get('/event/{link}/dashboard', 'EventEventsController@dashboard')->name('events.event.dashboard')->middleware(App\Http\Middleware\PageVisibility::class)->middleware('auth');
    Route::get('/event/{link}/edit', 'EventEventsController@edit')->name('events.event.edit')->middleware('auth');
    Route::get('/event/{link}/likes', 'EventEventsController@show')->name('events.event.show.likes');
    Route::get('/categories', 'EventCategoriesController@index')->name('events.categories')->middleware(App\Http\Middleware\PageVisibility::class);
    Route::get('/tags', 'EventTagsController@index')->name('events.tags')->middleware(App\Http\Middleware\PageVisibility::class);
    Route::get('/event/{id}/delete/confirmation', 'EventEventsController@delete_confirmation')->name('events.event.delete.confirmation')->middleware('auth');
    Route::delete('/event/{id}/delete','EventEventsController@delete_event')->name('events.event.delete')->middleware('auth');
    Route::get('/myevent/{filter_by?}/{filter_value?}', 'EventEventsController@myevents')->name('events.myevents')->middleware(App\Http\Middleware\PageVisibility::class)->middleware('auth');
    Route::get('/mytickets', 'EventEventsController@mytickets')->name('events.mytickets')->middleware(App\Http\Middleware\PageVisibility::class)->middleware('auth');
    Route::group(['middleware' => ['web', App\Http\Middleware\PageVisibility::class]], function(){
        Route::get('/{filter_by?}/{filter_value?}', 'EventEventsController@index')->name('events.events');
        Route::get('/category/{filter_value?}', 'EventEventsController@index')->name('events.category.events');
        Route::get('/tag/{filter_value?}', 'EventEventsController@index')->name('events.tag.events');
    });

    // Ticket
    Route::get('/event/{link}/ticket/{ticket}/buy-ticket-modal', 'EventTicketsController@buyTicketModal')->name('events.event.buy_ticket_modal')->middleware('auth');
    Route::get('/event/{link}/ticket/{ticket}/buy-ticket', 'EventEventsController@show')->name('events.event.buy_ticket')->middleware('auth');
    Route::post('/ticket/{id}/payment', 'EventTicketsController@payment')->name('events.ticket.payment')->middleware('auth');
    Route::get('/ticket-order/{id}/refund-confirmation', 'EventTicketsController@refundConfirmation')->name('events.ticket_order.refund_confirmation')->middleware('auth');
    Route::post('/ticket-order/{id}/refund', 'EventTicketsController@refund')->name('events.ticket_order.refund')->middleware('auth');
    Route::get('/ticket-order/{id}/refund-request', 'EventTicketsController@refundRequest')->name('events.ticket_order.refund_request')->middleware('auth');
    Route::post('/ticket-order/{id}/send-refund-request', 'EventTicketsController@sendRefundRequest')->name('events.ticket_order.send_refund_request')->middleware('auth');
    Route::get('/ticket-order/{id}/view-note', 'EventTicketsController@viewNote')->name('events.ticket_order.view_note')->middleware('auth');
    Route::post('/create-new-enclosed-ticket', 'EventTicketsController@createNewEnclosedTicket')->name('events.create_new_enclosed_ticket');
    Route::delete('/ticket/{id}/delete','EventTicketsController@deleteTicket')->name('events.ticket.delete')->middleware('auth');
});
