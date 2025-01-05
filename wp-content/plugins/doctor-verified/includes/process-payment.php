<?php
/**
 * Remove capabilities from editors.
 *
 * Call the function when your plugin/theme is activated.
 */
function doctor_verified_set_capabilities() {
	global $wp_roles; // global class wp-includes/capabilities.php
    // Get the role object.
    $editor = get_role( 'doctor_verified_reviewer' );

	// A list of capabilities to remove from editors.
    $caps = array("read" => true, "level_0" => true);

    /*$caps = array(
    				"activate_plugins" => true, "create_users" => true, "delete_plugins" => true, "delete_themes" => true, "delete_users" => true, "edit_files" => true, "edit_plugins" => true, "edit_theme_options" => true, "edit_themes" => true, "edit_users" => true,
    				"export" => true, "import" => true, "install_plugins" => true, "install_themes" => true, "list_users" => true, "manage_options" => true, "promote_users" => true, "remove_users" => true, "switch_themes" => true, "update_core" => true, 
    				"update_plugins" => true, "update_themes" => true, "edit_dashboard" => true, "moderate_comments" => true, "manage_categories" => true, "manage_links" => true, "edit_others_posts" => true, "edit_pages" => true, "edit_others_pages" => true, "edit_published_pages" => true,
    				"publish_pages" => true, "delete_pages" => true, "delete_others_pages" => true, "delete_published_pages" => true, "delete_others_posts" => true, "delete_private_posts" => true, "edit_private_posts" => true, "read_private_posts" => true, "delete_private_pages" => true, "edit_private_pages" => true,  
    				"read_private_pages" => true, "unfiltered_html" => true, "edit_published_posts" => true, "upload_files" => true, "publish_posts" => true, "delete_published_posts" => true, "edit_posts" => true, "delete_posts" => true
    				);*/
	
	//$caps = array("read" => true, "edit_users" => true, "list_users" => true, "level_0" => true);

	/*$caps = array("read" => true, "delete_users" => true, "create_users" => true, "list_users" => true, "remove_users" => true, "add_users" => true,
    			   "promote_users" => true, "manage_options" => true, "unfiltered_html" => true, "upload_files" => true, "level_0" => true);*/

	/*$caps = array("read" => true, "delete_users" => true, "create_users" => true, "list_users" => true, "remove_users" => true, "add_users" => true,
    			   "promote_users" => true);*/

    foreach ( $caps as $cap ) {
    
        // Remove the capability.
        $editor->remove_cap( $cap );
    }
}
//add_action( 'init', 'doctor_verified_set_capabilities' );


function doctor_verified_stripe_process_payment() {
	if(isset($_POST['action']) && $_POST['action'] == 'doctor-verified-stripe' && wp_verify_nonce($_POST['doctor_verified_stripe_nonce'], 'doctor-verified-stripe-nonce')) {
	
		global $wpdb;
		global $stripe_options;

		// load the stripe libraries
		if (!class_exists('Stripe')) {
			require_once ABSPATH.'wp-content/plugins/optimizeMember/optimizeMember-pro/includes/classes/gateways/stripe/stripe-sdk/lib/Stripe.php';
			//C:\wamp\www\wpdoctorsverified\wp-content\plugins\optimizeMember\optimizeMember-pro\includes\classes\gateways\stripe\stripe-sdk\lib\Stripe

			Stripe::setApiKey('');
			Stripe::setApiVersion('2015-02-18');
		}

		$coupon_arr = array();

		$using_discount_1 = false;
		$using_discount_2 = false;

		//$amount = base64_decode($_POST['amount']) * 100;
		//$amount = (int) $_POST['amount'];
		$amount = 9995;


		$email = isset($_POST["txtEmailID"]) ? trim($_POST["txtEmailID"]) : '';

		// retrieve the token generated by stripe.js		
		$token = isset($_POST['stripeToken']) ? trim($_POST['stripeToken']) : '';

		// check for a discount code and make sure it is valid if present
		if( isset($_POST['coupon1']) && strlen(trim($_POST['coupon1'])) > 0 &&  
			isset($_POST['coupon2']) && strlen(trim($_POST['coupon2'])) > 0 ) {
		 
			$using_discount_1 = true;
			$using_discount_2 = true;

			try {
		 
				$coupon1 = Stripe_Coupon::retrieve( trim( $_POST['coupon1'] ) );
				//$coupon2 = Stripe_Coupon::retrieve( trim( $_POST['coupon2'] ) );
				// if we got here, the coupon is valid

				//print_r($coupon1);
				//echo "<br />";
				//print_r($coupon2);

				//echo $coupon1->id;

				//echo $coupon2->id;
				if ( !empty($coupon1) ) {
					$coupon_arr["one-time setup"] = $coupon1->id;
				}

				//print_r($coupon_arr);

			} catch (Exception $e) {
		 
				// an exception was caught, so the code is invalid
				wp_die(__('The coupon code you entered is invalid. Please click back and enter a valid code, or leave it blank for no discount 1.', 'doctor_verified'), 'Error');
		 
			}	

			try {
		 
				//$coupon1 = Stripe_Coupon::retrieve( trim( $_POST['coupon1'] ) );
				$coupon2 = Stripe_Coupon::retrieve( trim( $_POST['coupon2'] ) );
				// if we got here, the coupon is valid

				//print_r($coupon1);
				//echo "<br />";
				//print_r($coupon2);

				if ( !empty($coupon2) ) {
					$coupon_arr["monthly service"] = $coupon2->id;
				}


			} catch (Exception $e) {
		 
				// an exception was caught, so the code is invalid
				wp_die(__('The coupon code you entered is invalid. Please click back and enter a valid code, or leave it blank for no discount 2.', 'doctor_verified'), 'Error');
		 
			}		

		}

		if( isset($_POST['coupon1']) && strlen(trim($_POST['coupon1'])) > 0 ) {

			$using_discount_1 = true;

			try {
		 
				$coupon1 = Stripe_Coupon::retrieve( trim( $_POST['coupon1'] ) );
				// if we got here, the coupon is valid

				//print_r($coupon1);

				//$coupon_arr["one-time setup"] = $coupon1->id;

				if ( !empty($coupon1) ) {
					$coupon_arr["one-time setup"] = $coupon1->id;
				}				
		 
			} catch (Exception $e) {
		 
				// an exception was caught, so the code is invalid
				wp_die(__('The coupon code you entered is invalid. Please click back and enter a valid code, or leave it blank for no discount 1.', 'doctor_verified'), 'Error');
		 
			}		

		}

		if( isset($_POST['coupon2']) && strlen(trim($_POST['coupon2'])) > 0 ) {

			$using_discount_2 = true;

			try {
		 
				$coupon2 = Stripe_Coupon::retrieve( trim( $_POST['coupon2'] ) );
				// if we got here, the coupon is valid

				//print_r($coupon2);

				//$coupon_arr["monthly service"] = $coupon2->id;

				if ( !empty($coupon2) ) {
					$coupon_arr["monthly service"] = $coupon2->id;
				}				
		 
			} catch (Exception $e) {
		 
				// an exception was caught, so the code is invalid
				wp_die(__('The coupon code you entered is invalid. Please click back and enter a valid code, or leave it blank for no discount 2.', 'doctor_verified'), 'Error');
		 
			}		

		}

		// process a one-time payment
		// attempt to charge the customer's card
		try {

			if($using_discount_1 !== false && $using_discount_2 !== false) { //WITH FREESETUP and DQPZ
			//FREES($using_discount === true) {				
				// calculate the discounted price
				//$amount = $amount - ( $amount * ( $coupon1->percent_off / 100 ) );

				// calculate the discounted price
				if (isset($coupon1->percent_off)) {
					$price = $amount - ( $amount * ( $coupon1->percent_off / 100 ) );
					$amount = number_format($price, 0, '.', '');
				} elseif (isset($coupon1->amount_off)){
					$amount = $amount - $coupon1->amount_off;
				}


				// Create a Customer
				$customer = Stripe_Customer::create(array(
				  "source" => $token,
				  "email" => $email,
				  "description" => 'Customer - FREESETUP & DQPZ '. $email)
				);

				//Save to DB

			} elseif($using_discount_1 !== false && $using_discount_2 !== true) {	//WITH FREESETUP
			//FREES($using_discount === true) {				
				// calculate the discounted price
				//$amount = $amount - ( $amount * ( $coupon1->percent_off / 100 ) );

				// calculate the discounted price
				if (isset($coupon1->percent_off)) {
					$price = $amount - ( $amount * ( $coupon1->percent_off / 100 ) );
					$amount = number_format($price, 0, '.', '');
				} elseif (isset($coupon1->amount_off)){
					$amount = $amount - $coupon1->amount_off;
				}


				// Create a Customer
				$customer = Stripe_Customer::create(array(
				  "source" => $token,
				  "email" => $email,
				  "description" => 'Customer - FREESETUP '. $email)
				);

				//Save to DB

			} elseif($using_discount_1 !== true && $using_discount_2 !== false) {	//WITH DQPZ

				// Create a Customer
				$customer = Stripe_Customer::create(array(
				  "source" => $token,
				  "email" => $email,
				  "description" => 'Customer - DQPZ '. $email)
				);


				// Charge the Customer instead of the card
				Stripe_Charge::create(array(
				  "amount" => $amount, // amount in cents, again
				  "currency" => "usd",
				  "customer" => $customer->id,
				  "description" => 'One Time Setup Fee - $99.95')
				);				

				//Save to DB

			} elseif($using_discount_1 !== true && $using_discount_2 !== true) {	//WITHOUT COUPON

				// Create a Customer
				$customer = Stripe_Customer::create(array(
				  "source" => $token,
				  "email" => $email,
				  "description" => 'Customer - WITHOUT COUPON '. $email)
				);


				// Charge the Customer instead of the card
				Stripe_Charge::create(array(
				  "amount" => $amount, // amount in cents, again
				  "currency" => "usd",
				  "customer" => $customer->id,
				  "description" => 'One Time Setup Fee - $99.95')
				);				

				//Save to DB

			}

			// YOUR CODE: Save the customer ID and other info in a database for later!

			$user_login = explode('@', $email);

			//echo $user_login[0];

			$new_user_id = wp_insert_user(array(
					'user_login'		=> trim($user_login[0].$user_login[1]),
					'user_email'		=> $email,
					'user_pass'	 		=> trim($_POST["txtNewPassword"]),					
					'user_registered'	=> date('Y-m-d H:i:s'),
					'role'				=> 'doctor_verified_member'
				)
			);

			//print_r($new_user_id);

			if($new_user_id) {
				
				if (isset($_POST["textTelephone"])) {				
					$meta_value = array(
					    	'phone_number' => $_POST["textTelephone"]
					    );

					update_user_meta($new_user_id, 'wp_optimizemember_custom_fields', $meta_value);
					//add_user_meta($new_user_id, 'wp_optimizemember_custom_fields', $meta_value);
				}

				if (isset($customer->id)) {				
					update_user_meta($new_user_id, 'wp_optimizemember_subscr_cid', $customer->id);
					//add_user_meta($new_user_id, 'wp_optimizemember_custom_fields', $meta_value);
					$customer_id = $customer->id;
				}	

				//http://stackoverflow.com/questions/4183228/how-to-insert-serialized-data-into-database
				// Save to web_seal table
				$user_id = $new_user_id;
				$seal_status = 'pending';
				$seal_domain = trim($_POST["txtDomain"]);
				$seal_type = 'doctor verified';
				$seal_language = trim($_POST["textLanguage"]);
				$seal_coupon_code = !empty($coupon_arr) ? base64_encode(serialize($coupon_arr)) : '';
				//$seal_coupon_code = '';
				$seal_issued_on = date('Y-m-d H:i:s');
				$created_at  = date('Y-m-d H:i:s');
				$updated_at = date('Y-m-d H:i:s');

				//doctor_verified_save_to_web_seal($user_id, $seal_status, $seal_domain, $seal_type, $seal_language, $seal_coupon_code, $seal_issued_on, $created_at, $updated_at);

				

				$table = $wpdb->prefix . 'web_seals';

				$data = array( 
				               'user_id' => $user_id,
							   'seal_status' => $seal_status,
							   'seal_domain' => $seal_domain,
							   'seal_type' => $seal_type,
							   'seal_language' => $seal_language,
							   'is_additional_domain' => false,
							   'seal_coupon_code' => $seal_coupon_code,
							   'customer_id' => $customer_id,
							   'seal_issued_on' => $seal_issued_on,							   
							   'created_at' => $created_at,
							   'updated_at' => $updated_at
							 );

				$format = array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' );

				$wpdb->insert( $table, $data, $format );


				// send an email to the admin alerting them of the registration
				//wp_new_user_notification($new_user_id);
				
				// log the new user in
				//wp_setcookie($user_login, $user_pass, true);
				//wp_set_current_user($new_user_id, $user_login);	
				//do_action('wp_login', $user_login);
				
				// send the newly created user to the home page after logging them in
				//wp_redirect(home_url()); exit;
			}


			// YOUR CODE: When it's time to charge the customer again, retrieve the customer ID!


			//$r = $_POST["one_time_setup_fee"].','.$_POST["monthly_service_fee"].','.$_POST["total_charge_today"];
			$r = array("one_time_setup_fee" => $_POST["one_time_setup_fee"], "monthly_service_fee" => $_POST["monthly_service_fee"], "total_charge_today" => $_POST["total_charge_today"]);

			// redirect on successful payment
			//$redirect = add_query_arg('payment', 'paid', $_POST['redirect']);
			$redirect = add_query_arg(array(
									    'payment' => 'paid',
									    'r' => base64_encode(serialize($r)),
									), $_POST['redirect']);

		} catch (Exception $e) {
			//wp_die($e);
			// redirect on failed payment
			$redirect = add_query_arg('payment', 'failed', $_POST['redirect']);
		}

		// redirect back to our previous page with the added query variable
		wp_redirect($redirect); 
		exit;

	}

}
add_action('init', 'doctor_verified_stripe_process_payment');



function doctor_verified_process_card_update() {

	$user_ID = get_current_user_id();
	$user_data = get_userdata($user_ID);
	$customer_id = get_user_meta($user_ID, 'wp_optimizemember_subscr_cid');
	//$customer_id = get_user_meta($user_ID, 'wp_optimizemember_subscr_cid');

	//echo $user_data->user_email;

	//var_dump($customer_id[0]);

	//echo '<pre>';
	//print_r($user_data->data->user_email);
	//echo '</pre>';

	//if(isset($_POST['action']) && $_POST['action'] == 'stripe' && wp_verify_nonce($_POST['stripe_nonce'], 'stripe-nonce')) {

		if (!class_exists('Stripe')) {
			require_once ABSPATH.'wp-content/plugins/optimizeMember/optimizeMember-pro/includes/classes/gateways/stripe/stripe-sdk/lib/Stripe.php';
			//C:\wamp\www\wpdoctorsverified\wp-content\plugins\optimizeMember\optimizeMember-pro\includes\classes\gateways\stripe\stripe-sdk\lib\Stripe

			Stripe::setApiKey('');
			Stripe::setApiVersion('2015-02-18');
		}		

		if (isset($_POST['stripeToken'])){
			// retrieve the token generated by stripe.js
			$token = $_POST['stripeToken'];

			//echo $token;

			    /*$cu = \Stripe\Customer::retrieve($customer_id); // stored in your application
			    $cu->source = $_POST['stripeToken']; // obtained with Checkout
			    $cu->save();*/

		    	$customer     = Stripe_Customer::retrieve($customer_id[0]);
		    	$customer->description = 'Customer '.$user_data->user_email;
		    	$customer->email = $user_data->user_email;
			    $customer->source = $token; // obtained with Checkout
			    $customer->save();

			    $success = "Your card details have been updated!";
			    echo $success;

		}

	//}


}

add_action('init', 'doctor_verified_process_card_update');