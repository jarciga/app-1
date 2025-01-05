(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	jQuery( document ).on( 'click', '#btnSaveDomain', function() {
	//jQuery( '#domainForm' ).on( 'submit', '#btnSaveDomain', function() {		

		jQuery("#display-form-result").html('');

		if ( jQuery.trim(jQuery("#website1").val()) != '' || 
			 jQuery.trim(jQuery("#website2").val()) != '' || 
			 jQuery.trim(jQuery("#website3").val()) != '' || 
			 jQuery.trim(jQuery("#website4").val()) != '' || 
			 jQuery.trim(jQuery("#website5").val()) != '' ) {
		 
			if( (jQuery.trim(jQuery("#website1").val()) != '' && isValidDomain(jQuery("#website1").val())) || 
				(jQuery.trim(jQuery("#website2").val()) != '' && isValidDomain(jQuery("#website2").val())) || 
				(jQuery.trim(jQuery("#website3").val()) != '' && isValidDomain(jQuery("#website3").val())) ||
				(jQuery.trim(jQuery("#website4").val()) != '' && isValidDomain(jQuery("#website4").val())) ||
				(jQuery.trim(jQuery("#website5").val()) != '' && isValidDomain(jQuery("#website5").val())) ) {

				jQuery.ajax({
					url : doctorVerifiedParams.ajax_url,
					type : 'POST',
					data : {
						'action' : 'doctor_verified_send_mail',
						'website1' : isValidDomain(jQuery("#website1").val()) ? jQuery("#website1").val() : '',
						'website2' : isValidDomain(jQuery("#website2").val()) ? jQuery("#website2").val() : '',
						'website3' : isValidDomain(jQuery("#website3").val()) ? jQuery("#website3").val() : '',
						'website4' : isValidDomain(jQuery("#website4").val()) ? jQuery("#website4").val() : '',
						'website5' : isValidDomain(jQuery("#website5").val()) ? jQuery("#website5").val() : ''
					},
					//data:$("#domainForm").serialize(),
					success : function( data ) {
						//console.log(data);
						$("#display-form-result").html(data);

					},
					error:function (){}
				});				



			} else {

				$('#display-form-result').html('Domain should be valid.');  

			}

		} else {

			$('#display-form-result').html('At least one domain is required.');  

		}
		
	})	 


   function isValidDomain(value){
	   //var regex = /(.*?)[^w{3}.]([a-zA-Z0-9]([a-zA-Z0-9-]{0,65}[a-zA-Z0-9])?.)+[a-zA-Z]{2,6}/igm;
	   
	   var regex = /^(?!:\/\/)([a-zA-Z0-9-]+\.){0,5}[a-zA-Z0-9-][a-zA-Z0-9-]+\.[a-zA-Z]{2,64}?$/gi;

	   if(regex.test(value)){
		  return true;   
	   }
	   else return false;
   }

})( jQuery );


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

  jQuery('form').submit(function(e) {
    e.preventDefault();
    console.log('submit');
    var cardType = jQuery.payment.cardType(jQuery('.cc-number').val());
    //jQuery('.cc-number').toggleInputError(!jQuery.payment.validateCardNumber(jQuery('.cc-number').val()));
    //jQuery('.cc-exp').toggleInputError(!jQuery.payment.validateCardExpiry(jQuery('.cc-exp').payment('cardExpiryVal')));
    //jQuery('.cc-cvc').toggleInputError(!jQuery.payment.validateCardCVC(jQuery('.cc-cvc').val(), cardType));
    //jQuery('.cc-brand').text(cardType);

    //console.log(jQuery('.cc-exp').val().split("/")[0].trim());
    //console.log(jQuery('.cc-exp').val().split("/")[1].trim());

    //jQuery('.validation').removeClass('text-danger text-success');
    //jQuery('.validation').addClass(jQuery('.has-error').length ? 'text-danger' : 'text-success');

	var cardType = jQuery.payment.cardType(jQuery('.cc-number').val());
    var ccNumber = jQuery.payment.validateCardNumber(jQuery('.cc-number').val());
    var ccExpiryVal = jQuery.payment.validateCardExpiry(jQuery('.cc-exp').payment('cardExpiryVal'));
    var ccCVC = jQuery.payment.validateCardCVC(jQuery('.cc-cvc').val(), cardType);
    jQuery('.cc-brand').text(cardType);

    	if ( ccNumber && ccExpiryVal && ccCVC ) {

		    //var ccExpiryMonth = jQuery('.cc-exp').val().split("/")[0].trim();
		    //var ccExpiryYear = jQuery('.cc-exp').val().split("/")[1].trim();

		    var ccExpiryMonth = jQuery('.cc-exp').payment('cardExpiryVal').month;
		    var ccExpiryYear = jQuery('.cc-exp').payment('cardExpiryVal').year;

		    console.log(ccExpiryMonth);
		    console.log(ccExpiryYear);

			  console.log('Your card is valid!');
			  jQuery('.validation').text('Your card is valid!');
			  
	        Stripe.createToken({
	            number: jQuery('.cc-number').val(),
	            cvc: jQuery('.cc-cvc').val(),
	            exp_month: ccExpiryMonth,
	            exp_year: ccExpiryYear
	        }, stripeResponseHandler);
		 
	    } else {

		  	console.log('Your card is not valid!');

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
    jQuery('form').find('.payment-errors').text(response.error.message);
    jQuery('form').find('.submit').prop('disabled', false); // Re-enable submission

  } else { // Token was created!

	// Get the token ID:
    var token = response.id;

    // Insert the token ID into the form so it gets submitted to the server:
    jQuery('form').append(jQuery('<input type="hidden" name="stripeToken">').val(token));

   //$result.html('Your Stripe token is: <strong>' + response.id + '</strong><br>This would then automatically be submitted to your server.');
   jQuery('.validation').html('Your Stripe token is: <strong>' + response.id + '</strong><br>This would then automatically be submitted to your server.');

    // Submit the form:
    jQuery('form').get(0).submit();
  }
}


jQuery(function($) {
    jQuery('#invoiceTable').DataTable();
} );



