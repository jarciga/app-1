<?php
function doctor_verified_load_stripe_scripts() {
	
	global $stripe_options;

	//$user_ID = get_current_user_id();
	//$user_data = get_userdata($user_ID);

	//$email = isset($_POST["txtEmailID"]) ? $_POST["txtEmailID"] : '';
	
	// check to see if we are in test mode
	if(isset($stripe_options['test_mode']) && $stripe_options['test_mode']) {
		$publishable = $stripe_options['test_publishable_key'];
	} else {
		$publishable = $stripe_options['live_publishable_key'];
	}


	//Javascript

	wp_enqueue_script('jquery');
	wp_enqueue_script('doctor-verified-datatables-js', '//cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js');
	wp_enqueue_script('doctor-verified-steps-js', DOCTOR_VERIFIED_BASE_URL . 'includes/js/jquery.steps.min.js');

	
	wp_enqueue_script('doctor-verified-validation-engine-js', DOCTOR_VERIFIED_BASE_URL . 'includes/js/jquery.validationEngine.js');
	wp_enqueue_script('doctor-verified-validate-js', DOCTOR_VERIFIED_BASE_URL . 'includes/js/jquery.validate.min.js');
	wp_enqueue_script('doctor-verified-payment-js', DOCTOR_VERIFIED_BASE_URL . 'includes/js/jquery.payment.min.js');
	wp_enqueue_script('doctor-verified-scripts-js', DOCTOR_VERIFIED_BASE_URL . 'includes/js/scripts.js', array( 'jquery' ), '', false);

	wp_localize_script('doctor-verified-scripts-js', 'doctorVerifiedParams', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'publishable_key' => 'pk_test_zblT5LXTY4nnFHoLeqqWtHiI', 'one_time_setup' => 'freesetup', 'monthly_service' => 'dqpz' ) );	

	wp_enqueue_script('stripe', 'https://js.stripe.com/v2/');
	wp_enqueue_script('checkout', 'https://checkout.stripe.com/checkout.js');
	wp_enqueue_script('doctor-verified-stripe-processing-js', DOCTOR_VERIFIED_BASE_URL . 'includes/js/stripe-processing.js');
	wp_localize_script('doctor-verified-stripe-processing-js', 'stripe_vars', array(
			'publishable_key' => $publishable,
		)
	);

}
add_action('wp_enqueue_scripts', 'doctor_verified_load_stripe_scripts');


function doctor_verified_load_stripe_styles() {
	//CSS

	wp_enqueue_style('doctor-verified-datatables', '//cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css');	
	wp_enqueue_style('doctor-verified-validation-engine', DOCTOR_VERIFIED_BASE_URL . 'includes/css/jquery.steps.css');
	wp_enqueue_style('doctor-verified-validation-engine', DOCTOR_VERIFIED_BASE_URL . 'includes/css/validationEngine.jquery.css');
	wp_enqueue_style('doctor-verified-style', DOCTOR_VERIFIED_BASE_URL . 'includes/css/style.css');
}

add_action('wp_enqueue_scripts', 'doctor_verified_load_stripe_styles', 11);


function doctor_verified_web_seal_enqueue_scripts() {

	//$user_ID = get_current_user_id();

	//wp_enqueue_script( 'doctor-verified-web-seal-script', plugins_url( '/public/js/doctor-verified-web-seal.js' , __FILE__), array( 'jquery' ), '', false );
	wp_register_script( 'doctor-verified-web-seal-script', DOCTOR_VERIFIED_BASE_URL . 'includes/js/doctor-verified-web-seal.js', array( 'jquery' ), '', false );
    wp_enqueue_script( 'doctor-verified-web-seal-script');
	/*$web_seal_params = array(
	    'master_id' => '10_6', //3865_641
	    'div_class_name' => 'web_seal' //web_seal
	    );
	wp_localize_script( 'doctor-verified-web-seal-script', 'WebSealParam', $web_seal_params );*/

}

add_action( 'wp_enqueue_scripts', 'doctor_verified_web_seal_enqueue_scripts' );


//Admin

function doctor_verified_admin_load_stripe_enqueue($hook) {
    if ( 'profile.php' != $hook && 'user-edit.php' != $hook ) {
        return;
    }

	//if ( current_user_can('administrator') || current_user_can('doctor_verified_reviewer') ) {
    	wp_enqueue_script('admin-media-uploader-js', DOCTOR_VERIFIED_BASE_URL . 'includes/js/media-uploader-3.5.js');
    	wp_enqueue_media();
    //}

}
add_action( 'admin_enqueue_scripts', 'doctor_verified_admin_load_stripe_enqueue' );