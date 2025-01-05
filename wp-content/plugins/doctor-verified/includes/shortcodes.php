<?php
function doctor_verified_registration_form() {
	ob_start();	
	if(isset($_GET['payment']) && $_GET['payment'] == 'paid') { ?>

		<?php 

			//echo $_POST["amount"]; 
			//var_dump(unserialize(base64_decode($_GET['r'])));
			//extract(unserialize(base64_decode($_GET['r'])));

			$r_arr = unserialize(base64_decode($_GET['r']));  
		?>

		<h3>Step 3</h3>
		<fieldset>
		    <legend>Step 3</legend>

		    <p class="application-thanks-page-text"> Thanks for applying for a Doctor Certified&trade; seal. 
		    You will receive a confirmation receipt shortly via email.</p><br>
		    <div class="receipt-box">
		    <p class="receipt-header">Order Summary </p> <br>
		    <div class="receipt-tables-container">
		    <div class="receipt-top-price-background">
		        <table class="pricing-table">
		            <tr>
			            <td class="pricing-td-1">
			            	<p class="receipt-setup-fee"> One-Time Setup Fee</p>
		            	</td>
			            <td>
				            <p class="receipt-pricing-numbers">
							<?php echo $r_arr["one_time_setup_fee"]; ?> </p>
			            </td>
		            </tr>
		        </table>
		    <hr class="receipt-total-hr">
		    </div>
		    <div class="receipt-total-price-background">
		    <table class="pricing-table">
		        <tr>
		            <td class="pricing-td-1">
			            <p class="receipt-total-charge"> Total Charge Today </p>
			            </td>
		            <td>
			            <p class="receipt-total-charge-numbers">
						<?php echo $r_arr["total_charge_today"]; ?></p>
		            </td>
		        </tr>
		    </table>
		    </div>
		    </div>
		    <br>
		    <p class="receipt-text"><b>A monthly service fee of <?php echo $r_arr["monthly_service_fee"]; ?> </b> will be charged to your account every month.<br><br>
		    The first charge will occur once your application has been approved. </p>
		    </div>
		    <br><br>
		    <p class="application-thanks-page-text"> It typically takes 2 - 4 days for the doctor to review your website. Once your website passes the review process we will send you a confirmation email with instructions on how to upload your Doctor Certified&trade; seal and certificate to your website. </p><br>
		    <p class="application-thanks-page-text"> You will also be able to access your seal and change your billing information by logging in to our <a href="#" class="faq-link" > website</a>. </p><br>
		    <p class="application-thanks-page-text"> If your website does not pass the initial review we will work with you to help get your website accepted, at no extra charge. </p>
		    <br><br>
		</fieldset>

<?php 
	} else {

?>
	<form id="applyform" action="" method="POST">
		<div id="message"></div>
	    <h3>Step 1</h3>
	    <fieldset>
	        <legend>Step 1</legend>
			<p>(*) Required</p>    

			<label class="label-style"><b>Domain Name *</b></label><br>
			<input type="text" name="txtDomain" id="txtDomain" maxlength="500" class="input-style" value="" placeholder="example.com" />
			<p class="password-reminder">Enter the domain name where you would like the seal to be displayed.</p>

			<label class="label-style"><b>Enter your Email *</b></label> <br>
			<input type="text" name="txtEmailID" id="txtEmailID"   maxlength="500" class=" input-style" value="" >
			<p class="password-reminder">This will be used as your username as well as for correspondance. </p>	

			<label class="label-style"><b>Choose a Password *</b></label><br> 
			<input type="password" name="txtNewPassword" id="txtNewPassword"  maxlength="25" class="input-style" value=""></input>
			<p class="password-reminder"> Password must be atleast six characters. </p>

			<label class="label-style"><b>Language *</b></label><br> 
			<select name="textLanguage" id="textLanguage" class="input-style">
				<option value="en" selected="selected">english (en)</option>
				<option value="ary">cestina "cs_CZ"</option>
				<option value="ar">danish (da_DK)</option>
				<option value="az">german (de_DE)</option>
				<option value="azb">spanish (es)</option>
				<option value="bg_BG">french (fr)</option>
				<option value="bn_BD">croatian/hrvatski (hr)</option>
				<option value="bs_BA">hungarian (hu_HU)</option>
				<option value="ca">italian (it_IT)</option>
				<option value="ceb">japan (jp)</option>
				<option value="cs_CZ">norweigian (no)</option>
				<option value="cy">netherlands / dutch (nl_NL)</option>
				<option value="da_DK">portugese (pt_PT)</option>
				<option value="de_CH">slovak (sk_SK)</option>
				<option value="de_CH_informal">slovenian (sl_SI)</option>
				<option value="de_DE">swedish (sv_SE)</option>
				<option value="de_DE_formal">turkish (tr_TR)</option>
				<option value="el">chinese (zh_CN)</option>				
				</select>			
			<br><br> 

			<label class="label-style-phone"><b>Phone Number </b> <span class="text-optional">(Optional)</span></label><br> 
			<input type="text" name="textTelephone" id="textTelephone"  size="26" maxlength="25" class="input-style-phone" value="" ></input>
			<p class="phone-number-reminder"> If we need to contact you about your application. </p>

	    </fieldset>
	 
	    <h3>Step 2</h3>
	    <fieldset>
	        <legend>Step 2</legend>
			<p>(*) Required</p>  	 

			<div class="pricing-background">
				<table class="pricing-table">
				<tr>
					<td class="pricing-td-1">
					<p class="individual-fees1"> One-Time Setup Fee</p>
					</td>
					<td>
					<p class="pricing-numbers" id="setup_price">$99.95 </p>
					</td>
				</tr>
				<tr>
					<td class="pricing-td-1">
					<p class="individual-fees"> Monthly Service Fee </p>
					</td>
					<td>
					<p class="pricing-numbers" id="monthly_cost"><span>$49.95</span> <span class="asterisk">* </span> </p>
					</td>
				</tr>
				</table>
				<hr class="total-hr">
				</div>
				<div class="total-charge-background">
				<table class="pricing-table">
				<tr>
					<td class="pricing-td-1">
					<p class="total-charge"> Total Charge Today </p>
					</td>
					<td>
					<p class="total-charge-numbers" id="total_setup_cost">$99.95</p>
					</td>
				</tr>
				</table>
			</div>
			<br>
			<p class="application2-text"><span class="asterisk">* </span>Your first monthly charge will be billed once your certificate is issued.<br>
			&nbsp;&nbsp;&nbsp;&nbsp;You may cancel at any time. </p> 

			<!--input type="hidden" name="amount" id="amount" value="99.95"-->
			<input type="hidden" name="one_time_setup_fee" id="one_time_setup_fee" value="">
			<input type="hidden" name="monthly_service_fee" id="monthly_service_fee" value="">
			<input type="hidden" name="total_charge_today" id="total_charge_today" value="">

			<br>
			<input name="save" id="btnSave" type="button" value="Continue"  class="continue-button btn-70-l open2"  style="display: none" />               
			
			<div id="stripe_script_container" style="float:left;display:"> 
				<!--script src="/web/20160430033815js_/https://checkout.stripe.com/checkout.js" class="stripe-button" 
				data-key="pk_live_V6PISHjBPsBnhRRuM2KQGCUv" 
				data-amount="9995" data-name="Doctor Certified&trade;" 
				data-description="One Time Setup Fee - $99.95 " data-email="" 
				data-image="images/doc-stripe.png" 
				data-allow-remember-me="false">
				</script-->
				<button id="customButton" class="stripe-custom-button"><span style="display: block; min-height: 30px;">Purchase</span></button>
			</div> 

			<br clear="all">
			<br>   
			<div class="do-you-have-a-coupon">
				<label class="label-style"><b>Do you have a Coupon?</b><!--<input type="checkbox" name="coupon" id="coupon" value="1">--></label>
				Yes<input type="radio" value="1" name="coupon" id="yes_coupon">&nbsp; 
				No<input type="radio" value="1" name="coupon" id="no_coupon"> 

				<div style="display:none" id="coupon_block">
					<label class="label-style"><b>Enter your Coupon</b></label> <br>
					<input type="text" name="coupon1" id="coupon1"  class=" input-style"  placeholder="One-Time Setup"><br><br>
					<input type="text" name="coupon2" id="coupon2"  class=" input-style"  placeholder="Monthly Service"><br>
					<button class="btn btn-warning continue-button btn-70-l" id="check_coupon_validity" type="button">Check Coupon Code Validity</button>
					<!--button class="btn btn-warning  continue-button btn-70-l" id="apply_coupon" type="button"> Apply</button--> 
				</div>
			</div>

	    </fieldset>
	 
	    <!--h3>Step 3</h3>
	    <fieldset>
	        <legend>Step 3</legend>

            <p class="application-thanks-page-text"> Thanks for applying for a Doctor Certified&trade; seal. 
            You will receive a confirmation receipt shortly via email.</p><br>
            <div class="receipt-box">
            <p class="receipt-header">Order Summary </p> <br>
            <div class="receipt-tables-container">
            <div class="receipt-top-price-background">
	            <table class="pricing-table">
		            <tr>
			            <td class="pricing-td-1">
			            	<p class="receipt-setup-fee"> One-Time Setup Fee</p>
		            	</td>
			            <td>
				            <p class="receipt-pricing-numbers">
							99.95 </p>
			            </td>
		            </tr>
	            </table>
            <hr class="receipt-total-hr">
            </div>
            <div class="receipt-total-price-background">
            <table class="pricing-table">
	            <tr>
		            <td class="pricing-td-1">
			            <p class="receipt-total-charge"> Total Charge Today </p>
			            </td>
		            <td>
			            <p class="receipt-total-charge-numbers">
						99.95</p>
		            </td>
	            </tr>
            </table>
            </div>
            </div>
            <br>
            <p class="receipt-text"><b>A monthly service fee of $49.95 </b> will be charged to your account every month.<br><br>
            The first charge will occur once your application has been approved. </p>
            </div>
            <br><br>
            <p class="application-thanks-page-text"> It typically takes 2 - 4 days for the doctor to review your website. Once your website passes the review process we will send you a confirmation email with instructions on how to upload your Doctor Certified&trade; seal and certificate to your website. </p><br>
            <p class="application-thanks-page-text"> You will also be able to access your seal and change your billing information by logging in to our <a href="/web/20160430033815/https://www.doctor-certified.com/login.php" class="faq-link" > website</a>. </p><br>
            <p class="application-thanks-page-text"> If your website does not pass the initial review we will work with you to help get your website accepted, at no extra charge. </p>
            <br><br>
	    </fieldset-->

	    <input type="hidden" name="action" value="doctor-verified-stripe"/>
		<input type="hidden" name="redirect" value="<?php echo get_permalink(); ?>"/>
		<input type="hidden" name="doctor_verified_stripe_nonce" value="<?php echo wp_create_nonce('doctor-verified-stripe-nonce'); ?>"/>

	</form>

 
<?php
	}
	return ob_get_clean();
}
add_shortcode('doctor_verified_registration_form', 'doctor_verified_registration_form');



// YOUR WEB SEAL
function doctor_verified_web_seal_html_table() {

	$plugin_img_url = plugin_dir_url( __FILE__ ).'img/';
	$user_ID = get_current_user_id();
	$user_data = get_userdata($user_ID);
	$user_meta = get_user_meta($user_ID, 'wp_optimizemember_custom_fields');

	//$meta_arr['user_domain_0'] = ( isset($meta_arr['user_domain_0']) ? $meta_arr['user_domain_0'] : '' ); 

	//$check_web_seal = doctor_verified_check_domain($meta_arr['user_domain_0']);


	//echo 'There is a record in the database table (web_seals)';
	$web_seals = doctor_verified_web_seal_html_table_repeater_result();

	$output = '';

	//if( !current_user_can('administrator') && current_user_can('access_optimizemember_level1')) {



		$output = '<table width="100%" border="1">';
		$output .= '<tr style="color:#ffffff; background-color:#7296da; font-weight:bold; font-size:16px;">';
		$output .= '<th scope="col" style="padding-top:5px; padding-bottom:5px;">Status</th>';
		$output .= '<th scope="col" style="padding-top:5px; padding-bottom:5px;">Domain</th>';
		$output .= '<th scope="col" style="padding-top:5px; padding-bottom:5px;">Issued On</th>';
		$output .= '<th scope="col" style="padding-top:5px; padding-bottom:5px;">Seal Type</th>';
		$output .= '<th scope="col" style="padding-top:5px; padding-bottom:5px;">Code</th>';
		$output .= '<th scope="col" style="padding-top:5px; padding-bottom:5px;">Seal</th>';
		$output .= '</tr>';		



		if( is_array( $web_seals ) && count( $web_seals ) > 0 ) {
			foreach($web_seals as $web_seal) {
				
				$output .= '<tr>';
				$output .= '<td align="center" valign="top" style="padding-top:5px; padding-bottom:5px;">'. ucwords($web_seal->seal_status). '</td>';
				$output .= '<td align="center" valign="top" style="padding-top:5px; padding-bottom:5px;">'. $web_seal->seal_domain. '</td>';
				$output .= '<td align="center" valign="top" style="padding-top:5px; padding-bottom:5px;">'. date('m-d-Y', strtotime($web_seal->seal_issued_on)). '</td>';  	
				$output .= '<td align="center" valign="top" style="padding-top:5px; padding-bottom:5px;">'. ucwords($web_seal->seal_type) . '</td>';

				//$output .= '<td align="center" valign="top" style="padding-top:5px; padding-bottom:5px;"><a href="http://puremaca.net/reviewsite5/code/?domain_id='.$web_seal->id.'" class=""> Get HTML Code</a></td>';
				$output .= '<td align="center" valign="top" style="padding-top:5px; padding-bottom:5px;"><a href="http://www.nutritionist-verified.com/code/?domain_id='.$web_seal->id.'" class=""> Get HTML Code</a></td>';

				$output .= '<td align="center" valign="top" style="padding-top:5px; padding-bottom:5px;">';
				$output .= '<div class="web_seal'.$web_seal->id.'" seal_type="version5"></div>';
				//$output .= "<script>var WebSeal{$web_seal->id} = new getWebSeal('{$web_seal->user_id}_{$web_seal->id}', 'version5');</script>";
				//$output .= var_dump(doctor_verified_web_seal_get_seal_domain_by_domain_id($web_seal->seal_domain));
				//$output .= "<script>alert('test');</script>";
				$output .= '</td>';				
				$output .= '</tr>';

				$output .= "<script>var WebSeal{$web_seal->id} = new getWebSeal('{$web_seal->user_id}_{$web_seal->id}', 'web_seal{$web_seal->id}');</script>";				

			}
		}


	//}

	$output .= '</table>';

	return $output;
}


function doctor_verified_web_seal_html_table_output() {
    ob_start();
    echo doctor_verified_web_seal_html_table();
    return ob_get_clean();
}


add_shortcode('doctor_verified_web_seal_html_table_repeater', 'doctor_verified_web_seal_html_table_output');


/*function doctor_verified_web_seal_enqueue_scripts() {

	wp_register_script( 'doctor-verified-web-seal-script', plugins_url( '/js/doctor-verified-web-seal.js' , __FILE__), array( 'jquery' ), '', false );
    wp_enqueue_script( 'doctor-verified-web-seal-script');

}

add_action( 'wp_enqueue_scripts', 'doctor_verified_web_seal_enqueue_scripts' );*/



function doctor_verified_html_form() {

	$output = '';

	//if(isset($_GET['domain']) && $_GET['domain'] == 1) {

	//} else {
	//if( !current_user_can('administrator') && current_user_can('access_optimizemember_level1')) {

		$output = '<div id="display-form-result"></div>';
		//$output .= '<form id="domainForm" name="domainForm" action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post" enctype="multipart/form-data">';
		//$output .= '<form id="domainForm" name="domainForm" action="" method="post" enctype="multipart/form-data">';
		$output .= '<form id="domainForm" name="domainForm" action="" method="post">';		
		$output .= '<div class="application-inner">';
		$output .= '<label class="label-style-domain">Domain 1:</label>';
		$output .= '<input type="text" name="website1" id="website1" class="input-style domain-input-spacing" value="' . ( isset( $_POST["website1"] ) ? esc_attr( $_POST["website1"] ) : '' ) . '">';
		$output .= '<br>';
		$output .= '<label class="label-style-domain">Domain 2:</label>';
		$output .= '<input type="text" name="website2" id="website2" class="input-style domain-input-spacing" value="' . ( isset( $_POST["website2"] ) ? esc_attr( $_POST["website2"] ) : '' ) . '">';
		$output .= '<br>';
		$output .= '<label class="label-style-domain">Domain 3:</label>';
		$output .= '<input type="text" name="website3" id="website3" class="input-style domain-input-spacing" value="' . ( isset( $_POST["website3"] ) ? esc_attr( $_POST["website3"] ) : '' ) . '">';
		$output .= '<br>';
		$output .= '<label class="label-style-domain">Domain 4:</label>';
		$output .= '<input type="text" name="website4" id="website4" class="input-style domain-input-spacing" value="' . ( isset( $_POST["website4"] ) ? esc_attr( $_POST["website4"] ) : '' ) . '">';
		$output .= '<br>';
		$output .= '<label class="label-style-domain">Domain 5:</label>';
		$output .= '<input type="text" name="website5" id="website5" class="input-style domain-input-spacing" value="' . ( isset( $_POST["website5"] ) ? esc_attr( $_POST["website5"] ) : '' ) . '">';
		$output .= '<br><br>';
		$output .= '<input name="save" id="btnSaveDomain" type="button" value="Continue" class="continue-button btn-70-l">';
		//$output .= '<input type="submit" name="save" id="btnSaveDomain" value="Continue" class="continue-button btn-70-l">';
		$output .= '</div>';

	    $output .= '<input type="hidden" name="action" value="doctor-verified-stripe"/>';
		$output .= '<input type="hidden" name="redirect" value="'. get_permalink().'"/>';
		$output .= '<input type="hidden" name="doctor_verified_additional_domain_nonce" value="'. wp_create_nonce('doctor-verified-additional-domain-nonce').'"/>';
		$output .= '</form>';
	//}
	//}
	
	return $output;

}

function doctor_verified_html_form_output() {
    ob_start();
    doctor_verified_send_mail();
    echo doctor_verified_html_form();
    return ob_get_clean();
}


add_shortcode('apply_for_additional_certificates_form', 'doctor_verified_html_form_output');



//https://stripe.com/docs/recipes/updating-customer-cards
//http://stackoverflow.com/questions/29753923/stripe-update-customer-default-card-php
//http://stackoverflow.com/questions/31627961/stripe-change-credit-card-number
//https://stripe.com/docs/api/php#create_charge
//https://stripe.com/docs/charges
function doctor_verified_card_update(){

	$plugin_img_url = plugin_dir_url( __FILE__ ).'img/';
	$user_ID = get_current_user_id();
	$user_data = get_userdata($user_ID);
	$customer_id = get_user_meta($user_ID, 'wp_optimizemember_subscr_cid');

	$user_email = isset($user_data->user_email) ? $user_data->user_email : '';

	if (!class_exists('Stripe')) {
		require_once ABSPATH.'wp-content/plugins/optimizeMember/optimizeMember-pro/includes/classes/gateways/stripe/stripe-sdk/lib/Stripe.php';
		//C:\wamp\www\wpdoctorsverified\wp-content\plugins\optimizeMember\optimizeMember-pro\includes\classes\gateways\stripe\stripe-sdk\lib\Stripe

		Stripe::setApiKey('');
		Stripe::setApiVersion('2015-02-18');
	}	

    $invoice_all = Stripe_Invoice::all(array("customer" => $customer_id[0], "limit" => 15));

    //$invoice_json = json_decode($invoices->data[0]);

    //return print_r($invoice_json );

    /*for( $i = 0; $i < count($invoices); $i++) {

    	$i = $invoices->data[0]->id;

    }*/

   //$invoice_all_json = json_decode($invoice_all->data[0]);

    $invoice_arr = array();
    for($i = 0; $i < count($invoice_all->data); $i++) {

    	$invoice_arr[] = json_decode($invoice_all->data[$i]);

    }

	//echo '<pre>';
    $invoice_item = array();
    foreach($invoice_arr as $invoice) {

    	//print_r($invoice->lines->data[0]);
    	//print_r($invoice->lines->data[0]->amount);
    	//print_r($invoice->lines->data[0]->period->start);
    	//print_r($invoice->lines->data[0]->period->end);
    	//print_r($invoice->lines->data[0]->amount);
    	//print_r($invoice->lines->data[0]->plan->name);
    	//print_r($invoice->lines->data[0]->plan->statement_descriptor);

    	$invoice_items["data"][] = array(
    									"period_start" => date('M j, Y', $invoice->lines->data[0]->period->start),
    									"period_end" => date('M j, Y', $invoice->lines->data[0]->period->end),
    									"payment_for_name" => $invoice->lines->data[0]->plan->name,
    									"payment_for_name_statement_descriptor" => $invoice->lines->data[0]->plan->statement_descriptor,
    									"amount" => number_format(($invoice->lines->data[0]->amount/100), 2, '.', ' ')

    								 );

    }
    //echo '</pre>';
	
	/*echo '<pre>';
	//echo print_r($invoice_arr);
	echo print_r($invoice_items["data"][0]["period_start"]);
	echo '</pre>';*/

    $output = '';

	$output = '<form id="cccard-info-form-update" novalidate autocomplete="on" action="" method="POST">';
	$output .= '<p class="validation"></p>';
	$output .= '<div class="form-group">';
	$output .= '<label for="cc-number" class="control-label">Card number formatting <small class="text-muted">[<span class="cc-brand"></span>]</small></label>';
	$output .= '<input id="cc-number" type="tel" class="input-lg form-control cc-number" autocomplete="cc-number" placeholder="•••• •••• •••• ••••" required>';
	$output .= '</div>';

	$output .= '<div class="form-group">';
	$output .= '<label for="cc-exp" class="control-label">Card expiry formatting</label>';
	$output .= '<input id="cc-exp" type="tel" class="input-lg form-control cc-exp" autocomplete="cc-exp" placeholder="•• / ••" required>';
	$output .= '</div>';

	$output .= '<div class="form-group">';
	$output .= '<label for="cc-cvc" class="control-label">Card CVC formatting</label>';
	$output .= '<input id="cc-cvc" type="tel" class="input-lg form-control cc-cvc" autocomplete="off" placeholder="•••" required>';
	$output .= '</div>';

	$output .= '<!--div class="form-group">';
	$output .= '<label for="numeric" class="control-label">Restrict numeric</label>';
	$output .= '<input id="numeric" type="tel" class="input-lg form-control" data-numeric>';
	$output .= '</div-->';

	$output .= '<input type="hidden" name="email" value="'.$user_email.'">';

	$output .= '<button type="submit" class="submit btn btn-lg btn-primary">Submit</button>';
	$output .= '</form>';

	$output .= '<table id="invoiceTable" class="display" cellspacing="0" width="100%">';
	$output .= '<thead>';
	$output .= '<tr>';
	$output .= '<th>Date</th>';
	$output .= '<th>Payment For</th>';
	$output .= '<th>Amount</th>';            
	$output .= '</tr>';
	$output .= '</thead>';
	$output .= '<tbody>';

	if(!empty($invoice_items["data"])) {
		for($i = 0; $i < count($invoice_items["data"]); $i++) {

			$output .= '<tr>';
			$output .= '<td class="align-row-center">'.$invoice_items["data"][$i]["period_start"].' - '.$invoice_items["data"][$i]["period_end"].'</td>'; //May 28, 2016-Jun 28, 2016
			$output .= '<td class="align-row-center">'.$invoice_items["data"][$i]["payment_for_name"].'</td>'; //Doctor Certified $29.95 monthly service fee
			$output .= '<td class="align-row-center">'.'&#36;'.$invoice_items["data"][$i]["amount"].'</td>'; //$29.95 
			$output .= '</tr>';
		}
	}

	$output .= '</tbody>';
	$output .= '</table>';

	return $output;

} 

function doctor_verified_card_update_output() {
    ob_start();
    echo doctor_verified_card_update();
    return ob_get_clean();
}

add_shortcode('doctor_verified_card_update', 'doctor_verified_card_update_output');


function doctor_verified_web_seal_web_seal_step_1() {

	global $wpdb;

	$domain_id = $_GET["domain_id"];
	$user_ID = get_current_user_id();
	$user_data = get_userdata($user_ID);

	$table = $wpdb->prefix . 'web_seals';
	$web_seal = $wpdb->get_row( "SELECT * FROM {$table} WHERE id = {$domain_id} AND user_id = {$user_ID}" );

	$master_id = $web_seal->user_id.'_'.$web_seal->id;

	$output = '<textarea readonly="readonly" rows="5" cols="170" id="step-1" class="textarea" style="width: 100%; height: 110px;">'."\n";
	$output .= '&lt;!--Doctor Verified --&gt;'."\n";
	
	//$output .= '&lt;script type="text/javascript" src="http://puremaca.net/reviewsite5/web-seal/js/doctor-verified-web-seal.js"&gt;&lt;/script&gt;'."\n";
	$output .= '&lt;script type="text/javascript" src="http://www.nutritionist-verified.com/web-seal/js/doctor-verified-web-seal.js"&gt;&lt;/script&gt;'."\n";	
	

	$output .= '&lt;script&gt;var WebSeal=new getWebSeal("'.$master_id.'","web_seal")&lt;/script&gt;'."\n";
	$output .= '&lt;!--End of Doctor Verified --&gt;'."\n";	
	$output .= '</textarea>';	
	$output .= "<script>var WebSeal = new getWebSeal('{$master_id}','web_seal');</script>";
	return $output;
}	


function doctor_verified_web_seal_web_seal_step_1_output() {
    ob_start();
    echo doctor_verified_web_seal_web_seal_step_1();
    return ob_get_clean();
}

add_shortcode('doctor_verified_web_seal_web_seal_step_1', 'doctor_verified_web_seal_web_seal_step_1_output');


function doctor_verified_web_seal_script() {

	global $wpdb;

	$domain_id = $_GET["domain_id"];
	$user_ID = get_current_user_id();
	$user_data = get_userdata($user_ID);

	$table = $wpdb->prefix . 'web_seals';
	$web_seal = $wpdb->get_row( "SELECT * FROM {$table} WHERE id = {$domain_id} AND user_id = {$user_ID}" );

	$master_id = $web_seal->user_id.'_'.$web_seal->id;

	$output .= "<script>var WebSeal = new getWebSeal('{$master_id}','web_seal');</script>";
	return $output;

}	


function doctor_verified_web_seal_script_output() {
    ob_start();
    echo doctor_verified_web_seal_script();
    return ob_get_clean();
}

add_shortcode('doctor_verified_web_seal_script', 'doctor_verified_web_seal_script_output');



function doctor_verified_reviewer_registration_form() {
	ob_start();	
	if(isset($_GET['reviewer']) && $_GET['reviewer'] == true) { ?>

		<?php 

			//echo $_POST["amount"]; 
			//var_dump(unserialize(base64_decode($_GET['r'])));
			//extract(unserialize(base64_decode($_GET['r'])));


			//$r_arr = unserialize(base64_decode($_GET['r']));  
		?>
		<h3>Step 2</h3>
		<fieldset>
		    <legend></legend>

		    <p class="application-thanks-page-text"> Thanks for applying as a reviewer of Doctor Certified&trade; seal.<br>
		    You will receive a confirmation receipt shortly via email.</p><br>

		</fieldset>

<?php 
	} else {

?>
	<style>

	</style>

	<form id="applyform-reviewer-none" class="cmxform" action="" method="POST" enctype="multipart/form-data">
		<div id="message"></div>
	    <!--h3>Step 1</h3-->
	    <fieldset>
	        <legend>Step 1</legend>
			<p>(*) Required</p>    

			<label for="txtFirstName" class="label-style"><b>First Name *</b></label><br>
			<input type="text" name="txtFirstName" id="txtFirstName" maxlength="500" class="input-style" value="" placeholder="" />
			<p class="password-reminder">Enter your First Name</p>

			<label class="label-style"><b>Last Name *</b></label><br>
			<input type="text" name="txtLastName" id="txtLastName" maxlength="500" class="input-style" value="" placeholder="" />
			<p class="password-reminder">Enter your Last Name</p>

			<label class="label-style"><b>Profession *</b></label><br>
			<input type="text" name="txtProfession" id="txtProfession" maxlength="500" class="input-style" value="" placeholder="" />
			<p class="password-reminder">Enter your Profession</p>

			<label class="label-style"><b>Enter your Email *</b></label> <br>
			<input type="text" name="txtEmailID" id="txtEmailID"   maxlength="500" class=" input-style" value="" >
			<p class="password-reminder">This will be used as your username as well as for correspondance. </p>	

			<label class="label-style"><b>Choose a Password *</b></label><br> 
			<input type="password" name="txtNewPassword" id="txtNewPassword"  maxlength="25" class="input-style" value=""></input>
			<p class="password-reminder"> Password must be atleast six characters. </p>

			<label class="label-style"><b>Address</b></label><br>
			<textarea name="txtAddress" id="txtAddress" maxlength="500" class="input-style" value="" placeholder="" /></textarea>
			<p class="password-reminder">Enter your Address</p>

			<label class="label-style"><b>City *</b></label><br>
			<input type="text" name="txtCity" id="txtCity" maxlength="500" class="input-style" value="" placeholder="" />
			<p class="password-reminder">Enter your City</p>

			<label class="label-style"><b>PostCode *</b></label><br>
			<input type="text" name="txtPostCode" id="txtPostCode" maxlength="500" class="input-style" value="" placeholder="" />
			<p class="password-reminder">Enter your PostCode</p>			


			<label class="label-style"><b>Country *</b></label><br>
			<input type="text" name="txtCountry" id="txtCountry" maxlength="500" class="input-style" value="" placeholder="" />
			<p class="password-reminder">Enter your Country</p>


			<label class="label-style"><b>Language *</b></label><br> 
			<select name="textLanguage" id="textLanguage" class="input-style">
				<option value="en" selected="selected">english (en)</option>
				<option value="ary">cestina "cs_CZ"</option>
				<option value="ar">danish (da_DK)</option>
				<option value="az">german (de_DE)</option>
				<option value="azb">spanish (es)</option>
				<option value="bg_BG">french (fr)</option>
				<option value="bn_BD">croatian/hrvatski (hr)</option>
				<option value="bs_BA">hungarian (hu_HU)</option>
				<option value="ca">italian (it_IT)</option>
				<option value="ceb">japan (jp)</option>
				<option value="cs_CZ">norweigian (no)</option>
				<option value="cy">netherlands / dutch (nl_NL)</option>
				<option value="da_DK">portugese (pt_PT)</option>
				<option value="de_CH">slovak (sk_SK)</option>
				<option value="de_CH_informal">slovenian (sl_SI)</option>
				<option value="de_DE">swedish (sv_SE)</option>
				<option value="de_DE_formal">turkish (tr_TR)</option>
				<option value="el">chinese (zh_CN)</option>				
				</select>			
			<br><br> 

			<label class="label-style-phone"><b>Contact Number </b> <span class="text-optional">(Optional)</span></label><br> 
			<input type="text" name="textContactNumber" id="textContactNumber" size="26" maxlength="25" class="input-style-phone" value="" ></input>
			<p class="phone-number-reminder"> If we need to contact you about your application. </p>

	    </fieldset>
	 
	    <!--h3>Step 2</h3-->
	    <fieldset>
	        <legend>Step 2</legend>
			<p>(Optional)</p>    

			<label class="label-style"><b>Biography</b></label><br>
			<textarea name="txtBiography" id="txtBiography" maxlength="500" class="input-style" value="" placeholder="" /></textarea>
			<p class="password-reminder">Enter your Biography</p>

	    </fieldset>

	    <!--h3>Step 3</h3-->
	    <fieldset>
	        <legend>Step 3</legend>
			<p>(*) Required</p>    

			<label class="label-style"><b>Upload Proof of Certificate</b></label><br>
			<input type="file" name="txtProofOfCertificate" id="txtProofOfCertificate" class="input-style" value="">
			<br><br> 
			<label class="label-style"><b>Upload Photo ID</b></label><br>
			<input type="file" name="txtPhotoID" id="txtPhotoID" class="input-style" value="">

			<?php //echo do_shortcode('[fu-upload-form]'); ?>

	    </fieldset>	    
	 	  
	    <input type="hidden" name="action" value="doctor-verified-reviewer"/>
		<input type="hidden" name="redirect" value="<?php echo get_permalink(); ?>"/>
		<input type="hidden" name="doctor_verified_reviewer_nonce" value="<?php echo wp_create_nonce('doctor-verified-reviewer-nonce'); ?>"/>
		<!--input name="save" id="btnSaveDomain" type="button" value="Continue" class="continue-button btn-70-l"-->
		<input type="submit" value="Save" name="submit">

	</form>
 
<?php
	}
	return ob_get_clean();
}
add_shortcode('doctor_verified_reviewer_registration_form', 'doctor_verified_reviewer_registration_form');