<?php

function pippin_stripe_process_payment() {
	if(isset($_POST['action']) && $_POST['action'] == 'stripe' && wp_verify_nonce($_POST['stripe_nonce'], 'stripe-nonce')) {
		
		global $stripe_options;
		
		// load the stripe libraries
		require_once(DOCTOR_VERIFIED_BASE_DIR . '/lib/Stripe.php');
		
		// retrieve the token generated by stripe.js
		$token = $_POST['stripeToken'];

		// check if we are using test mode
		if(isset($stripe_options['test_mode']) && $stripe_options['test_mode']) {
			$secret_key = $stripe_options['test_secret_key'];
		} else {
			$secret_key = $stripe_options['live_secret_key'];
		}
		
		// attempt to charge the customer's card
		try {
			Stripe::setApiKey($secret_key);
			$charge = Stripe_Charge::create(array(
					'amount' => 1000, // $10
					'currency' => 'usd',
					'card' => $token
				)
			);
				
				
			// redirect on successful payment
			$redirect = add_query_arg('payment', 'paid', $_POST['redirect']);
			
		} catch (Exception $e) {
			// redirect on failed payment
			$redirect = add_query_arg('payment', 'failed', $_POST['redirect']);
		}
		
		// redirect back to our previous page with the added query variable
		wp_redirect($redirect); exit;
	}
}
add_action('init', 'pippin_stripe_process_payment');