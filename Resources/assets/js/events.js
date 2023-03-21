class Events {
    initEventChange() {
        let eventContentWrapepr = $('#events_event_tickets-widget [data-ticket-target]');

        $(eventContentWrapepr[0]).addClass(`show active`);

        eventContentWrapepr.each(function(indexTicket, itemTicket) {
            $(itemTicket).addClass(`ticket-target-${indexTicket}`);
            $(itemTicket).attr('data-ticket-target', indexTicket);
        });
    }

    typeChange() {
        let typeInputs = 'ticket_name[]';

        $('.tab-content').on('keyup', function(event) {
            let eventTarget = $(event.target);

            if(eventTarget.attr('name') == typeInputs) {
                $(`[data-target='.ticket-target-${eventTarget.closest('.tab-pane').attr('data-ticket-target')}']`).text(eventTarget.val());
            }
        });
    }

    follow(element) {
        $(element).toggleClass('btn-follow btn-unfollow');
        $(element).addClass('disabled loading');

        let url = $(element).attr('data-url');

        $.ajax({
            url: url,
            type: "POST",
            dataType: 'json',
            data: {'_method': 'POST'},
            success: function (response) {
                if (response.status) {
                    $(element).after(`<div class="text-center font-weight-bold alert alert-info">
                            ${response.message}
                        </div>`);
                    $(element).remove();
                } else {
                    $.toast({
                        heading: 'Error',
                        text: response.errors ? response.errors : 'Error',
                        showHideTransition: 'slide',
                        icon: 'error'
                    });
                }
            },
            error: function (xhr, text, error) {
            }
        });
    }

    unfollow(element) {
        $(element).toggleClass('btn-follow btn-unfollow');
        $(element).addClass('disabled loading');

        let url = $(element).attr('data-url');

        $.ajax({
            url: url,
            type: "POST",
            dataType: 'json',
            data: {'_method': 'POST'},
            success: function (response) {
                if (response.status) {
                    $('[role="dialog"]').modal('hide');
                    if (response.message) {
                        $.toast({
                            heading: 'Success',
                            text: response.message,
                            showHideTransition: 'slide',
                            icon: 'success'
                        });
                    }
                    if (response.reload) {
                        setTimeout(function () {
                            location.reload();
                        }, $.toast.arguments.hideAfter);
                    }

                     if (response.data_id) {
                        var row = $('.items .item[data-id="'+response.data_id+'"]');
                        if (row) {
                            row.fadeOut(600);
                            setTimeout(function() {
                                let parent = row.parent();
                                row.remove();
                                if (!parent.children().length) {
                                    let refunded_tickets = parent.next('.items.refunded-tickets');
                                    if (refunded_tickets && refunded_tickets.hasClass('custom-border-top')) {
                                        refunded_tickets.removeClass('custom-border-top')
                                    }
                                    parent.remove();
                                }
                            }, 600);
                        }
                    }
                } else if (response.status == false) {
                    $.toast({
                        heading: 'Error',
                        text: response.errors ? response.errors : 'Error',
                        showHideTransition: 'slide',
                        icon: 'error'
                    });
                    if (response.reload) {
                        setTimeout(function () {
                            location.reload();
                        }, $.toast.arguments.hideAfter);
                    }
                }
            },
            error: function (xhr, text, error) {
            }
        });
    }

    toggleTicketType(element) {
        let value = element.val();
        switch (value) {
            case 'paid':
                $('#event-paid-ticket-details').fadeIn();
                $('#event-paid-ticket-details').find('.value_field').removeClass('hidden');
                $('#event-combined-ticket-details').fadeOut();
                $('#event-combined-ticket-details').find('.value_field').addClass('hidden');
                break;
            case 'combined':
                $('#event-paid-ticket-details').fadeOut();
                $('#event-paid-ticket-details').find('.value_field').addClass('hidden');
                $('#event-combined-ticket-details').fadeIn();
                $('#event-combined-ticket-details').find('.value_field').removeClass('hidden');
                break;
            default:
                $('#event-paid-ticket-details').fadeOut();
                $('#event-paid-ticket-details').find('.value_field').addClass('hidden');
                $('#event-combined-ticket-details').fadeOut();
                $('#event-combined-ticket-details').find('.value_field').addClass('hidden');
                break;
        }
    }

    createEnclosedTicket(element) {
        let url = element.attr('data-url');
        element.addClass('loading');

        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if(response.status){
                    var newTicketId = Date.now();
                    let newTabLink    = `<li class="nav-item"><a class="nav-link btn btn-primary mx-1 px-3 py-0" data-target=".ticket-target-${newTicketId}" data-toggle="tab" role="tab" data-ticket-name-inject aria-selected="false">New Ticket</a></li>`,
                        ticketWrapper = `${response.data}`,
                        deleteIcon    = '<button class="btn mx-1 p-0 align-self-center bg-transparent border-0 color-dark" onclick="window.hm.events.deleteUnsavedTicket($(this))"><icon-image data-icon="delete_forever"></icon-image></button>';
                                
                    element.parent().before(newTabLink);

                    $('#combinedTicketTabContent').append(ticketWrapper);
                    $('#events_event_tickets-widget [data-ticket-target]').last().addClass(`ticket-target-${newTicketId}`);
                    $('#events_event_tickets-widget [data-ticket-target]').last().attr('data-ticket-target', newTicketId);
                    $('.tab-pane').last().find('[name="ticket_name[]"]').after(deleteIcon);

                    $('[role="tablist"] .nav-link[data-target]').last().trigger('click');

                    let activeTab = $('.datetimepicker_min_current');

                    activeTab.datetimepicker && activeTab.datetimepicker({
                        minDate: new Date(),
                        keepInvalid: true,
                    });
                } else {
                    /* toast */
                    $.toast({ 
                        heading: 'Error',
                        text : response.errors ? response.errors : 'Error',
                        showHideTransition : 'slide',
                        icon: 'error'
                    });
                }
                element.removeClass('loading');
                return false;
            },
            error: function(xhr, text, error){
                /* toast */
                $.toast({ 
                    heading: 'Error',
                    text : error, 
                    showHideTransition : 'slide',
                    icon: 'error'
                });
                element.removeClass('loading');
                return false;
            }
        });
        return false;
    }

    deleteTicket(element) {
        let url = element.attr('data-url');
        element.addClass('loading');

        $.ajax({
            url: url,
            type: 'DELETE',
            dataType: 'json',
            success: function(response) {
                element.removeClass('loading');
                let idDeleteElement = $(element).closest('[data-grouping="enclosed_ticket"]').attr('id');

                $(element).closest('[data-grouping="enclosed_ticket"]').remove();
                $(`[role="tablist"] [id*="link-ticket-${idDeleteElement}"]`).parent().remove();
                $('[role="tablist"] .nav-link[data-target]').first().trigger('click');
            },
            error: function(xhr, text, error){
                /* toast */
                $.toast({ 
                    heading: 'Error',
                    text : error, 
                    showHideTransition : 'slide',
                    icon: 'error'
                });
                element.removeClass('loading');
                return false;
            }
        });
        return false;
    }

    deleteUnsavedTicket(element) {
        var current = $(element).closest('[data-ticket-target]').data('ticket-target');
        $(element).closest('[data-grouping="enclosed_ticket"]').remove();
        $(`[role="tablist"] .nav-link[data-target='.ticket-target-${current}']`).parent().remove();
        $('[role="tablist"] .nav-link[data-target]').first().trigger('click');
    }
}

class Category {
    constructor() {
        $(window).on('load', function(){
            /* category */
            $('.selectpicker-category').on('loaded.bs.select', function() {
                let badge = [];
                $(this).closest('.inner').find('.badge').each(function() {
                    $(this).find(`option[value="${$(this).text()}"]`);
                    badge.push($(this).text());
                });
                $('.selectpicker-category').selectpicker('val', badge);
                $(this).closest('.dropdown').find('.filter-option-inner-inner').text($(this).attr('title'));
            });
            
            $('.selectpicker-category').on('hidden.bs.select', function() {
                $(this).closest('.inner').find('.badge').remove();
                
                for(let i of $(this).selectpicker('val')) {          
                    $(this).closest('.dropdown').before(`
                        <span class="badge badge-primary">${i}</span>
                    `);
                }
                $(this).closest('.dropdown').find('.filter-option-inner-inner').text($(this).attr('title'));
            });

            $('.selectpicker-category').on('changed.bs.select', function() {
                $(this).closest('.inner').find('.badge').remove();
                
                for(let i of $(this).selectpicker('val')) {          
                    $(this).closest('.dropdown').before(`
                        <span class="badge badge-primary">${i}</span>
                    `);
                }
                $(this).closest('.dropdown').find('.filter-option-inner-inner').text($(this).attr('title'));
            });

            $('[widget-name="events_event_categories"] [data-creating="true"] [aria-label="Search"]').on('input', function() {
                if( $(this).closest('.bootstrap-select').find('.no-results').length != 0 ) {
                    $(this).closest('.bootstrap-select').find('.no-results').append(`
                        <br> 
                        <button type="button" class="btn btn-secondary btn-sm btn-add-group" onclick="hm.events.category.create(event)">
                            <i class="material-icons">add</i> 
                            Add new Category
                        </button>
                    `);
                }
            });
        });

    }

    get(event) {
        let url = $(event.target).closest('[data-url]').attr('data-url');

        $(event.target).addClass('loading');
        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: {'_method': 'GET'},
            success: function(response) {
                if(response.status){
                    /* toast */
                    $.toast({ 
                        heading: 'Information',
                        text : response.message ? response.message : 'Category loaded.', 
                        showHideTransition : 'slide',
                        icon: 'info'
                    });
                } else {
                    let errorsString = '';                  
                    if(response.errors) {
                        for( let [key, value] of Object.entries(response.errors) ) {
                            errorsString += '<div class="errors-item">- '+value+'<div>';
                        }
                    }
                    /* toast */
                    $.toast({ 
                        heading: 'Error',
                        text : response.errors ? errorsString : 'Error',
                        showHideTransition : 'slide',
                        icon: 'error'
                    });
                }
                $(event.target).removeClass('loading');
            },
            error: function(xhr, text, error){
                /* toast */
                $.toast({ 
                    heading: 'Error',
                    text : error, 
                    showHideTransition : 'slide',
                    icon: 'error'
                });
                $(event.target).removeClass('loading');
            }
        });
    }

    create(event) {
        event.preventDefault();
        event.stopPropagation();
        
        let url = $(event.target).closest('.bootstrap-select').find('.selectpicker').attr('data-url'),
            name = $(event.target).closest('.bootstrap-select').find('[aria-label="Search"]').val();

        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: {'_method': 'PUT', 'name': name},
            success: function(response) {
                if(response.status){
                    /* toast */
                    $.toast({ 
                        heading: 'Information',
                        text : response.message ? response.message : 'Category created', 
                        showHideTransition : 'slide',
                        icon: 'info'
                    });

                    /* if bootstrap select new category */
                    if( $(event.target).closest('.bootstrap-select').length != 0 ) {
                        let filterOption = $(event.target).closest('.dropdown').find('.filter-option-inner-inner'),
                            filterOptionText = $(event.target).closest('.bootstrap-select').find('.selectpicker').attr('title');

                        $(event.target).closest('.bootstrap-select').find('.selectpicker').append(`
                            <option value="${name}" selected>${name}</option>
                        `);

                        $(event.target).closest('form').find('.badge').remove();
                        for(let i of $(event.target).closest('.bootstrap-select').find('.selectpicker').selectpicker('val')) {            
                            $(event.target).closest('.bootstrap-select').before(`
                                <span class="badge badge-primary">${i}</span>
                            `);
                        }

                        $(event.target).closest('.bootstrap-select').find('.selectpicker').selectpicker('refresh');
                        filterOption.text(filterOptionText);
                    }
                } else {
                    let errorsString = '';                  
                    if(response.errors) {
                        for( let [key, value] of Object.entries(response.errors) ) {
                            errorsString += '<div class="errors-item">- '+value+'<div>';
                        }
                    }
                    /* toast */
                    $.toast({ 
                        heading: 'Error',
                        text : response.errors ? errorsString : 'Error',
                        showHideTransition : 'slide',
                        icon: 'error'
                    });
                }
            },
            error: function(xhr, text, error){
                /* toast */
                $.toast({ 
                    heading: 'Error',
                    text : error, 
                    showHideTransition : 'slide',
                    icon: 'error'
                });
            }
        });
    }

    edit(event) {
        event.preventDefault();

        let url = $(event.target).attr('action'),
            name = $(event.target).find('[name="name"]').val();

        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: {'_method': 'POST', 'name': name},
            success: function(response) {
                if(response.status){
                    /* toast */
                    $.toast({ 
                        heading: 'Information',
                        text : response.message ? response.message : 'Category chaged', 
                        showHideTransition : 'slide',
                        icon: 'info'
                    });
                } else {
                    let errorsString = '';                  
                    if(response.errors) {
                        for( let [key, value] of Object.entries(response.errors) ) {
                            errorsString += '<div class="errors-item">- '+value+'<div>';
                        }
                    }
                    /* toast */
                    $.toast({ 
                        heading: 'Error',
                        text : response.errors ? errorsString : 'Error',
                        showHideTransition : 'slide',
                        icon: 'error'
                    });
                }
                return false;
            },
            error: function(xhr, text, error){
                /* toast */
                $.toast({ 
                    heading: 'Error',
                    text : error, 
                    showHideTransition : 'slide',
                    icon: 'error'
                });
                return false;
            }
        });
        return false;
    }

    delete(event) {
        let url = $(event.target).closest('[data-url]').attr('data-url'),
            name = $(event.target).closest('[data-name]').attr('data-name');

        $(event.target).addClass('loading');
        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: {'_method': 'DELETE', 'name': name},
            success: function(response) {
                if(response.status){
                    /* toast */
                    $.toast({ 
                        heading: 'Information',
                        text : response.message ? response.message : 'Category deleted', 
                        showHideTransition : 'slide',
                        icon: 'info'
                    });

                    $(event.target).remove();
                } else {
                    let errorsString = '';                  
                    if(response.errors) {
                        for( let [key, value] of Object.entries(response.errors) ) {
                            errorsString += '<div class="errors-item">- '+value+'<div>';
                        }
                    }
                    /* toast */
                    $.toast({ 
                        heading: 'Error',
                        text : response.errors ? errorsString : 'Error',
                        showHideTransition : 'slide',
                        icon: 'error'
                    });
                }
                $(event.target).removeClass('loading');
            },
            error: function(xhr, text, error){
                /* toast */
                $.toast({ 
                    heading: 'Error',
                    text : error, 
                    showHideTransition : 'slide',
                    icon: 'error'
                });
                $(event.target).removeClass('loading');
            }
        });
    }
}

class Tag {
    constructor() {
        $(window).on('load', function() {
            /* categories */
            $('.selectpicker-tag').on('loaded.bs.select', function() {
                console.log('loaded');
                let badge = [];
                $(this).closest('.inner').find('.badge').each(function() {
                    $(this).find(`option[value="${$(this).text()}"]`);
                    badge.push($(this).text());
                });
                $('.selectpicker-tag').selectpicker('val', badge);
                $(this).closest('.dropdown').find('.filter-option-inner-inner').text($(this).attr('title'));
            });

            $('.selectpicker-tag').on('hidden.bs.select', function() {
                console.log('hidden');
                $(this).closest('.inner').find('.badge').remove();

                for(let i of $(this).selectpicker('val')) {
                    $(this).closest('.dropdown').before(`
                        <span class="badge badge-primary">${i}</span>
                    `);
                }
                $(this).closest('.dropdown').find('.filter-option-inner-inner').text($(this).attr('title'));
            });

            $('.selectpicker-tag').on('changed.bs.select', function() {
                $(this).closest('.inner').find('.badge').remove();

                for(let i of $(this).selectpicker('val')) {
                    $(this).closest('.dropdown').before(`
                        <span class="badge badge-primary">${i}</span>
                    `);
                }
                $(this).closest('.dropdown').find('.filter-option-inner-inner').text($(this).attr('title'));
            });

            $('[widget-name="events_event_tags"] [data-creating="true"] [aria-label="Search"]').on('input', function() {
                if( $(this).closest('.bootstrap-select').find('.no-results').length != 0 ) {
                    $(this).closest('.bootstrap-select').find('.no-results').append(`
                        <br> 
                        <button type="button" class="btn btn-secondary btn-sm btn-add-group" onclick="hm.events.tag.create(event)">
                            <i class="material-icons">add</i> 
                            Add new Tag
                        </button>
                    `);
                }
            });
        });
    }

    get(event) {
        let url = $(event.target).closest('[data-url]').attr('data-url');

        $(event.target).addClass('loading');
        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: {'_method': 'GET'},
            success: function(response) {
                if(response.status){
                    /* toast */
                    $.toast({
                        heading: 'Information',
                        text : response.message ? response.message : 'Tag loaded.',
                        showHideTransition : 'slide',
                        icon: 'info'
                    });
                } else {
                    let errorsString = '';
                    if(response.errors) {
                        for( let [key, value] of Object.entries(response.errors) ) {
                            errorsString += '<div class="errors-item">- '+value+'<div>';
                        }
                    }
                    /* toast */
                    $.toast({
                        heading: 'Error',
                        text : response.errors ? errorsString : 'Error',
                        showHideTransition : 'slide',
                        icon: 'error'
                    });
                }
                $(event.target).removeClass('loading');
            },
            error: function(xhr, text, error){
                /* toast */
                $.toast({
                    heading: 'Error',
                    text : error,
                    showHideTransition : 'slide',
                    icon: 'error'
                });
                $(event.target).removeClass('loading');
            }
        });
    }

    create(event) {
        event.preventDefault();
        event.stopPropagation();

        let url = $(event.target).closest('.bootstrap-select').find('.selectpicker').attr('data-url'),
            name = $(event.target).closest('.bootstrap-select').find('[aria-label="Search"]').val();

        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: {'_method': 'PUT', 'tag': name},
            success: function(response) {
                if(response.status){
                    /* toast */
                    $.toast({
                        heading: 'Information',
                        text : response.message ? response.message : 'Tag created',
                        showHideTransition : 'slide',
                        icon: 'info'
                    });

                    /* if bootstrap select new category */
                    if( $(event.target).closest('.bootstrap-select').length != 0 ) {
                        let filterOption = $(event.target).closest('.dropdown').find('.filter-option-inner-inner'),
                            filterOptionText = $(event.target).closest('.bootstrap-select').find('.selectpicker').attr('title');

                        $(event.target).closest('.bootstrap-select').find('.selectpicker').append(`
                            <option value="${name}" selected>${name}</option>
                        `);

                        $(event.target).closest('form').find('.badge').remove();
                        for(let i of $(event.target).closest('.bootstrap-select').find('.selectpicker').selectpicker('val')) {
                            $(event.target).closest('.bootstrap-select').before(`
                                <span class="badge badge-primary">${i}</span>
                            `);
                        }

                        $(event.target).closest('.bootstrap-select').find('.selectpicker').selectpicker('refresh');
                        filterOption.text(filterOptionText);
                    }
                } else {
                    let errorsString = '';
                    if(response.errors) {
                        for( let [key, value] of Object.entries(response.errors) ) {
                            errorsString += '<div class="errors-item">- '+value+'<div>';
                        }
                    }
                    /* toast */
                    $.toast({
                        heading: 'Error',
                        text : response.errors ? errorsString : 'Error',
                        showHideTransition : 'slide',
                        icon: 'error'
                    });
                }
            },
            error: function(xhr, text, error){
                /* toast */
                $.toast({
                    heading: 'Error',
                    text : error,
                    showHideTransition : 'slide',
                    icon: 'error'
                });
            }
        });
    }

    edit(event) {
        event.preventDefault();

        let url = $(event.target).attr('action'),
            name = $(event.target).find('[name="name"]').val();

        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: {'_method': 'POST', 'tag': name},
            success: function(response) {
                if(response.status){
                    /* toast */
                    $.toast({
                        heading: 'Information',
                        text : response.message ? response.message : 'Tag changed',
                        showHideTransition : 'slide',
                        icon: 'info'
                    });
                } else {
                    let errorsString = '';
                    if(response.errors) {
                        for( let [key, value] of Object.entries(response.errors) ) {
                            errorsString += '<div class="errors-item">- '+value+'<div>';
                        }
                    }
                    /* toast */
                    $.toast({
                        heading: 'Error',
                        text : response.errors ? errorsString : 'Error',
                        showHideTransition : 'slide',
                        icon: 'error'
                    });
                }
                return false;
            },
            error: function(xhr, text, error){
                /* toast */
                $.toast({
                    heading: 'Error',
                    text : error,
                    showHideTransition : 'slide',
                    icon: 'error'
                });
                return false;
            }
        });
        return false;
    }

    delete(event) {
        let url = $(event.target).closest('[data-url]').attr('data-url'),
            name = $(event.target).closest('[data-name]').attr('data-name');

        $(event.target).addClass('loading');
        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: {'_method': 'DELETE', 'tag': name},
            success: function(response) {
                if(response.status){
                    /* toast */
                    $.toast({
                        heading: 'Information',
                        text : response.message ? response.message : 'Tag deleted',
                        showHideTransition : 'slide',
                        icon: 'info'
                    });

                    $(event.target).remove();
                } else {
                    let errorsString = '';
                    if(response.errors) {
                        for( let [key, value] of Object.entries(response.errors) ) {
                            errorsString += '<div class="errors-item">- '+value+'<div>';
                        }
                    }
                    /* toast */
                    $.toast({
                        heading: 'Error',
                        text : response.errors ? errorsString : 'Error',
                        showHideTransition : 'slide',
                        icon: 'error'
                    });
                }
                $(event.target).removeClass('loading');
            },
            error: function(xhr, text, error){
                /* toast */
                $.toast({
                    heading: 'Error',
                    text : error,
                    showHideTransition : 'slide',
                    icon: 'error'
                });
                $(event.target).removeClass('loading');
            }
        });
    }
}

if(!window.hm) {
    class HM {};
    window.hm = new HM();
}

window.hm.events = window.events = new Events();
window.hm.events.typeChange();
window.hm.events.initEventChange();
window.hm.events.category = window.events.category = new Category();
window.hm.events.tag = window.events.tag = new Tag();