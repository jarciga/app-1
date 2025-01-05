/*Stripe.setPublishableKey(doctorVerifiedParams.publishable_key);
function stripeResponseHandler(status, response) {
    if (response.error) {
		// show errors returned by Stripe
        jQuery(".payment-errors").html(response.error.message);
		// re-enable the submit button
		jQuery('#stripe-submit').attr("disabled", false);
    } else {
        var form$ = jQuery("#stripe-payment-form");
        // token contains id, last4, and card type
        var token = response['id'];
        // insert the token into the form so it gets submitted to the server
        form$.append("<input type='hidden' name='stripeToken' value='" + token + "'/>");
        // and submit
        form$.get(0).submit();
    }
}
jQuery(document).ready(function($) {
	$("#stripe-payment-form").submit(function(event) {
		// disable the submit button to prevent repeated clicks
		$('#stripe-submit').attr("disabled", "disabled");
		
		// send the card details to Stripe
		Stripe.createToken({
			number: $('.card-number').val(),
			cvc: $('.card-cvc').val(),
			exp_month: $('.card-expiry-month').val(),
			exp_year: $('.card-expiry-year').val()
		}, stripeResponseHandler);

		// prevent the form from submitting with the default action
		return false;
	});
});*/

// BILLING INFORMATION

Stripe.setPublishableKey(doctorVerifiedParams.publishable_key);

jQuery(function($) {
  jQuery('[data-numeric]').payment('restrictNumeric');
  jQuery('.cc-number').payment('formatCardNumber');
  jQuery('.cc-exp').payment('formatCardExpiry');
  jQuery('.cc-cvc').payment('formatCardCVC');

  jQuery.fn.toggleInputError = function(erred) {
    this.parent('.form-group').toggleClass('has-error', erred);
    return this;
  };

  jQuery('#cccard-info-form-update').submit(function(e) {
  //jQuery('form').submit(function(e) {
    e.preventDefault();
    console.log('submit');
    var cardType = jQuery.payment.cardType(jQuery('.cc-number').val());
    var ccNumber = jQuery.payment.validateCardNumber(jQuery('.cc-number').val());
    var ccExpiryVal = jQuery.payment.validateCardExpiry(jQuery('.cc-exp').payment('cardExpiryVal'));
    var ccCVC = jQuery.payment.validateCardCVC(jQuery('.cc-cvc').val(), cardType);
    jQuery('.cc-brand').text(cardType);

      if ( ccNumber && ccExpiryVal && ccCVC ) {

        var ccExpiryMonth = jQuery('.cc-exp').payment('cardExpiryVal').month;
        var ccExpiryYear = jQuery('.cc-exp').payment('cardExpiryVal').year;

        console.log(ccExpiryMonth);
        console.log(ccExpiryYear);

        //console.log('Your card is valid!');
        jQuery('.validation').text('Your card is valid!');
        
          Stripe.createToken({
              number: jQuery('.cc-number').val(),
              cvc: jQuery('.cc-cvc').val(),
              exp_month: ccExpiryMonth,
              exp_year: ccExpiryYear
          }, stripeResponseHandler);
     
      } else {

        //console.log('Your card is not valid!');

        jQuery('.validation').removeClass('text-danger text-success');
        jQuery('.validation').addClass(jQuery('.has-error').length ? 'text-danger' : 'text-success');

        jQuery('.validation').text('Please enter valid test credit card information.');

         //$form.find('.validation').text('Please enter valid test credit card information.');
         //$form.find('.submit').prop('disabled', false);

      }

  return false; 
  });

});


function stripeResponseHandler(status, response) {
  if (response.error) { // Problem!

    // Show the errors on the form:
    jQuery('#cccard-info-form-update').find('.payment-errors').text(response.error.message);
    jQuery('#cccard-info-form-update').find('.submit').prop('disabled', false); // Re-enable submission

  } else { // Token was created!

  // Get the token ID:
    var token = response.id;

    // Insert the token ID into the form so it gets submitted to the server:
    jQuery('#cccard-info-form-update').append(jQuery('<input type="hidden" name="stripeToken">').val(token));

   //$result.html('Your Stripe token is: <strong>' + response.id + '</strong><br>This would then automatically be submitted to your server.');
   //jQuery('.validation').html('Your Stripe token is: <strong>' + response.id + '</strong><br>This would then automatically be submitted to your server.');

    // Submit the form:
    jQuery('#cccard-info-form-update').get(0).submit();
  }
}