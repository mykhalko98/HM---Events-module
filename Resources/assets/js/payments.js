class Payment {
    initialize(stripeKey) {
        this.stripe = Stripe(stripeKey);
        this.generateElements();
        this.inputCountField();
    }

    inputCountField() {
        $('#payment-form').find('[name="count"]').on('input', function(event) {
            var amount_to_pay = $('#payment-form').find('[name="amount"]').val(),
                count = parseInt($(this).val());
            if (parseInt($(this).val()) > parseInt($(this).attr('max'))) {
                count = parseInt($(this).attr('max'));
                $(this).val(count);
            } else if(parseInt($(this).val()) < parseInt($(this).attr('min'))) {
                count = parseInt($(this).attr('min'));
                $(this).val(count);
            }
            if (count > 1) {
                $('#payment-form').find('#amount-to-pay').text(((amount_to_pay*count)/100).toFixed(2));
            } else {
                $('#payment-form').find('#amount-to-pay').text((amount_to_pay/100).toFixed(2));
            }
        });
    }

    generateElements() {// Create an instance of Elements.w
        var elements = this.stripe.elements();

        // Custom styling can be passed to options when creating an Element.
        var style = {
            base: {
                // Add your base input styles here. For example:
                fontSize: '16px',
                background: '#f8f8f8',
                color: '#32325d',
                lineHeight: '18px',
                fontFamily: '"Helvetica Neue", Helvetica, sans-self',
                fontSmoothing: 'antialiased',
                '::placeholder': {
                    color: '#495057'
                }
            },
            invalid: {
                color: '#fa755a',
                iconColor: '#fa755a'
            }
        };

        // Create an instance of the card Element.
        var card = elements.create('card', {
            style: style,
            hidePostalCode : true
        });

        // Add an instance of the card Element into the `card-element` <div>.
        card.mount('#card-element');

        var count  = parseInt($('#payment-form input[name="count"]').val()),
            amount = parseInt($('#payment-form input[name="amount"]').val());
        if (count > 1) {
            amount = amount * count;
        }
        var paymentRequest = hm.events_payment.stripe.paymentRequest({
            country: 'US',
            currency: $('body').attr('currency') ? $('body').attr('currency') : 'usd',
            total: {
                label: 'Demo total',
                amount: amount,
            },
            requestPayerName: true,
            requestPayerEmail: true,
        });
        paymentRequest.on('paymentmethod', function(ev) {
            // Confirm the PaymentIntent without handling potential next actions (yet).
            hm.events_payment.sendRequest({
                'ticket': $('#payment-form input[name="ticket"]').val(),
                'email': ev.payerEmail,
                'name': ev.payerName,
                'count': count,
                'amount': amount,
            }, {
                payment_method: ev.paymentMethod.id
            }, ev);
        });

        var prButton = elements.create('paymentRequestButton', {
            paymentRequest: paymentRequest,
        });

        // Check the availability of the Payment Request API first.
        paymentRequest.canMakePayment().then(function(result) {
            if (result) {
                prButton.mount('#payment-request-button');
            } else {
                document.getElementById('payment-request-button').style.display = 'none';
            }
        });

        card.on("change", function (event) {
            // Disable the Pay button if there are no card details in the Element
            document.querySelector("#payment-button").disabled = event.empty;
            document.querySelector("#card-errors").textContent = event.error ? event.error.message : "";
        });

        // Create a token or display an error when the form is submitted.
        $('#payment-form').on('submit', function(event) {
            event.preventDefault();

            var count  = parseInt($('#payment-form input[name="count"]').val()),
                amount = parseInt($('#payment-form input[name="amount"]').val());
            if (count > 1) {
                amount = amount * count;
            }
            console.log(count);

            $('#payment-button').addClass('loading');
            var data = {
                'ticket': $('#payment-form input[name="ticket"]').val(),
                'email': $('#payment-form input[name="email"]').val(),
                'name': $('#payment-form input[name="name"]').val(),
                'count': count,
                'amount': amount,
            };
            var confirmPaymentData = {
                payment_method: {
                    card: card,
                    billing_details: {
                        email: data.email,
                        name: data.name,
                    },
                }
            }

            // Submit the form
            hm.events_payment.sendRequest(data, confirmPaymentData);
        });
    }

    sendRequest(data, confirmPaymentData, event = null) {
        let formData = Object.assign({
            '_method': 'POST',
            '_token': $('meta[name=csrf-token]').attr('content')
        }, data);
        $.ajax({
            url: $('#payment-form').attr('action'),
            type: 'POST',
            dataType: "JSON",
            data: formData,
            success: function(response) {
                if(response.status) {
                    hm.events_payment.stripe.confirmCardPayment(response.clientSecret, confirmPaymentData).then(function (result) {
                        if (result.error) {
                            event && event.complete('fail');
                            $('#payment-button').removeClass('loading');
                            // Show error to your customer
                            $.toast({
                                heading: 'Error',
                                text: result.error.message ? result.error.message : 'Error payment',
                                showHideTransition: 'slide',
                                icon: 'error'
                            });
                        } else if(result.paymentIntent && result.paymentIntent.status === 'succeeded') {
                            event && event.complete('success');
                            $('#payment-button').removeClass('loading');
                            if (response.redirect) {
                                window.location = response.redirect;
                            } else if(response.reload) {
                                $('[role="dialog"]').modal('hide');
                                location.reload();
                            } else {
                                $('[role="dialog"]').modal('hide');
                                $.toast({
                                    heading: 'Information',
                                    text: 'Payment is registered',
                                    showHideTransition: 'slide',
                                    icon: 'info'
                                });
                            }
                        } else {
                            console.log(result);
                            $.toast({
                                heading: 'Information',
                                text: 'Something is wrong, please try again.',
                                showHideTransition: 'slide',
                                icon: 'info'
                            });
                        }
                    });
                } else if(response.status == false) {
                    $('#payment-button').removeClass('loading');

                    $.toast({
                        heading: 'Error',
                        text: response.errors ? response.errors : 'Error',
                        showHideTransition: 'slide',
                        icon: 'error'
                    });

                    if (response.reload) {
                        setTimeout(function() {
                            location.reload();
                        }, 3000);
                    }
                }
            },
            error: function(xhr, status, error) {
                $('#payment-button').removeClass('loading');

                $.toast({
                    heading: 'Error',
                    text: 'Sorry server error:' + error,
                    showHideTransition: 'slide',
                    icon: 'error'
                });
            }
        });
    }

    refundRequest(event, element) {
        event.preventDefault();
        var url = element.attr('action'),
            description = element.find('[name="description"]').val(),
            button = element.find('[type="submit"]');

        button.addClass('loading');
        button.addClass('disabled');
        $.ajax({
            url: url,
            method: 'POST',
            dataType: 'json',
            data: {'_method': 'POST', 'description': description},
            success: function(response) {
                if (response.status) {
                    button.removeClass('loading');
                    button.removeClass('disabled');
                    $('[role="dialog"]').modal('hide');
                    $.toast({
                        heading: 'Information',
                        text: response.message ? response.message : 'Refund request successfully sent',
                        showHideTransition: 'slide',
                        icon: 'info'
                    });
                    if (response.reload) {
                        setTimeout(function() {
                            location.reload()
                        }, 2000);
                    }
                } else if (response.status == false) {
                    button.removeClass('loading');
                    button.removeClass('disabled');
                    if (response.reload) {
                        location.reload();
                    }
                    $.toast({
                        heading: 'Error',
                        text: response.errors ? response.errors : 'Error',
                        showHideTransition: 'slide',
                        icon: 'error'
                    });
                }
            },
            error: function(xhr, text, error){
                button.removeClass('loading');
                button.removeClass('disabled');
                $.toast({
                    heading: 'Error',
                    text : error,
                    showHideTransition : 'slide',
                    icon: 'error'
                });
            }
        });
    }

    refund(event, element) {
        event.preventDefault();
        var url = element.attr('data-url');

        element.addClass('loading');
        $.ajax({
            url: url,
            method: 'POST',
            dataType: 'json',
            data: {'_method': 'POST'},
            success: function(response) {
                if (response.status) {
                    element.removeClass('loading');
                    $('[role="dialog"]').modal('hide');
                    $.toast({
                        heading: 'Information',
                        text: response.message ? response.message : 'Refund was created',
                        showHideTransition: 'slide',
                        icon: 'info'
                    });
                    if (response.reload) {
                        location.reload();
                    } else if (response.redirect) {
                        setTimeout(function () {
                            window.location = response.redirect;
                        }, 3000);
                    }
                } else if (response.status == false) {
                    element.removeClass('loading');
                    $.toast({
                        heading: 'Error',
                        text: response.errors ? response.errors : 'Error',
                        showHideTransition: 'slide',
                        icon: 'error'
                    });
                    if (response.reload) {
                        location.reload();
                    } else if (response.redirect) {
                        setTimeout(function () {
                            window.location = response.redirect;
                        }, 3000);
                    }
                }
            },
            error: function(xhr, text, error){
                $(element).removeClass('loading');
                $.toast({
                    heading: 'Error',
                    text : error,
                    showHideTransition : 'slide',
                    icon: 'error'
                });
            }
        });
    }
}

if(!window.hm) {
    class HM {};
    window.hm = new HM();
}

window.events_payment = hm.events_payment = new Payment();