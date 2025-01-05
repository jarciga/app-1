<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://www.doctor-verified.com
 * @since             1.0.0
 * @package           Doctor_Verified
 *
 * @wordpress-plugin
 * Plugin Name:       doctor verified
 * Plugin URI:        
 * Description:       Doctor Verified Plugin
 * Version:           1.0.0
 * Author:            Doctor Verified
 * Author URI:        
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       doctor-verified
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**********************************
* constants and globals
**********************************/

if(!defined('DOCTOR_VERIFIED_BASE_URL')) {
	define('DOCTOR_VERIFIED_BASE_URL', plugin_dir_url(__FILE__));
}
if(!defined('DOCTOR_VERIFIED_BASE_DIR')) {
	define('DOCTOR_VERIFIED_BASE_DIR', dirname(__FILE__));
}

$doctor_verified_options = get_option('doctor_verified_settings');

/*******************************************
* plugin text domain for translations
*******************************************/

load_plugin_textdomain( 'doctor_verified', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

/**********************************
* includes
**********************************/

if(is_admin()) {
	// load admin includes
	include(DOCTOR_VERIFIED_BASE_DIR . '/includes/settings.php');
	include(DOCTOR_VERIFIED_BASE_DIR . '/includes/scripts.php');
} else {
	// load front-end includes
	include(DOCTOR_VERIFIED_BASE_DIR . '/includes/scripts.php');
	include(DOCTOR_VERIFIED_BASE_DIR . '/includes/shortcodes.php');
	include(DOCTOR_VERIFIED_BASE_DIR . '/includes/process-payment.php');
	include(DOCTOR_VERIFIED_BASE_DIR . '/includes/process-reviewer.php');
}


register_activation_hook( __FILE__, 'activate_doctor_verified' );

register_deactivation_hook( __FILE__, 'deactivate_doctor_verified' );

function activate_doctor_verified() {

    //global $wp_roles;

   /* if ( ! isset( $wp_roles ) ){
        $wp_roles = new WP_Roles();
    }*/

    /*$caps1 = array(
    				"activate_plugins" => true, "create_users" => true, "delete_plugins" => true, "delete_themes" => true, "delete_users" => true, "edit_files" => true, "edit_plugins" => true, "edit_theme_options" => true, "edit_themes" => true, "edit_users" => true,
    				"export" => true, "import" => true, "install_plugins" => true, "install_themes" => true, "list_users" => true, "manage_options" => true, "promote_users" => true, "remove_users" => true, "switch_themes" => true, "update_core" => true, 
    				"update_plugins" => true, "update_themes" => true, "edit_dashboard" => true, "moderate_comments" => true, "manage_categories" => true, "manage_links" => true, "edit_others_posts" => true, "edit_pages" => true, "edit_others_pages" => true, "edit_published_pages" => true,
    				"publish_pages" => true, "delete_pages" => true, "delete_others_pages" => true, "delete_published_pages" => true, "delete_others_posts" => true, "delete_private_posts" => true, "edit_private_posts" => true, "read_private_posts" => true, "delete_private_pages" => true, "edit_private_pages" => true,  
    				"read_private_pages" => true, "unfiltered_html" => true, "edit_published_posts" => true, "upload_files" => true, "publish_posts" => true, "delete_published_posts" => true, "edit_posts" => true, "delete_posts" => true, "level_10" => true
    				);*/

    $caps1 = array("read" => true, "delete_users" => true, "create_users" => true, "list_users" => true, "remove_users" => true, "add_users" => true,
    			   "promote_users" => true, "manage_options" => true, "unfiltered_html" => true, "upload_files" => true, "level_0" => true);   

    $caps2 = array("read" => true, "upload_files" => true, "level_0" => true);

	add_role( 'doctor_verified_reviewer', 'Reviewer - Doctor Verified', $caps1 );
	add_role( 'doctor_verified_member', 'Member - Doctor Verified', $caps2 );
}

function deactivate_doctor_verified() {

	remove_role( 'doctor_verified_reviewer');
	remove_role( 'doctor_verified_member');

}


//$user_id, $seal_status = 'active', $seal_domain, $seal_type = 'doctor verified', $seal_language = 'en', $seal_issued_on, $created_at, $updated_at
function doctor_verified_save_to_web_seal($user_id, $seal_status = 'pending', $seal_domain, $seal_type = 'doctor verified', $seal_language = 'en', $seal_coupon_code ='', $seal_issued_on, $created_at, $updated_at) {

	global $wpdb;

	$table = $wpdb->prefix . 'web_seals';

	$data = array( 
                   'user_id' => $user_id,
				   'seal_status' => $seal_status,
				   'seal_domain' => $seal_domain,
				   'seal_type' => $seal_type,
				   'seal_language' => $seal_language,
				   'seal_coupon_code' => $seal_coupon_code,
				   'seal_issued_on' => $seal_issued_on,
				   'created_at' => $created_at,
				   'updated_at' => $updated_at
				 );

	$format = array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' );

	$wpdb->insert( $table, $data, $format );

}


function doctor_verified_web_seal_html_table_repeater_result() {

	global $wpdb;

	$user_ID = get_current_user_id();

	$table = $wpdb->prefix . 'web_seals';
	$web_seals = $wpdb->get_results( "SELECT * FROM {$table} WHERE user_id = {$user_ID}" );
	return $web_seals;

}


function doctor_verified_check_domain($domain = '') {

	global $wpdb;
	$table = $wpdb->prefix . 'web_seals';
	$check_web_seals = $wpdb->get_results( "SELECT * FROM {$table} WHERE seal_domain = '{$domain}'" );

	return $check_web_seals;

}


function doctor_verified_save_to_db_table($domain, $type = 'doctor verified', $language = 'en', $seal_coupon_code = '') {

	global $wpdb;

	$table = $wpdb->prefix . 'web_seals';
	$data = array( 
                   'user_id' => get_current_user_id(),
				   'seal_status' => 'pending',
				   'seal_domain' => $domain,
				   'seal_type' => $type,
				   'seal_language' => $language,
				   'seal_coupon_code' => $seal_coupon_code,				   
				   'seal_issued_on' => date("Y-m-d H:i:s"),
				   'created_at' => date("Y-m-d H:i:s"),
				   'updated_at' => date("Y-m-d H:i:s")
				 );

	$format = array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' );

	return $wpdb->insert( $table, $data, $format );
}


	/**
	*  A method for inserting multiple rows into the specified table
	*  Updated to include the ability to Update existing rows by primary key
	*  
	*  Usage Example for insert: 
	*
	*  $insert_arrays = array();
	*  foreach($assets as $asset) {
	*  $time = current_time( 'mysql' );
	*  $insert_arrays[] = array(
	*  'type' => "multiple_row_insert",
	*  'status' => 1,
	*  'name'=>$asset,
	*  'added_date' => $time,
	*  'last_update' => $time);
	*
	*  }
	*
	*
	*  wp_insert_rows($insert_arrays, $wpdb->tablename);
	*
	*  Usage Example for update:
	*
	*  wp_insert_rows($insert_arrays, $wpdb->tablename, true, "primary_column");
	*
	*
	* @param array $row_arrays
	* @param string $wp_table_name
	* @param boolean $update
	* @param string $primary_key
	* @return false|int
	*
	* @author	Ugur Mirza ZEYREK
	* @contributor Travis Grenell
	* @source http://stackoverflow.com/a/12374838/1194797
	* @ source http://stackoverflow.com/questions/12373903/wordpress-wpdb-insert-multiple-records
	*/
	 
function wp_insert_rows($row_arrays = array(), $wp_table_name, $update=false, $primary_key=null) {
	global $wpdb;
	$wp_table_name = esc_sql($wp_table_name);
	// Setup arrays for Actual Values, and Placeholders
	$values = array();
	$place_holders = array();
	$query = "";
	$query_columns = "";
	
	$query .= "INSERT INTO `{$wp_table_name}` (";
	
	        foreach($row_arrays as $count => $row_array)
	        {
	
	            foreach($row_array as $key => $value) {
	
	                if($count == 0) {
	                    if($query_columns) {
	                    $query_columns .= ", ".$key."";
	                    } else {
	                    $query_columns .= "".$key."";
	                    }
	                }
	
	                $values[] =  $value;
	
	                if(is_numeric($value)) {
	                    if(isset($place_holders[$count])) {
	                    $place_holders[$count] .= ", '%d'";
	                    } else {
	                    $place_holders[$count] = "( '%d'";
	                    }
	                } else {
	                    if(isset($place_holders[$count])) {
	                    $place_holders[$count] .= ", '%s'";
	                    } else {
	                    $place_holders[$count] = "( '%s'";
	                    }
	                }
	            }
	                    // mind closing the GAP
	                    $place_holders[$count] .= ")";
	        }
	
	$query .= " $query_columns ) VALUES ";
	
	$query .= implode(', ', $place_holders);
	
  if ($update)	
  {
    $update=" ON DUPLICATE KEY UPDATE $primary_key=VALUES( $primary_key ),";
    $cnt=0;
    foreach($row_arrays[0] as $key => $value)
    {
      if($cnt==0) {
        $update .= "$key=VALUES($key)";
        $cnt=1;
      } else {
        $update .= ", $key=VALUES($key)";
      }
    }
    $query .= $update;
  }
  
  $sql=$wpdb->prepare($query, $values);
  if($wpdb->query($sql)){
    return true;
  } else {
    return false;
  }
}

add_action( 'wp_ajax_nopriv_doctor_verified_send_mail', 'doctor_verified_send_mail' );
add_action( 'wp_ajax_doctor_verified_send_mail', 'doctor_verified_send_mail' );

//http://codeblow.com/questions/wordpress-place-multiple-rows-right-into-a-data-table/
/*function doctor_verified_send_mail() {

	global $wpdb;

	$user_ID = get_current_user_id();
	$user_data = get_userdata($user_ID);

	$table = $wpdb->prefix . 'web_seals';

	$website_arr = array();
	$result = '';
	$output = '';

	$web_seal1 = false;
	$web_seal2 = false;
	$web_seal3 = false;
	$web_seal4 = false;
	$web_seal5 = false;

	if(isset($_POST["website1"]) && $_POST["website1"] !== '' ){

		$web_seal1 = true;

		doctor_verified_save_to_db_table($_POST["website1"]);

	}
	if(isset( $_POST["website2"]) && $_POST["website2"] !== '' ){

		$web_seal2 = true;

		doctor_verified_save_to_db_table($_POST["website2"]);		

	}
	if(isset( $_POST["website3"]) && $_POST["website3"] !== '' ){

		$web_seal3 = true;

		doctor_verified_save_to_db_table($_POST["website3"]);		

	}
	if(isset( $_POST["website4"]) && $_POST["website4"] !== '' ){

		$web_seal4 = true;

		doctor_verified_save_to_db_table($_POST["website4"]);		

	}	
	if(isset( $_POST["website5"]) && $_POST["website5"] !== '' ){

		$web_seal5 = true;

		doctor_verified_save_to_db_table($_POST["website5"]);		

	}	


	if($web_seal1 !== false || $web_seal2 !== false || $web_seal3 !== false || $web_seal4 !== false || $web_seal5 !== false) {

		$output = '<b>Thank you for submitting an application for the following domain';
		$output .= 'name(s):<br><br>There will be a monthly charge of $29.95 for each'; 
		$output .= 'domain name added.<br>We will be in touch with you once your website(s)'; 
		$output .= 'have been reviewed by our team of doctors.</b><br><br>';

		//echo $output;
		wp_die($output);

	}	

}*/

function doctor_verified_send_mail() {

	global $wpdb;

	$user_ID = get_current_user_id();
	$user_data = get_userdata($user_ID);

	$table = $wpdb->prefix . 'web_seals';

	$website_arr = array();
	$result = '';
	$output = '';

    $user_id = get_current_user_id();
    $seal_status = 'pending';	  
    $seal_type = 'doctor verified';
    $seal_language = 'en';
    $seal_coupon_code = '';
    $seal_issued_on = date("Y-m-d H:i:s");
    $created_at = date("Y-m-d H:i:s");
    $updated_at = date("Y-m-d H:i:s");	

	$web_seal1 = false;
	$web_seal2 = false;
	$web_seal3 = false;
	$web_seal4 = false;
	$web_seal5 = false;

	if(isset($_POST["website1"]) && $_POST["website1"] !== '' ){

		$web_seal1 = true;
		$website_arr[] = $_POST["website1"];

	}
	if(isset( $_POST["website2"]) && $_POST["website2"] !== '' ){

		$web_seal2 = true;
		$website_arr[] = $_POST["website2"];		

	}
	if(isset( $_POST["website3"]) && $_POST["website3"] !== '' ){

		$web_seal3 = true;
		$website_arr[] = $_POST["website3"];	

	}
	if(isset( $_POST["website4"]) && $_POST["website4"] !== '' ){

		$web_seal4 = true;
		$website_arr[] = $_POST["website4"];		

	}	
	if(isset( $_POST["website5"]) && $_POST["website5"] !== '' ){

		$web_seal5 = true;
		$website_arr[] = $_POST["website5"];		

	}	


	//Get seal status that's not active
	$check_web_seals_coupon_codes = $wpdb->get_results( 
		"
		SELECT * 
		FROM $table								
		WHERE user_id = $user_ID
		AND is_additional_domain = 0
		",
		ARRAY_A
	); 

	/*if(!empty($check_web_seals_coupon_codes[0]["seal_coupon_code"])) {
		echo 'not empty';

	} else {

		echo 'empty';

	}*/

	$seal_coupon_code_arr = unserialize(base64_decode($check_web_seals_coupon_codes[0]["seal_coupon_code"]));

	//echo $seal_coupon_code_arr["one-time setup"];
	//echo $seal_coupon_code_arr["monthly service"];

	//print_r( unserialize(base64_decode($check_web_seals_coupon_codes[0]["seal_coupon_code"])) );


	if(!empty($seal_coupon_code_arr)) {
		
		// With freesetup, dqpz
		// Plan Name: Seal Basic Monthly Fee with "dqpz" Coupon 
		// Plan ID: seal_basic_monthly_fee_with_dqpz_coupon 
		// Plan Price: $29.95 USD/month
		if( ( isset($seal_coupon_code_arr["one-time setup"] ) && trim($seal_coupon_code_arr["one-time setup"]) === 'freesetup' ) &&
			( isset($seal_coupon_code_arr["monthly service"] ) && trim($seal_coupon_code_arr["monthly service"]) === 'dqpz' ) ) {

			//echo $plan_id = 'seal_basic_monthly_fee_with_dqpz_coupon';
			$monthly_service_fee = '$29.95';

		}


		// With freesetup
		// Plan Name: Seal Basic Monthly Fee
		// Plan ID: seal_basic_monthly_fee
		// Plan Price: $49.95 USD/month
		elseif( ( isset($seal_coupon_code_arr["one-time setup"] ) && trim($seal_coupon_code_arr["one-time setup"]) === 'freesetup' ) ) {

			//echo $plan_id = 'seal_basic_monthly_fee';
			$monthly_service_fee = '$49.95';

		}						

		//with dqpz
		// Plan Name: Seal Basic Monthly Fee with "dqpz" Coupon 
		// Plan ID: seal_basic_monthly_fee_with_dqpz_coupon 
		// Plan Price: $29.95 USD/month					
		elseif( ( isset($seal_coupon_code_arr["monthly service"] ) && trim($seal_coupon_code_arr["monthly service"]) === 'dqpz' ) ) {

			//echo $plan_id = 'seal_basic_monthly_fee_with_dqpz_coupon';
			$monthly_service_fee = '$29.95';

		}

	} else { // Empty Seal Coupon Code

		$monthly_service_fee = '$49.95';

		/*if( isset($charge_web_seals[$i]["is_additional_domain"]) && 0 === (int) $charge_web_seals[$i]["is_additional_domain"]) {
		
			echo $plan_id = 'seal_basic_monthly_fee';

		} elseif( isset($charge_web_seals[$i]["is_additional_domain"]) && 1 === (int) $charge_web_seals[$i]["is_additional_domain"]) {

			echo $plan_id = 'additional_domain_monthly_charges_4995';


		}*/

	}	


	if($web_seal1 !== false || $web_seal2 !== false || $web_seal3 !== false || $web_seal4 !== false || $web_seal5 !== false) {

		$insert_arr = array();

		$customer_id = get_user_meta(get_current_user_id(), 'wp_optimizemember_subscr_cid');	

		foreach($website_arr as $website) {

			$insert_arr[] = array( 
			   'user_id' => get_current_user_id(),
			   'seal_status' => 'pending',
			   'seal_domain' => $website,
			   'seal_type' => 'doctor verified',
			   'seal_language' => 'en',
			   'is_additional_domain' => true,
			   'seal_coupon_code' => !empty($check_web_seals_coupon_codes[0]["seal_coupon_code"]) ? $check_web_seals_coupon_codes[0]["seal_coupon_code"] : '',		   
			   'seal_issued_on' => date("Y-m-d H:i:s"),
			   'customer_id' => $customer_id[0],
			   'created_at' => date("Y-m-d H:i:s"),
			   'updated_at' => date("Y-m-d H:i:s")
			);

		}

		$result = wp_insert_rows($insert_arr, $table);

		if($result) {

			$message = 'Request for additional domain' . "\r\n";

			foreach($website_arr as $website) {

				$message .= $website . "\r\n";

			}	
			
			$to = get_bloginfo('admin_email');
			$subject = 'Request for additional domain';
			$headers = 'From: Doctor Verified Member <'.$user_data->user_email.'>' . "\r\n";
			$headers .= 'Content-Type: text/html' . "\r\n";
			$headers .= 'BCC: Jay-ar A. Arciga Jr. <jayar.arciga.jr@gmail.com>' . "\r\n";

			wp_mail( $to, $subject, $message, $headers );

			$output = '<b>Thank you for submitting an application for the following domain';
			$output .= 'name(s):<br><br>There will be a monthly charge of '. $monthly_service_fee .' for each'; 
			//$output .= 'name(s):<br><br>There will be a monthly charge of $29.95 for each'; 
			$output .= 'domain name added.<br>We will be in touch with you once your website(s)'; 
			$output .= 'have been reviewed by our team of doctors.</b><br><br>';		
			
			wp_die($output);
		}

	}

}


//ADMINISTRATION AREA


if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


/************************** CREATE A PACKAGE CLASS *****************************
 *******************************************************************************
 * Create a new list table package that extends the core WP_List_Table class.
 * WP_List_Table contains most of the framework for generating the table, but we
 * need to define and override some methods so that our data can be displayed
 * exactly the way we need it to be.
 * 
 * To display this example on a page, you will first need to instantiate the class,
 * then call $yourInstance->prepare_items() to handle any data manipulation, then
 * finally call $yourInstance->display() to render the table to the page.
 * 
 * Our theme for this list table is going to be movies.
 */
class Doctor_Verified_List_Table extends WP_List_Table {

    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'seal',     //singular name of the listed records
            'plural'    => 'seals',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
    }


    /** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title() 
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as 
     * possible. 
     * 
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     * 
     * For more detailed insight into how columns are handled, take a look at 
     * WP_List_Table::single_row_columns()
     * 
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($item, $column_name){
        switch($column_name){
        	case 'user_id':
            case 'seal_status':
            case 'seal_email':             
            case 'seal_domain':
            case 'seal_type':                                                     
            case 'seal_language':
            case 'is_additional_domain':            
            case 'seal_coupon_code':            
            case 'seal_issued_on':
                return $item[$column_name];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }


    /** ************************************************************************
     * Recommended. This is a custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'title'. Every time the class
     * needs to render a column, it first looks for a method named 
     * column_{$column_title} - if it exists, that method is run. If it doesn't
     * exist, column_default() is called instead.
     * 
     * This example also illustrates how to implement rollover actions. Actions
     * should be an associative array formatted as 'slug'=>'link html' - and you
     * will need to generate the URLs yourself. You could even ensure the links
     * 
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/

    function column_seal_domain($item){

    	$user_ID = get_current_user_id();
        
        //Build row actions
        $actions = array(
            
            'edit'      => sprintf('<a href="?page=%s&action=%s&seal=%s&user='.$user_ID.'">Edit</a>',$_REQUEST['page'],'edit',$item['id']),

            //'edit'      => sprintf('<a href="?page=%s&action=%s&seal=%s">Edit</a>',$_REQUEST['page'],'edit',$item['id']),
            //'delete'    => sprintf('<a href="?page=%s&action=%s&seal=%s">Delete</a>',$_REQUEST['page'],'delete',$item['id']),
        );
       
        //Return the title contents
        //return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/ //$item['seal_domain'],
            /*$2%s*/ //$item['id'],
            /*$3%s*/ //$this->row_actions($actions)
        //);


         return sprintf('%1$s %2$s', $item['seal_domain'], $this->row_actions($actions) );
    }


	function column_seal_language( $item ) {

		//'<select name="textLanguage" id="textLanguage" class="input-style"><option value="en" selected="selected">English (United States)</option><option value="ary">العربية المغربية</option><option value="ar">العربية</option><option value="az">Azərbaycan dili</option><option value="azb">گؤنئی آذربایجان</option><option value="bg_BG">Български</option><option value="bn_BD">বাংলা</option><option value="bs_BA">Bosanski</option><option value="ca">Català</option><option value="ceb">Cebuano</option><option value="cs_CZ">Čeština&lrm;</option><option value="cy">Cymraeg</option><option value="da_DK">Dansk</option><option value="de_CH">Deutsch (Schweiz)</option><option value="de_CH_informal">Deutsch (Schweiz, Du)</option><option value="de_DE">Deutsch</option><option value="de_DE_formal">Deutsch (Sie)</option><option value="el">Ελληνικά</option><option value="en_GB">English (UK)</option><option value="en_ZA">English (South Africa)</option><option value="en_NZ">English (New Zealand)</option><option value="en_CA">English (Canada)</option><option value="en_AU">English (Australia)</option><option value="eo">Esperanto</option><option value="es_VE">Español de Venezuela</option><option value="es_MX">Español de México</option><option value="es_ES">Español</option><option value="es_CO">Español de Colombia</option><option value="es_CL">Español de Chile</option><option value="es_AR">Español de Argentina</option><option value="es_GT">Español de Guatemala</option><option value="es_PE">Español de Perú</option><option value="et">Eesti</option><option value="eu">Euskara</option><option value="fa_IR">فارسی</option><option value="fi">Suomi</option><option value="fr_BE">Français de Belgique</option><option value="fr_CA">Français du Canada</option><option value="fr_FR">Français</option><option value="gd">Gàidhlig</option><option value="gl_ES">Galego</option><option value="haz">هزاره گی</option><option value="he_IL">עִבְרִית</option><option value="hi_IN">हिन्दी</option><option value="hr">Hrvatski</option><option value="hu_HU">Magyar</option><option value="hy">Հայերեն</option><option value="id_ID">Bahasa Indonesia</option><option value="is_IS">Íslenska</option><option value="it_IT">Italiano</option><option value="ja">日本語</option><option value="ka_GE">ქართული</option><option value="ko_KR">한국어</option><option value="lt_LT">Lietuvių kalba</option><option value="mk_MK">Македонски јазик</option><option value="mr">मराठी</option><option value="ms_MY">Bahasa Melayu</option><option value="my_MM">ဗမာစာ</option><option value="nb_NO">Norsk bokmål</option><option value="nl_NL">Nederlands</option><option value="nl_NL_formal">Nederlands (Formeel)</option><option value="nn_NO">Norsk nynorsk</option><option value="oci">Occitan</option><option value="pl_PL">Polski</option><option value="ps">پښتو</option><option value="pt_PT">Português</option><option value="pt_BR">Português do Brasil</option><option value="ro_RO">Română</option><option value="ru_RU">Русский</option><option value="sk_SK">Slovenčina</option><option value="sl_SI">Slovenščina</option><option value="sq">Shqip</option><option value="sr_RS">Српски језик</option><option value="sv_SE">Svenska</option><option value="th">ไทย</option><option value="tl">Tagalog</option><option value="tr_TR">Türkçe</option><option value="ug_CN">Uyƣurqə</option><option value="uk">Українська</option><option value="vi">Tiếng Việt</option><option value="zh_CN">简体中文</option><option value="zh_TW">繁體中文</option></select>';	

		/*$languages = array( "en" => "English (United States)", "ary" => "العربية المغربية", "ar" => "العربية", "az" => "Azərbaycan dili",
							"azb" => "گؤنئی آذربایجان", "bg_BG" => "Български", "bn_BD" => "বাংলা", "bs_BA" => "Bosanski",
							"ca" => "Català", "ceb" => "Cebuano", "cs_CZ" => "Čeština&lrm;", "cy" => "Cymraeg",
							"da_DK" => "Dansk", "de_CH" => "Deutsch (Schweiz)", "de_CH_informal" => "Deutsch (Schweiz, Du)", "de_DE" => "Deutsch",
							"de_DE_formal" => "Deutsch (Sie)", "el" => "Ελληνικά", "en_GB" => "English (UK)", "en_ZA" => "English (South Africa)",
							"en_NZ" => "English (New Zealand)", "en_CA" => "English (Canada)", "en_AU" => "English (Australia)", "eo" => "Esperanto",
							"es_VE" => "Español de Venezuela", "es_MX" => "Español de México", "es_ES" => "Español", "es_CO" => "Español de Colombia",
							"es_CL" => "Español de Chile", "es_AR" => "Español de Argentina", "es_GT" => "Español de Guatemala", "es_PE" => "Español de Perú",
							"et" => "Eesti", "eu" => "Euskara", "fa_IR" => "فارسی", "fi" => "Suomi",
							"fr_BE" => "Français de Belgique", "fr_CA" => "Français du Canada", "fr_FR" => "Français", "gd" => "Gàidhlig",
							"gl_ES" => "Galego", "haz" => "هزاره گی", "he_IL" => "עִבְרִית", "hi_IN" => "हिन्दी",
							"hr" => "Hrvatski", "hu_HU" => "Magyar", "hy" => "Հայերեն", "id_ID" => "Bahasa Indonesia",
							"is_IS" => "Íslenska", "it_IT" => "Italiano", "ja" => "日本語", "ka_GE" => "ქართული",
							"ko_KR" => "한국어", "lt_LT" => "Lietuvių kalba", "mk_MK" => "Македонски јазик", "mr" => "मराठी",
							"ms_MY" => "Bahasa Melayu", "my_MM" => "ဗမာစာ", "nb_NO" => "Norsk bokmål", "nl_NL" => "Nederlands",
							"nl_NL_formal" => "Nederlands (Formeel)", "nn_NO" => "Norsk nynorsk", "oci" => "Occitan", "pl_PL" => "Polski",
							"ps" => "پښتو", "pt_PT" => "Português", "pt_BR" => "Português do Brasil", "ro_RO" => "Română", 
							"ru_RU" => "Русский", "sk_SK" => "Slovenčina", "sl_SI" => "Slovenščina", "sq" => "Shqip",
							"sr_RS" => "Српски језик", "sv_SE" => "Svenska", "th" => "ไทย", "tl" => "Tagalog",
							"tr_TR" => "Türkçe", "ug_CN" => "Uyƣurqə", "uk" => "Українська", "vi" => "Tiếng Việt",
							"zh_CN" => "简体中文", "zh_TW" => "繁體中文");	*/

		/*$languages = array("en" => 'english (en)', "cs_CZ" => 'cestina (cs_CZ)', "da_DK" => 'danish (da_DK)', "de_DE" => 'german (de_DE)',
		"es" => 'spanish (es)', "fr" => 'french (fr)', "hr" => 'croatian/hrvatski (hr)', "hu_HU" => 'hungarian (hu_HU)',
		"it_IT" => 'italian (it_IT)', "jp" => 'japan (jp)', "no" => 'norweigian (no)', "nl_NL" => 'netherlands / dutch (nl_NL)',
		"pl_PL" => 'polish (pl_PL)', "pt_PT" => 'portugese (pt_PT)', "sk_SK" => 'slovak (sk_SK)', "sl_SI" => 'slovenian (sl_SI)',
		"sv_SE" => 'swedish (sv_SE)', "tr_TR" => 'turkish (tr_TR)', "zh_CN" => 'chinese (zh_CN)');*/	


		$languages = array("en" => 'English', "cs_CZ" => 'Cestina', "da_DK" => 'Danish', "de_DE" => 'German',
		"es" => 'Spanish', "fr" => 'French', "hr" => 'Croatian', "hu_HU" => 'Hungarian',
		"it_IT" => 'Italian', "jp" => 'Japan', "no" => 'Norweigian', "nl_NL" => 'Netherlands',
		"pl_PL" => 'Polish', "pt_PT" => 'Portugese ', "sk_SK" => 'Slovak', "sl_SI" => 'Slovenian',
		"sv_SE" => 'Swedish', "tr_TR" => 'Turkish', "zh_CN" => 'Chinese');			

		$output = '<select name="textLanguage['.$item['id'].']" id="textLanguage['.$item['id'].']" class="input-style" style="width:119px; font-size:11px;"><option value="en" selected="selected">';

		foreach( $languages as $key => $value) {

			if($key === $item['seal_language']) {
				$output .= '<option value="'.$key.'" selected="selected">'.$value.'</option>';
			} else {
				$output .= '<option value="'.$key.'">'.$value.'</option>';				
			}

		}

		$output .= '</select>';

		return $output;	
	}

    /*function column_is_additional_domain($item) {

    }*/	


	function column_seal_email( $item ) {

		$user_ID = $item["user_id"];
		$user_data = get_userdata($user_ID);		

		return $user_data->user_email;
	}



	function column_seal_coupon_code( $item ) {
		//unserialize(base64_decode($web_seals[$i]["seal_coupon_code"]))
		//return print_r(unserialize(base64_decode($item['seal_coupon_code'])));
		//( [one-time setup] => freesetup [monthly service] => dqpz ) 

		$seal_coupon_code_arr = unserialize(base64_decode($item['seal_coupon_code']));

		if(!empty($seal_coupon_code_arr)) {
			//foreach($seal_coupon_code_arr as $seal_coupon_code_key => $seal_coupon_code_value) {}

			return implode(', ', $seal_coupon_code_arr);
		}

		//return $seal_coupon_code;
	}


    /** ************************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['id']                //The value of the checkbox should be the record's id
        );
    }


    /** ************************************************************************
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value 
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     * 
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'user_id' => 'user ID',
            'seal_status'    => 'Status',
            'seal_email'     => 'Email',            
            'seal_domain'     => 'Domain',
            'seal_type'     => 'Type',                                         
            'seal_language'    => 'Lang',
			'is_additional_domain' =>  'Is Additional',          
            'seal_coupon_code'    => 'Coupon/s',
            'seal_issued_on'  => 'Created'
        );
        return $columns;
    }


    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle), 
     * you will need to register it here. This should return an array where the 
     * key is the column that needs to be sortable, and the value is db column to 
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     * 
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items() and sort
     * your data accordingly (usually by modifying your query).
     * 
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
    function get_sortable_columns() {
        $sortable_columns = array(
            'user_id' => array('user_id',false),
            'seal_status'    => array('seal_status',false),
            'seal_email'    => array('seal_email',false),            
            'seal_domain'     => array('seal_domain',false),     //true means it's already sorted
            'seal_type'    => array('seal_type',false),
            'seal_language'    => array('seal_language',false),
            'is_additional_domain'    => array('is_additional_domain',false),
            'seal_coupon_code'    => array('seal_coupon_code',false),
            'seal_issued_on'  => array('seal_issued_on',false)
        );
        return $sortable_columns;
    }


    /** ************************************************************************
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible Title'
     * 
     * If this method returns an empty value, no bulk action will be rendered. If
     * you specify any bulk actions, the bulk actions box will be rendered with
     * the table automatically on display().
     * 
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     * 
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions() {

         if ( current_user_can('administrator') || current_user_can('doctor_verified_reviewer') ) {
	        $actions = array(	            
	            'activate' => 'Activate',
				'activate with dqpz'  => 'Activate with dqpz',
				//'activate with freesetup' => 'Activate with freesetup',
				//'activate with freesetup and dqpz' => 'Activate with freesetup and dqpz',
	            'deactivate'    => 'Deactivate',
	            'update'    => 'Update'           
	            //'delete'    => 'Delete'        
	        );
    	}  else {    		
    		$actions = array();
    	}

    	return $actions;

    }


    /** ************************************************************************
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     * 
     * @see $this->prepare_items()
     **************************************************************************/
    function process_bulk_action() {
        
        //Detect when a bulk action is being triggered...
        /* if( 'delete'===$this->current_action() ) {
            wp_die('Items deleted (or they would be if we had items to delete)!');
        }*/

        global $wpdb;
        
    	$table = $wpdb->prefix . 'web_seals';        

      	//$user_ID = isset($_REQUEST["user"]) ? $_REQUEST["user"] : '';
		$user_ID = get_current_user_id();		  
		$selected_user = get_userdata( $user_ID );
		$user_customer_id = get_user_meta($user_ID, 'wp_optimizemember_subscr_cid');

		//Dev/Test
		//$exclude_user_ids = array(32); //user_id 33 - infocode.com
		//Production/Live
		$exclude_user_ids = array(32, 37); //user_id 37 - mattejslo@gmail.com | 32 - zdeshkogmail.com | - support@cashswimmers.com

 
		if (!class_exists('Stripe')) {
			require_once ABSPATH.'wp-content/plugins/optimizeMember/optimizeMember-pro/includes/classes/gateways/stripe/stripe-sdk/lib/Stripe.php';

			Stripe::setApiKey('sk_test_XqQdQBT9MaHLUBaRnNt2SXbl');
			Stripe::setApiVersion('2015-02-18');
		}	


		$update_arr  = array();

		if( 'update' === $this->current_action() ) {

			//$seals = implode(', ', $_GET['seal']);
			//$seal_language = implode(', ', $_GET['textLanguage']);
			//echo  '<br />';
			//print_r($seal_language);

			foreach ($_GET['seal'] as $seal_value) {

				$update_seal_language_arr[] = array('id' => $seal_value, 'seal_language' => $_GET['textLanguage'][$seal_value]);

			}

			for( $i = 0; $i < sizeof($update_seal_language_arr); $i++ ){

				$web_seal_update = $wpdb->update( 
					$table,
					array( 
						'seal_language' => $update_seal_language_arr[$i]['seal_language'],	// string
					),	 
					array( 'id' => $update_seal_language_arr[$i]['id'] ),
					array( 
						'%s',
					),
					array( '%d' )
				);

			}		

			wp_die('Successful!');

		}


		if( 'activate' === $this->current_action() ) {
			
        	$seals = implode(', ', $_GET['seal']);
        	
        	//Get seal status that's not active
			$web_seals = $wpdb->get_results( 
				"
				SELECT * 
				FROM $table								
				WHERE id IN ($seals)
				",
				ARRAY_A
			);    

			//wp_die(count($web_seals)); 

			for( $i = 0; $i < sizeof($web_seals); $i++ ){   				

				$id = $web_seals[$i]["id"];
				$user_id = $web_seals[$i]["user_id"];					
				$seal_status = $web_seals[$i]["seal_status"];
				$seal_domain = $web_seals[$i]["seal_domain"];
				$seal_type = $web_seals[$i]["seal_type"];
				$seal_language = $web_seals[$i]["seal_language"];
				$is_additional_domain = $web_seals[$i]["is_additional_domain"];
				$seal_coupon_code = unserialize(base64_decode($web_seals[$i]['seal_coupon_code']));		
				$updated_at	= date('Y-m-d H:i:s');
				$user_customer_id = get_user_meta($user_id, 'wp_optimizemember_subscr_cid');

				$web_seal_update = $wpdb->update( 
					$table,
					array( 
						'seal_status' => 'active',	// string
					),	 
					array( 'id' => $id ),
					array( 
						'%s',
					),
					array( '%d' )
				);

				if ( !in_array($user_id, $exclude_user_ids) ) {

					//Charge each badge using payment api
					if( $web_seal_update === 1 ) {

						$charge_web_seals[] = array(
							'id' => $id,
							'user_id' => $user_id,
							'seal_status' => $seal_status,
							'seal_domain' => $seal_domain,
							'seal_type' => $seal_type,
							'seal_language' => $seal_language,
							'is_additional_domain' => $is_additional_domain,
							'seal_coupon_code' => $seal_coupon_code,
							'user_customer_id' => $user_customer_id						
						);

					} else {

						$charge_web_seals[] = array(
							'id' => $id,
							'user_id' => $user_id,
							'seal_status' => $seal_status,
							'seal_domain' => $seal_domain,
							'seal_type' => $seal_type,
							'seal_language' => $seal_language,
							'is_additional_domain' => $is_additional_domain,							
							'seal_coupon_code' => $seal_coupon_code,
							'user_customer_id' => $user_customer_id							
						);

					}

				}

			}



			//wp_die(count($charge_web_seals));
			if( !empty($charge_web_seals) ) {

				for( $i = 0; $i < sizeof($charge_web_seals); $i++ ) {

					$customer_id = $charge_web_seals[$i]["user_customer_id"][0];


					//Array ( [one-time setup] => freesetup [monthly service] => dqpz ) 1
					//$seal_coupon_code_arr = unserialize(base64_decode($web_seals['seal_coupon_code']));

					//wp_die(print_r($seal_coupon_code_arr));

					if(!empty($charge_web_seals[$i]["seal_coupon_code"])) {

						//foreach($seal_coupon_code_arr as $seal_coupon_code_key => $seal_coupon_code_value) {}

						//print_r($charge_web_seals[$i]["seal_coupon_code"]["one-time setup"]);
						
						// With freesetup, dqpz
						// Plan Name: Seal Basic Monthly Fee with "dqpz" Coupon 
						// Plan ID: seal_basic_monthly_fee_with_dqpz_coupon 
						// Plan Price: $29.95 USD/month
						if( ( isset($charge_web_seals[$i]["seal_coupon_code"]["one-time setup"] ) && trim($charge_web_seals[$i]["seal_coupon_code"]["one-time setup"]) === 'freesetup' ) &&
							( isset($charge_web_seals[$i]["seal_coupon_code"]["monthly service"] ) && trim($charge_web_seals[$i]["seal_coupon_code"]["monthly service"]) === 'dqpz' ) ) {

							//print_r($charge_web_seals[$i]["seal_coupon_code"]);
							//wp_die('freesetup and dqpz');

							$plan_id = 'seal_basic_monthly_fee_with_dqpz_coupon';


							// Create a subscription
							$customer     = Stripe_Customer::retrieve($customer_id);
							$subscription = $customer->subscriptions->create(array('plan' => $plan_id));

							if (isset($subscription->id)) {				
								update_user_meta($user_id, 'wp_optimizemember_subscr_id', $subscription->id);
								//add_user_meta($new_user_id, 'wp_optimizemember_custom_fields', $meta_value);

								$wpdb->update( 
									$table,
									array( 
										'subscription_id' => $subscription->id,	// string
									),	 
									array( 'id' => $charge_web_seals[$i]["id"] ),
									array( 
										'%s',
									),
									array( '%d' )
								);	
														
							}							

							/*echo '<pre>';
							print_r($customer);					
							echo '</pre>'; 					
							echo '<br /><br /><br />';
							echo '<pre>';
							print_r($subscription);					
							echo '</pre>'; */

						}


						// With freesetup
						// Plan Name: Seal Basic Monthly Fee
						// Plan ID: seal_basic_monthly_fee
						// Plan Price: $49.95 USD/month
						elseif( ( isset($charge_web_seals[$i]["seal_coupon_code"]["one-time setup"] ) && trim($charge_web_seals[$i]["seal_coupon_code"]["one-time setup"]) === 'freesetup' ) ) {

							//print_r($charge_web_seals[$i]["seal_coupon_code"]);
							//wp_die('freesetup');

							$plan_id = 'seal_basic_monthly_fee';


							// Create a subscription
							$customer     = Stripe_Customer::retrieve($customer_id);
							$subscription = $customer->subscriptions->create(array('plan' => $plan_id));

							if (isset($subscription->id)) {				
								update_user_meta($user_id, 'wp_optimizemember_subscr_id', $subscription->id);
								//add_user_meta($new_user_id, 'wp_optimizemember_custom_fields', $meta_value);

								$wpdb->update( 
									$table,
									array( 
										'subscription_id' => $subscription->id,	// string
									),	 
									array( 'id' => $charge_web_seals[$i]["id"] ),
									array( 
										'%s',
									),
									array( '%d' )
								);								
							}						

							/*echo '<pre>';
							print_r($customer);					
							echo '</pre>'; 					
							echo '<br /><br /><br />';
							echo '<pre>';
							print_r($subscription);					
							echo '</pre>';*/

						}						

						//with dqpz
						// Plan Name: Seal Basic Monthly Fee with "dqpz" Coupon 
						// Plan ID: seal_basic_monthly_fee_with_dqpz_coupon 
						// Plan Price: $29.95 USD/month					
						elseif( ( isset($charge_web_seals[$i]["seal_coupon_code"]["monthly service"] ) && trim($charge_web_seals[$i]["seal_coupon_code"]["monthly service"]) === 'dqpz' ) ) {

							//print_r($charge_web_seals[$i]["seal_coupon_code"]);
							//wp_die('dqpz');

							$plan_id = 'seal_basic_monthly_fee_with_dqpz_coupon';


							// Create a subscription
							$customer     = Stripe_Customer::retrieve($customer_id);
							$subscription = $customer->subscriptions->create(array('plan' => $plan_id));

							if (isset($subscription->id)) {				
								update_user_meta($user_id, 'wp_optimizemember_subscr_id', $subscription->id);
								//add_user_meta($new_user_id, 'wp_optimizemember_custom_fields', $meta_value);

								$wpdb->update( 
									$table,
									array( 
										'subscription_id' => $subscription->id,	// string
									),	 
									array( 'id' => $charge_web_seals[$i]["id"] ),
									array( 
										'%s',
									),
									array( '%d' )
								);								
							}						

							/*echo '<pre>';
							print_r($customer);					
							echo '</pre>'; 					
							echo '<br /><br /><br />';
							echo '<pre>';
							print_r($subscription);					
							echo '</pre>';*/

						}

					} else { // Empty Seal Coupon Code

						if( isset($charge_web_seals[$i]["is_additional_domain"]) && 0 === (int) $charge_web_seals[$i]["is_additional_domain"]) {
						
							$plan_id = 'seal_basic_monthly_fee';
							/*$description = "One-Time Setup Fee (".$charge_web_seals[$i]["seal_domain"].")";

							// Charge the Customer
							$customer_charge = Stripe_Charge::create(array(
							  "amount" => 9995, // 99.95 One time Setup Fee
							  "currency" => "usd",
							  "customer" => $customer_id,
							  "description" => $description
							  )		  
							);*/

							//Assign a Subscription - recurring payment of 49.95
							$customer     = Stripe_Customer::retrieve($customer_id);
							$subscription = $customer->subscriptions->create(array('plan' => $plan_id));	

							if (isset($subscription->id)) {				
								update_user_meta($user_id, 'wp_optimizemember_subscr_id', $subscription->id);
								//add_user_meta($new_user_id, 'wp_optimizemember_custom_fields', $meta_value);

								$wpdb->update( 
									$table,
									array( 
										'subscription_id' => $subscription->id,	// string
									),	 
									array( 'id' => $charge_web_seals[$i]["id"] ),
									array( 
										'%s',
									),
									array( '%d' )
								);							
							}					

							/*echo '<pre>';
							print_r($customer);					
							echo '</pre>'; 					
							echo '<br /><br /><br />';
							echo '<pre>';
							print_r($subscription);					
							echo '</pre>';*/			

						} elseif( isset($charge_web_seals[$i]["is_additional_domain"]) && 1 === (int) $charge_web_seals[$i]["is_additional_domain"]) {


							//$plan_id = 'additional_domain_monthly_charges';
							$plan_id = 'additional_domain_monthly_charges_4995';

							// Create a subscription
							$customer     = Stripe_Customer::retrieve($customer_id);
							$subscription = $customer->subscriptions->create(array('plan' => $plan_id));

							if (isset($subscription->id)) {				
								update_user_meta($user_id, 'wp_optimizemember_subscr_id', $subscription->id);
								//add_user_meta($new_user_id, 'wp_optimizemember_custom_fields', $meta_value);

								$wpdb->update( 
									$table,
									array( 
										'subscription_id' => $subscription->id,	// string
									),	 
									array( 'id' => $charge_web_seals[$i]["id"] ),
									array( 
										'%s',
									),
									array( '%d' )
								);								
							}						

							/*echo '<pre>';
							print_r($customer);					
							echo '</pre>'; 					
							echo '<br /><br /><br />';
							echo '<pre>';
							print_r($subscription);					
							echo '</pre>';*/ 


						}

					}
						
				}

				wp_die('Successful!');

			} else {

				wp_die('Declined!');

			}

		
		}	


		if( 'activate with dqpz' === $this->current_action() ) {

       		$seals = implode(', ', $_GET['seal']);
        	
        	//Get seal status that's not active
			$web_seals = $wpdb->get_results( 
				"
				SELECT * 
				FROM $table								
				WHERE id IN ($seals)
				",
				ARRAY_A
			);    

			//wp_die(count($web_seals)); 

			for( $i = 0; $i < sizeof($web_seals); $i++ ){   				

				$id = $web_seals[$i]["id"];
				$user_id = $web_seals[$i]["user_id"];					
				$seal_status = $web_seals[$i]["seal_status"];
				$seal_domain = $web_seals[$i]["seal_domain"];
				$seal_type = $web_seals[$i]["seal_type"];
				$seal_language = $web_seals[$i]["seal_language"];
				$is_additional_domain = $web_seals[$i]["is_additional_domain"];
				$seal_coupon_code = unserialize(base64_decode($web_seals[$i]['seal_coupon_code']));		
				$updated_at	= date('Y-m-d H:i:s');
				$user_customer_id = get_user_meta($user_id, 'wp_optimizemember_subscr_cid');

				$web_seal_update = $wpdb->update( 
					$table,
					array( 
						'seal_status' => 'active',	// string
					),	 
					array( 'id' => $id ),
					array( 
						'%s',
					),
					array( '%d' )
				);

				if ( !in_array($user_id, $exclude_user_ids) ) {

					//Charge each badge using payment api
					if( $web_seal_update === 1 ) {

						$charge_web_seals[] = array(
							'id' => $id,
							'user_id' => $user_id,
							'seal_status' => $seal_status,
							'seal_domain' => $seal_domain,
							'seal_type' => $seal_type,
							'seal_language' => $seal_language,
							'is_additional_domain' => $is_additional_domain,
							'seal_coupon_code' => $seal_coupon_code,
							'user_customer_id' => $user_customer_id						
						);

					} else {

						$charge_web_seals[] = array(
							'id' => $id,
							'user_id' => $user_id,
							'seal_status' => $seal_status,
							'seal_domain' => $seal_domain,
							'seal_type' => $seal_type,
							'seal_language' => $seal_language,
							'is_additional_domain' => $is_additional_domain,							
							'seal_coupon_code' => $seal_coupon_code,
							'user_customer_id' => $user_customer_id							
						);

					}

				}

			}


			//wp_die(count($charge_web_seals));
			if( !empty($charge_web_seals) ) {			
			
				for( $i = 0; $i < sizeof($charge_web_seals); $i++ ) {

					$customer_id = $charge_web_seals[$i]["user_customer_id"][0];

					//Array ( [one-time setup] => freesetup [monthly service] => dqpz ) 1
					//$seal_coupon_code_arr = unserialize(base64_decode($web_seals['seal_coupon_code']));

					//wp_die(print_r($seal_coupon_code_arr));

					if(!empty($charge_web_seals[$i]["seal_coupon_code"])) {

						//foreach($seal_coupon_code_arr as $seal_coupon_code_key => $seal_coupon_code_value) {}

						//print_r($charge_web_seals[$i]["seal_coupon_code"]["one-time setup"]);

						// With freesetup
						// Plan Name: Seal Basic Monthly Fee
						// Plan ID: seal_basic_monthly_fee
						// Plan Price: $49.95 USD/month
						if( ( isset($charge_web_seals[$i]["seal_coupon_code"]["one-time setup"] ) && trim($charge_web_seals[$i]["seal_coupon_code"]["one-time setup"]) === 'freesetup' ) ) {

							//print_r($charge_web_seals[$i]["seal_coupon_code"]);
							//wp_die('dqpz');

							$plan_id = 'seal_basic_monthly_fee_with_dqpz_coupon';


							// Create a subscription
							$customer     = Stripe_Customer::retrieve($customer_id);
							$subscription = $customer->subscriptions->create(array('plan' => $plan_id));

							if (isset($subscription->id)) {				
								update_user_meta($user_id, 'wp_optimizemember_subscr_id', $subscription->id);
								//add_user_meta($new_user_id, 'wp_optimizemember_custom_fields', $meta_value);

								$wpdb->update( 
									$table,
									array( 
										'subscription_id' => $subscription->id,	// string
									),	 
									array( 'id' => $charge_web_seals[$i]["id"] ),
									array( 
										'%s',
									),
									array( '%d' )
								);								
							}						

							/*echo '<pre>';
							print_r($customer);					
							echo '</pre>'; 					
							echo '<br /><br /><br />';
							echo '<pre>';
							print_r($subscription);					
							echo '</pre>';*/

						}				

					} else { // Empty Seal Coupon Code
						
						if( isset($charge_web_seals[$i]["is_additional_domain"]) && 0 === (int) $charge_web_seals[$i]["is_additional_domain"]) {

							//print_r($charge_web_seals[$i]["seal_coupon_code"]);
							//wp_die('dqpz');

							$plan_id = 'seal_basic_monthly_fee_with_dqpz_coupon';


							// Create a subscription
							$customer     = Stripe_Customer::retrieve($customer_id);
							$subscription = $customer->subscriptions->create(array('plan' => $plan_id));

							if (isset($subscription->id)) {				
								update_user_meta($user_id, 'wp_optimizemember_subscr_id', $subscription->id);
								//add_user_meta($new_user_id, 'wp_optimizemember_custom_fields', $meta_value);

								$wpdb->update( 
									$table,
									array( 
										'subscription_id' => $subscription->id,	// string
									),	 
									array( 'id' => $charge_web_seals[$i]["id"] ),
									array( 
										'%s',
									),
									array( '%d' )
								);								
							}						

							/*echo '<pre>';
							print_r($customer);					
							echo '</pre>'; 					
							echo '<br /><br /><br />';
							echo '<pre>';
							print_r($subscription);					
							echo '</pre>';*/ 	

						} elseif( isset($charge_web_seals[$i]["is_additional_domain"]) && 1 === (int) $charge_web_seals[$i]["is_additional_domain"]) {

							/*$web_seal_update = $wpdb->update( 
								$table,
								array( 
									'seal_status' => 'pending',	// string
								),	 
								array( 'id' => $charge_web_seals[$i]["id"] ),
								array( 
									'%s',
								),
								array( '%d' )
							);*/

							//wp_die('This domain is additional domain - Cannot process this request');

							//$plan_id = 'additional_domain_monthly_charges';
							$plan_id = 'seal_basic_monthly_fee_with_dqpz_coupon';

							// Create a subscription
							$customer     = Stripe_Customer::retrieve($customer_id);
							$subscription = $customer->subscriptions->create(array('plan' => $plan_id));

							if (isset($subscription->id)) {				
								update_user_meta($user_id, 'wp_optimizemember_subscr_id', $subscription->id);
								//add_user_meta($new_user_id, 'wp_optimizemember_custom_fields', $meta_value);

								$wpdb->update( 
									$table,
									array( 
										'subscription_id' => $subscription->id,	// string
									),	 
									array( 'id' => $charge_web_seals[$i]["id"] ),
									array( 
										'%s',
									),
									array( '%d' )
								);								
							}	

								
						}					

					}

						

				}

				wp_die('Successful!');

			} else {

				wp_die('Declined!');

			}
		
		}


		if( 'deactivate' === $this->current_action() ) {

			$seals = implode(', ', $_GET['seal']);
        	
        	//Get seal status that's not active
			$web_seals = $wpdb->get_results( 
				"
				SELECT * 
				FROM $table								
				WHERE id IN ($seals)
				AND seal_status = 'active'
				",
				ARRAY_A
			);    

			//print_r($web_seals["seal_domain"]);
			//wp_die(count($web_seals)); 

			for( $i = 0; $i < sizeof($web_seals); $i++ ){   				

				$id = $web_seals[$i]["id"];
				$user_id = $web_seals[$i]["user_id"];					
				$seal_status = $web_seals[$i]["seal_status"];
				$seal_domain = $web_seals[$i]["seal_domain"];
				$seal_type = $web_seals[$i]["seal_type"];
				$seal_language = $web_seals[$i]["seal_language"];
				$is_additional_domain = $web_seals[$i]["is_additional_domain"];
				$seal_coupon_code = unserialize(base64_decode($web_seals[$i]['seal_coupon_code']));		
				$updated_at	= date('Y-m-d H:i:s');
				$user_customer_id = get_user_meta($user_id, 'wp_optimizemember_subscr_cid');
				$user_subscription_id = $web_seals[$i]["subscription_id"];


				$web_seal_update = $wpdb->update( 
					$table,
					array( 
						'seal_status' => 'inactive',	// string
					),	 
					array( 'id' => $id ),
					array( 
						'%s',
					),
					array( '%d' )
				);

				if ( !in_array($user_id, $exclude_user_ids) ) {

					//Charge each badge using payment api
					if( $web_seal_update === 1 ) {

						$charge_web_seals[] = array(
							'id' => $id,
							'user_id' => $user_id,
							'seal_status' => $seal_status,
							'seal_domain' => $seal_domain,
							'seal_type' => $seal_type,
							'seal_language' => $seal_language,
							'is_additional_domain' => $is_additional_domain,
							'seal_coupon_code' => $seal_coupon_code,
							'user_customer_id' => $user_customer_id,	
							'user_subscription_id' => $user_subscription_id						
						);

					} else {

						$charge_web_seals[] = array(
							'id' => $id,
							'user_id' => $user_id,
							'seal_status' => $seal_status,
							'seal_domain' => $seal_domain,
							'seal_type' => $seal_type,
							'seal_language' => $seal_language,
							'is_additional_domain' => $is_additional_domain,							
							'seal_coupon_code' => $seal_coupon_code,
							'user_customer_id' => $user_customer_id,
							'user_subscription_id' => $user_subscription_id														
						);

					}

				}

			}

			if( !empty($charge_web_seals) ) {

				for( $i = 0; $i < sizeof($charge_web_seals); $i++ ) {

					echo $customer_id = $charge_web_seals[$i]["user_customer_id"][0];
					$subscription_id = $charge_web_seals[$i]["user_subscription_id"];

					$customer     = Stripe_Customer::retrieve($customer_id);
					$subscription = $customer->subscriptions->retrieve($subscription_id);				
					//$subscription     = Stripe_Subscription::retrieve($subscription_id);
					$subscription->cancel();
					//s$subscription->cancel(array('at_period_end' => true));
	
					//print_r($subscription);

				}

				wp_die('Successful!');

			}


		}

    }


    /** ************************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     * 
     * @global WPDB $wpdb
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/
    function prepare_items() {
        global $wpdb; //This is used only if making any database queries

        $table = $wpdb->prefix . 'web_seals';
      	//$user_ID = isset($_REQUEST["user"]) ? $_REQUEST["user"] : '';
		$user_ID = get_current_user_id();
		$selected_user = get_userdata($user_ID);      	
		

        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 15;
        
        
        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        
        /**
         * REQUIRED. Finally, we build an array to be used by the class for column 
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        
        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
        $this->process_bulk_action();
        
        
        /**
         * Instead of querying a database, we're going to fetch the example data
         * property we created for use in this plugin. This makes this example 
         * package slightly different than one you might build on your own. In 
         * this example, we'll be using array manipulation to sort and paginate 
         * our data. In a real-world implementation, you will probably want to 
         * use sort and pagination data to build a custom query instead, as you'll
         * be able to use your precisely-queried data immediately.
         */
        //$data = $this->example_data;

		/*if( !empty($web_seals) && $selected_user->roles[0] === 'administrator' ) {
			
			echo 'testing 3';
			$data = $wpdb->get_results( 
				"
				SELECT * 
				FROM $table				
				",
				ARRAY_A
			);

		}	

		if( empty($web_seals) && $selected_user->roles[0] === 'doctor_verified_reviewer' ) {
			
			echo 'testing 3';
			$data = $wpdb->get_results( 
				"
				SELECT * 
				FROM $table				
				",
				ARRAY_A
			);

		}  */

		$data = $wpdb->get_results( 
			"
			SELECT * 
			FROM $table				
			",
			ARRAY_A
		);      
                
        
        /**
         * This checks for sorting input and sorts the data in our array accordingly.
         * 
         * In a real-world situation involving a database, you would probably want 
         * to handle sorting by passing the 'orderby' and 'order' values directly 
         * to a custom query. The returned data will be pre-sorted, and this array
         * sorting technique would be unnecessary.
         */
        function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'seal_status'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
        usort($data, 'usort_reorder');
        
        
        /***********************************************************************
         * ---------------------------------------------------------------------
         * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
         * 
         * In a real-world situation, this is where you would place your query.
         *
         * For information on making queries in WordPress, see this Codex entry:
         * http://codex.wordpress.org/Class_Reference/wpdb
         * 
         * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
         * ---------------------------------------------------------------------
         **********************************************************************/
        
                
        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently 
         * looking at. We'll need this later, so you should always include it in 
         * your own package classes.
         */
        $current_page = $this->get_pagenum();
        
        /**
         * REQUIRED for pagination. Let's check how many items are in our data array. 
         * In real-world use, this would be the total number of items in your database, 
         * without filtering. We'll need this later, so you should always include it 
         * in your own package classes.
         */
        $total_items = count($data);
        
        
        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to 
         */
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        
        
        
        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where 
         * it can be used by the rest of the class.
         */
        $this->items = $data;
        
        
        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }


}


//Additional Menus

add_action( 'admin_menu', 'doctor_verified_plugin_menu' );

function doctor_verified_plugin_menu() {
	add_menu_page( 'Doctor Verified', 'Doctor Verified', 'manage_options', 'admin.php?page=doctor_verified', 'doctor_verified_screen', 'none', 4 );
	//add_submenu_page( 'my-top-level-handle', 'Page title', 'Sub-menu title', 'manage_options', 'my-submenu-handle', 'doctor_verified_screen');
}


/*function doctor_verified_plugin_menu() {
	$user_ID = get_current_user_id();
	add_menu_page( 'Doctor Verified', 'Doctor Verified', 'manage_options', 'admin.php?page=doctor_verified&action=seals&amp;user='.$user_ID, 'doctor_verified_screen', 'none', 4 );
	//add_submenu_page( 'my-top-level-handle', 'Page title', 'Sub-menu title', 'manage_options', 'my-submenu-handle', 'doctor_verified_screen');
}*/


/*function doctor_verified_user_row_action_links($actions, $user_object) {
	//$actions['edit_badges'] = "<a class='doctor_verified_ub_edit_seal' href='" . admin_url( "users.php?page=doctor-verified-seals&action=seals&amp;user=$user_object->ID") . "'>" . __( 'Edit Seals', 'doctor_verified_ub' ) . "</a>";
	$actions['edit_badges'] = "<a class='doctor_verified_ub_edit_seal' href='" . admin_url( "profile.php?page=doctor-verified-submenu-page&action=seals&amp;user=$user_object->ID") . "'>" . __( 'Edit Seals', 'doctor_verified_ub' ) . "</a>";
	return $actions;
}*/


/*function doctor_verified_screen(){

    //Create an instance of our package class...
    $doctorverifiedListTable = new Doctor_Verified_List_Table();
    //Fetch, prepare, sort, and filter our data...
    $doctorverifiedListTable->prepare_items();

    $user_ID = get_current_user_id();
    
    ?>
    <div class="wrap">
        
        <div id="icon-users" class="icon32"><br/></div>
        <h2>Review Domain</h2>
        
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="seals-filter" method="get">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <!-- Now we can render the completed list table -->
            <?php $doctorverifiedListTable->display() ?>
        </form>
        
    </div>
    <?php
}*/

function doctor_verified_screen(){

	global $wpdb;
	$table = $wpdb->prefix . 'web_seals';

	$user_id = get_current_user_id();
    
    //Create an instance of our package class...
    $doctorverifiedListTable = new Doctor_Verified_List_Table();
    //Fetch, prepare, sort, and filter our data...
    $doctorverifiedListTable->prepare_items();

	$seal_id = isset($_GET['seal']) ? (int) trim($_GET['seal']) : '';

  	if( isset($_POST["submit"]) ) {
  		//save to db
  		//var_dump($_POST);

		$wpdb->update( 
			$table, 
			array( 
				'verified_products' => trim($_POST['verified_products'])	// string (text)
			), 
			array( 'id' => $seal_id ), 
			array( 
				'%s',	// verified_products
			), 
			array( '%d' ) 
		);

  	}

  	if(!empty($seal_id)) {
  		$get_seal_row = $wpdb->get_row( "SELECT * FROM $table WHERE id = ".$seal_id );
		//var_dump($get_seal_row->verified_products);
	}

    ?>
    <div class="wrap">

        <?php if( (isset($_GET['action']) && $_GET['action'] == 'edit') && isset($_GET['seal']) ) : ?>

	        <div id="icon-users" class="icon32"><br/></div>
	        <h2>Review Domain</h2>

	        <?php 
	        	//print_r($_GET);  
	        	
	        	$seal = $_GET["seal"];
	        	//$user_id = $_GET["user"];

	        ?>
	        
	        <!--form action="<?php //echo admin_url( 'profile.php?page=doctor-verified-submenu-page&action=edit&amp;seal='.$seal.'&amp;user='.$user_id); ?>"  method="post" name="frmupdate">
	        	<input type="submit" value="Submit">
	        </form-->

	       		<?php //echo admin_url( 'admin.php%3Fpage%3Doctor_verified&amp;action=edit&amp;seal='.$seal.'&amp;user='.$user_id); ?>

				<form action="" method="post">
				  <fieldset>
				    <legend>Verified Products:</legend>
				    <textarea name="verified_products" rows="10" cols="30"><?php echo isset($get_seal_row->verified_products) ? $get_seal_row->verified_products : "Verified Products"; ?> </textarea><br />
				    <input type="submit" name="submit" value="Submit"> 
				    <a href="<?php echo admin_url( 'admin.php?page=admin.php?page=doctor_verified'); ?>">Back</a>
				  </fieldset>
				</form>

    	<?php else : ?>
 
	        <div id="icon-users" class="icon32"><br/></div>
	        <h2>Review Domain</h2>
	        
	        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
	        <form id="seals-filter" method="get">
	            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
	            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
	            <!-- Now we can render the completed list table -->
	            <?php $doctorverifiedListTable->display() ?>
	        </form>

    	<?php endif; ?>
        
    </div>
    <?php
}


//=================================================================================================================================================================


class Doctor_Verified_User_List_Table extends WP_List_Table {
    
    /** ************************************************************************
     * Normally we would be querying data from a database and manipulating that
     * for use in your list table. For this example, we're going to simplify it
     * slightly and create a pre-built array. Think of this as the data that might
     * be returned by $wpdb->query()
     * 
     * In a real-world scenario, you would make your own custom query inside
     * this class' prepare_items() method.
     * 
     * @var array 
     **************************************************************************/
    /*var $example_data = array(
            array(
                'ID'        => 1,
                'user_id'     => '300',
                'seal_status'    => 'R',
                'seal_domain'  => 'Zach Snyder',
                'seal_issued_on'  => 'Zach Snyder'                
            ),
            array(
                'ID'        => 2,
                'user_id'     => 'Eyes Wide Shut',
                'seal_status'    => 'R',
                'seal_domain'  => 'Stanley Kubrick',
                'seal_issued_on'  => 'Zach Snyder'
            ),
            array(
                'ID'        => 3,
                'user_id'     => 'Moulin Rouge!',
                'seal_status'    => 'PG-13',
                'seal_domain'  => 'Baz Luhrman',
                'seal_issued_on'  => 'Zach Snyder'
            ),
            array(
                'ID'        => 4,
                'user_id'     => 'Snow White',
                'seal_status'    => 'G',
                'seal_domain'  => 'Walt Disney',
                'seal_issued_on'  => 'Zach Snyder'
            ),
            array(
                'ID'        => 5,
                'user_id'     => 'Super 8',
                'seal_status'    => 'PG-13',
                'seal_domain'  => 'JJ Abrams',
                'seal_issued_on'  => 'Zach Snyder'
            ),
            array(
                'ID'        => 6,
                'user_id'     => 'The Fountain',
                'seal_status'    => 'PG-13',
                'seal_domain'  => 'Darren Aronofsky',
                'seal_issued_on'  => 'Zach Snyder'
            ),
            array(
                'ID'        => 7,
                'user_id'     => 'Watchmen',
                'seal_status'    => 'R',
                'seal_domain'  => 'Zach Snyder',
                'seal_issued_on'  => 'Zach Snyder'
            ),
            array(
                'ID'        => 8,
                'user_id'     => '2001',
                'seal_status'    => 'G',
                'seal_domain'  => 'Stanley Kubrick',
                'seal_issued_on'  => 'Zach Snyder'
            ),
        );*/


    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'seal',     //singular name of the listed records
            'plural'    => 'seals',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
    }


    /** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title() 
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as 
     * possible. 
     * 
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     * 
     * For more detailed insight into how columns are handled, take a look at 
     * WP_List_Table::single_row_columns()
     * 
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($item, $column_name){
        switch($column_name){
        	case 'user_id':
            case 'seal_status':
            case 'seal_email':             
            case 'seal_domain':
            case 'seal_type':                                                     
            case 'seal_language':
            case 'is_additional_domain':            
            case 'seal_coupon_code':            
            case 'seal_issued_on':
                return $item[$column_name];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }


    /** ************************************************************************
     * Recommended. This is a custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'title'. Every time the class
     * needs to render a column, it first looks for a method named 
     * column_{$column_title} - if it exists, that method is run. If it doesn't
     * exist, column_default() is called instead.
     * 
     * This example also illustrates how to implement rollover actions. Actions
     * should be an associative array formatted as 'slug'=>'link html' - and you
     * will need to generate the URLs yourself. You could even ensure the links
     * 
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_seal_domain($item){
        
        //Build row actions
        $actions = array(
            
            'edit'      => sprintf('<a href="?page=%s&action=%s&seal=%s&user='.$_GET['user'].'">Edit</a>',$_REQUEST['page'],'edit',$item['id']),

            //'edit'      => sprintf('<a href="?page=%s&action=%s&seal=%s">Edit</a>',$_REQUEST['page'],'edit',$item['id']),
            //'delete'    => sprintf('<a href="?page=%s&action=%s&seal=%s">Delete</a>',$_REQUEST['page'],'delete',$item['id']),
        );
       
        //Return the title contents
        //return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/ //$item['seal_domain'],
            /*$2%s*/ //$item['id'],
            /*$3%s*/ //$this->row_actions($actions)
        //);


         return sprintf('%1$s %2$s', $item['seal_domain'], $this->row_actions($actions) );
    }


	function column_seal_language( $item ) {

		//'<select name="textLanguage" id="textLanguage" class="input-style"><option value="en" selected="selected">English (United States)</option><option value="ary">العربية المغربية</option><option value="ar">العربية</option><option value="az">Azərbaycan dili</option><option value="azb">گؤنئی آذربایجان</option><option value="bg_BG">Български</option><option value="bn_BD">বাংলা</option><option value="bs_BA">Bosanski</option><option value="ca">Català</option><option value="ceb">Cebuano</option><option value="cs_CZ">Čeština&lrm;</option><option value="cy">Cymraeg</option><option value="da_DK">Dansk</option><option value="de_CH">Deutsch (Schweiz)</option><option value="de_CH_informal">Deutsch (Schweiz, Du)</option><option value="de_DE">Deutsch</option><option value="de_DE_formal">Deutsch (Sie)</option><option value="el">Ελληνικά</option><option value="en_GB">English (UK)</option><option value="en_ZA">English (South Africa)</option><option value="en_NZ">English (New Zealand)</option><option value="en_CA">English (Canada)</option><option value="en_AU">English (Australia)</option><option value="eo">Esperanto</option><option value="es_VE">Español de Venezuela</option><option value="es_MX">Español de México</option><option value="es_ES">Español</option><option value="es_CO">Español de Colombia</option><option value="es_CL">Español de Chile</option><option value="es_AR">Español de Argentina</option><option value="es_GT">Español de Guatemala</option><option value="es_PE">Español de Perú</option><option value="et">Eesti</option><option value="eu">Euskara</option><option value="fa_IR">فارسی</option><option value="fi">Suomi</option><option value="fr_BE">Français de Belgique</option><option value="fr_CA">Français du Canada</option><option value="fr_FR">Français</option><option value="gd">Gàidhlig</option><option value="gl_ES">Galego</option><option value="haz">هزاره گی</option><option value="he_IL">עִבְרִית</option><option value="hi_IN">हिन्दी</option><option value="hr">Hrvatski</option><option value="hu_HU">Magyar</option><option value="hy">Հայերեն</option><option value="id_ID">Bahasa Indonesia</option><option value="is_IS">Íslenska</option><option value="it_IT">Italiano</option><option value="ja">日本語</option><option value="ka_GE">ქართული</option><option value="ko_KR">한국어</option><option value="lt_LT">Lietuvių kalba</option><option value="mk_MK">Македонски јазик</option><option value="mr">मराठी</option><option value="ms_MY">Bahasa Melayu</option><option value="my_MM">ဗမာစာ</option><option value="nb_NO">Norsk bokmål</option><option value="nl_NL">Nederlands</option><option value="nl_NL_formal">Nederlands (Formeel)</option><option value="nn_NO">Norsk nynorsk</option><option value="oci">Occitan</option><option value="pl_PL">Polski</option><option value="ps">پښتو</option><option value="pt_PT">Português</option><option value="pt_BR">Português do Brasil</option><option value="ro_RO">Română</option><option value="ru_RU">Русский</option><option value="sk_SK">Slovenčina</option><option value="sl_SI">Slovenščina</option><option value="sq">Shqip</option><option value="sr_RS">Српски језик</option><option value="sv_SE">Svenska</option><option value="th">ไทย</option><option value="tl">Tagalog</option><option value="tr_TR">Türkçe</option><option value="ug_CN">Uyƣurqə</option><option value="uk">Українська</option><option value="vi">Tiếng Việt</option><option value="zh_CN">简体中文</option><option value="zh_TW">繁體中文</option></select>';	

		/*$languages = array( "en" => "English (United States)", "ary" => "العربية المغربية", "ar" => "العربية", "az" => "Azərbaycan dili",
							"azb" => "گؤنئی آذربایجان", "bg_BG" => "Български", "bn_BD" => "বাংলা", "bs_BA" => "Bosanski",
							"ca" => "Català", "ceb" => "Cebuano", "cs_CZ" => "Čeština&lrm;", "cy" => "Cymraeg",
							"da_DK" => "Dansk", "de_CH" => "Deutsch (Schweiz)", "de_CH_informal" => "Deutsch (Schweiz, Du)", "de_DE" => "Deutsch",
							"de_DE_formal" => "Deutsch (Sie)", "el" => "Ελληνικά", "en_GB" => "English (UK)", "en_ZA" => "English (South Africa)",
							"en_NZ" => "English (New Zealand)", "en_CA" => "English (Canada)", "en_AU" => "English (Australia)", "eo" => "Esperanto",
							"es_VE" => "Español de Venezuela", "es_MX" => "Español de México", "es_ES" => "Español", "es_CO" => "Español de Colombia",
							"es_CL" => "Español de Chile", "es_AR" => "Español de Argentina", "es_GT" => "Español de Guatemala", "es_PE" => "Español de Perú",
							"et" => "Eesti", "eu" => "Euskara", "fa_IR" => "فارسی", "fi" => "Suomi",
							"fr_BE" => "Français de Belgique", "fr_CA" => "Français du Canada", "fr_FR" => "Français", "gd" => "Gàidhlig",
							"gl_ES" => "Galego", "haz" => "هزاره گی", "he_IL" => "עִבְרִית", "hi_IN" => "हिन्दी",
							"hr" => "Hrvatski", "hu_HU" => "Magyar", "hy" => "Հայերեն", "id_ID" => "Bahasa Indonesia",
							"is_IS" => "Íslenska", "it_IT" => "Italiano", "ja" => "日本語", "ka_GE" => "ქართული",
							"ko_KR" => "한국어", "lt_LT" => "Lietuvių kalba", "mk_MK" => "Македонски јазик", "mr" => "मराठी",
							"ms_MY" => "Bahasa Melayu", "my_MM" => "ဗမာစာ", "nb_NO" => "Norsk bokmål", "nl_NL" => "Nederlands",
							"nl_NL_formal" => "Nederlands (Formeel)", "nn_NO" => "Norsk nynorsk", "oci" => "Occitan", "pl_PL" => "Polski",
							"ps" => "پښتو", "pt_PT" => "Português", "pt_BR" => "Português do Brasil", "ro_RO" => "Română", 
							"ru_RU" => "Русский", "sk_SK" => "Slovenčina", "sl_SI" => "Slovenščina", "sq" => "Shqip",
							"sr_RS" => "Српски језик", "sv_SE" => "Svenska", "th" => "ไทย", "tl" => "Tagalog",
							"tr_TR" => "Türkçe", "ug_CN" => "Uyƣurqə", "uk" => "Українська", "vi" => "Tiếng Việt",
							"zh_CN" => "简体中文", "zh_TW" => "繁體中文");	*/

		/*$languages = array("en" => 'english (en)', "cs_CZ" => 'cestina (cs_CZ)', "da_DK" => 'danish (da_DK)', "de_DE" => 'german (de_DE)',
		"es" => 'spanish (es)', "fr" => 'french (fr)', "hr" => 'croatian/hrvatski (hr)', "hu_HU" => 'hungarian (hu_HU)',
		"it_IT" => 'italian (it_IT)', "jp" => 'japan (jp)', "no" => 'norweigian (no)', "nl_NL" => 'netherlands / dutch (nl_NL)',
		"pl_PL" => 'polish (pl_PL)', "pt_PT" => 'portugese (pt_PT)', "sk_SK" => 'slovak (sk_SK)', "sl_SI" => 'slovenian (sl_SI)',
		"sv_SE" => 'swedish (sv_SE)', "tr_TR" => 'turkish (tr_TR)', "zh_CN" => 'chinese (zh_CN)');*/	


		$languages = array("en" => 'English', "cs_CZ" => 'Cestina', "da_DK" => 'Danish', "de_DE" => 'German',
		"es" => 'Spanish', "fr" => 'French', "hr" => 'Croatian', "hu_HU" => 'Hungarian',
		"it_IT" => 'Italian', "jp" => 'Japan', "no" => 'Norweigian', "nl_NL" => 'Netherlands',
		"pl_PL" => 'Polish', "pt_PT" => 'Portugese ', "sk_SK" => 'Slovak', "sl_SI" => 'Slovenian',
		"sv_SE" => 'Swedish', "tr_TR" => 'Turkish', "zh_CN" => 'Chinese');	

		$output = '<select name="textLanguage['.$item['id'].']" id="textLanguage['.$item['id'].']" class="input-style" style="width:119px; font-size:11px;"><option value="en" selected="selected">';

		foreach( $languages as $key => $value) {

			if($key === $item['seal_language']) {
				$output .= '<option value="'.$key.'" selected="selected">'.$value.'</option>';
			} else {
				$output .= '<option value="'.$key.'">'.$value.'</option>';				
			}

		}

		$output .= '</select>';

		return $output;	
	}

    /*function column_is_additional_domain($item) {

    }*/	


	function column_seal_email( $item ) {

		$user_ID = $item["user_id"];
		$user_data = get_userdata($user_ID);		

		return $user_data->user_email;
	}


	function column_seal_coupon_code( $item ) {
		//unserialize(base64_decode($web_seals[$i]["seal_coupon_code"]))
		//return print_r(unserialize(base64_decode($item['seal_coupon_code'])));
		//( [one-time setup] => freesetup [monthly service] => dqpz ) 

		$seal_coupon_code_arr = unserialize(base64_decode($item['seal_coupon_code']));

		if(!empty($seal_coupon_code_arr)) {
			//foreach($seal_coupon_code_arr as $seal_coupon_code_key => $seal_coupon_code_value) {}

			return implode(', ', $seal_coupon_code_arr);
		}

		//return $seal_coupon_code;
	}		    


    /** ************************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['id']                //The value of the checkbox should be the record's id
        );
    }


    /** ************************************************************************
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value 
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     * 
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'user_id' => 'user ID',
            'seal_status'    => 'Status',
            'seal_email'     => 'Email',             
            'seal_domain'     => 'Domain',
            'seal_type'     => 'Type',                                        
            'seal_language'    => 'Lang',
			'is_additional_domain' =>  'Is Additional',          
            'seal_coupon_code'    => 'Coupon/s',
            'seal_issued_on'  => 'Created'
        );
        return $columns;
    }


    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle), 
     * you will need to register it here. This should return an array where the 
     * key is the column that needs to be sortable, and the value is db column to 
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     * 
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items() and sort
     * your data accordingly (usually by modifying your query).
     * 
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
    function get_sortable_columns() {
        $sortable_columns = array(
            'user_id' => array('user_id',false),
            'seal_status'    => array('seal_status',false),
            'seal_email'    => array('seal_email',false),            
            'seal_domain'     => array('seal_domain',false),     //true means it's already sorted
            'seal_type'    => array('seal_type',false),
            'seal_language'    => array('seal_language',false),
            'is_additional_domain'    => array('is_additional_domain',false),
            'seal_coupon_code'    => array('seal_coupon_code',false),
            'seal_issued_on'  => array('seal_issued_on',false)
        );
        return $sortable_columns;
    }



    /** ************************************************************************
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible Title'
     * 
     * If this method returns an empty value, no bulk action will be rendered. If
     * you specify any bulk actions, the bulk actions box will be rendered with
     * the table automatically on display().
     * 
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     * 
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions() {

         if ( current_user_can('administrator') || current_user_can('doctor_verified_reviewer') ) {
	        $actions = array(	            
	            'activate' => 'Activate',
				'activate with dqpz'  => 'Activate with dqpz',
				//'activate with freesetup' => 'Activate with freesetup',
				//'activate with freesetup and dqpz' => 'Activate with freesetup and dqpz',
	            'deactivate'    => 'Deactivate',
	            'update'    => 'Update',           
	            //'delete'    => 'Delete' 
	            'edit' => 'Edit'
	        );
    	}  else {    		
    		$actions = array();
    	}

    	return $actions;

    }


    /** ************************************************************************
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     * 
     * @see $this->prepare_items()
     **************************************************************************/
    function process_bulk_action() {
        
        //Detect when a bulk action is being triggered...
        /* if( 'delete'===$this->current_action() ) {
            wp_die('Items deleted (or they would be if we had items to delete)!');
        }*/

        global $wpdb;
        
    	$table = $wpdb->prefix . 'web_seals';        

      	$user_ID = isset($_REQUEST["user"]) ? $_REQUEST["user"] : '';
		//$user_ID = get_current_user_id();		  
		$selected_user = get_userdata( $user_ID );
		$user_customer_id = get_user_meta($user_ID, 'wp_optimizemember_subscr_cid');

		//Dev/Test
		//$exclude_user_ids = array(32); //user_id 33 - infocode.com
		//Production/Live
		$exclude_user_ids = array(8); //user_id 8 - mattejslo@gmail.com
 
		if (!class_exists('Stripe')) {
			require_once ABSPATH.'wp-content/plugins/optimizeMember/optimizeMember-pro/includes/classes/gateways/stripe/stripe-sdk/lib/Stripe.php';

			Stripe::setApiKey('sk_test_XqQdQBT9MaHLUBaRnNt2SXbl');
			Stripe::setApiVersion('2015-02-18');
		}	


		$update_arr  = array();		

		if( 'update' === $this->current_action() ) {

			$seals = implode(', ', $_GET['seal']);
			//$seals = implode(', ', $_GET['seal']);
			//$seal_language = implode(', ', $_GET['textLanguage']);
			//echo  '<br />';
			
			/*echo '<pre>';
			print_r($_GET['seal']);
			echo '</pre>';*/



			foreach ($_GET['seal'] as $seal_value) {

				$update_seal_language_arr[] = array('id' => $seal_value, 'seal_language' => $_GET['textLanguage'][$seal_value]);

			}

			for( $i = 0; $i < sizeof($update_seal_language_arr); $i++ ){

				$web_seal_update = $wpdb->update( 
					$table,
					array( 
						'seal_language' => $update_seal_language_arr[$i]['seal_language'],	// string
					),	 
					array( 'id' => $update_seal_language_arr[$i]['id'] ),
					array( 
						'%s',
					),
					array( '%d' )
				);

			}		

			wp_die('Successful!');

		}


		if( 'activate' === $this->current_action() ) {
			
        	$seals = implode(', ', $_GET['seal']);
        	
        	//Get seal status that's not active
			$web_seals = $wpdb->get_results( 
				"
				SELECT * 
				FROM $table								
				WHERE id IN ($seals)
				",
				ARRAY_A
			);    

			//wp_die(count($web_seals)); 

			for( $i = 0; $i < sizeof($web_seals); $i++ ){   				

				$id = $web_seals[$i]["id"];
				$user_id = $web_seals[$i]["user_id"];					
				$seal_status = $web_seals[$i]["seal_status"];
				$seal_domain = $web_seals[$i]["seal_domain"];
				$seal_type = $web_seals[$i]["seal_type"];
				$seal_language = $web_seals[$i]["seal_language"];
				$is_additional_domain = $web_seals[$i]["is_additional_domain"];
				$seal_coupon_code = unserialize(base64_decode($web_seals[$i]['seal_coupon_code']));		
				$updated_at	= date('Y-m-d H:i:s');
				$user_customer_id = get_user_meta($user_id, 'wp_optimizemember_subscr_cid');

				$web_seal_update = $wpdb->update( 
					$table,
					array( 
						'seal_status' => 'active',	// string
					),	 
					array( 'id' => $id ),
					array( 
						'%s',
					),
					array( '%d' )
				);

				if ( !in_array($user_id, $exclude_user_ids) ) {

					//Charge each badge using payment api
					if( $web_seal_update === 1 ) {

						$charge_web_seals[] = array(
							'id' => $id,
							'user_id' => $user_id,
							'seal_status' => $seal_status,
							'seal_domain' => $seal_domain,
							'seal_type' => $seal_type,
							'seal_language' => $seal_language,
							'is_additional_domain' => $is_additional_domain,
							'seal_coupon_code' => $seal_coupon_code,
							'user_customer_id' => $user_customer_id						
						);

					} else {

						$charge_web_seals[] = array(
							'id' => $id,
							'user_id' => $user_id,
							'seal_status' => $seal_status,
							'seal_domain' => $seal_domain,
							'seal_type' => $seal_type,
							'seal_language' => $seal_language,
							'is_additional_domain' => $is_additional_domain,							
							'seal_coupon_code' => $seal_coupon_code,
							'user_customer_id' => $user_customer_id							
						);

					}

				}

			}



			//wp_die(count($charge_web_seals));
			if( !empty($charge_web_seals) ) {

				for( $i = 0; $i < sizeof($charge_web_seals); $i++ ) {

					$customer_id = $charge_web_seals[$i]["user_customer_id"][0];


					//Array ( [one-time setup] => freesetup [monthly service] => dqpz ) 1
					//$seal_coupon_code_arr = unserialize(base64_decode($web_seals['seal_coupon_code']));

					//wp_die(print_r($seal_coupon_code_arr));

					if(!empty($charge_web_seals[$i]["seal_coupon_code"])) {

						//foreach($seal_coupon_code_arr as $seal_coupon_code_key => $seal_coupon_code_value) {}

						//print_r($charge_web_seals[$i]["seal_coupon_code"]["one-time setup"]);
						
						// With freesetup, dqpz
						// Plan Name: Seal Basic Monthly Fee with "dqpz" Coupon 
						// Plan ID: seal_basic_monthly_fee_with_dqpz_coupon 
						// Plan Price: $29.95 USD/month
						if( ( isset($charge_web_seals[$i]["seal_coupon_code"]["one-time setup"] ) && trim($charge_web_seals[$i]["seal_coupon_code"]["one-time setup"]) === 'freesetup' ) &&
							( isset($charge_web_seals[$i]["seal_coupon_code"]["monthly service"] ) && trim($charge_web_seals[$i]["seal_coupon_code"]["monthly service"]) === 'dqpz' ) ) {

							//print_r($charge_web_seals[$i]["seal_coupon_code"]);
							//wp_die('freesetup and dqpz');

							$plan_id = 'seal_basic_monthly_fee_with_dqpz_coupon';


							// Create a subscription
							$customer     = Stripe_Customer::retrieve($customer_id);
							$subscription = $customer->subscriptions->create(array('plan' => $plan_id));

							if (isset($subscription->id)) {				
								update_user_meta($user_id, 'wp_optimizemember_subscr_id', $subscription->id);
								//add_user_meta($new_user_id, 'wp_optimizemember_custom_fields', $meta_value);

								$wpdb->update( 
									$table,
									array( 
										'subscription_id' => $subscription->id,	// string
									),	 
									//array( 'id' => $id ),
									array( 'id' => $charge_web_seals[$i]["id"] ),									
									array( 
										'%s',
									),
									array( '%d' )
								);	
														
							}							

							/*echo '<pre>';
							print_r($customer);					
							echo '</pre>'; 					
							echo '<br /><br /><br />';
							echo '<pre>';
							print_r($subscription);					
							echo '</pre>';*/

						}


						// With freesetup
						// Plan Name: Seal Basic Monthly Fee
						// Plan ID: seal_basic_monthly_fee
						// Plan Price: $49.95 USD/month
						elseif( ( isset($charge_web_seals[$i]["seal_coupon_code"]["one-time setup"] ) && trim($charge_web_seals[$i]["seal_coupon_code"]["one-time setup"]) === 'freesetup' ) ) {

							//print_r($charge_web_seals[$i]["seal_coupon_code"]);
							//wp_die('freesetup');

							$plan_id = 'seal_basic_monthly_fee';


							// Create a subscription
							$customer     = Stripe_Customer::retrieve($customer_id);
							$subscription = $customer->subscriptions->create(array('plan' => $plan_id));

							if (isset($subscription->id)) {				
								update_user_meta($user_id, 'wp_optimizemember_subscr_id', $subscription->id);
								//add_user_meta($new_user_id, 'wp_optimizemember_custom_fields', $meta_value);

								$wpdb->update( 
									$table,
									array( 
										'subscription_id' => $subscription->id,	// string
									),	 
									//array( 'id' => $id ),
									array( 'id' => $charge_web_seals[$i]["id"] ),	
									array( 
										'%s',
									),
									array( '%d' )
								);								
							}						

							/*echo '<pre>';
							print_r($customer);					
							echo '</pre>'; 					
							echo '<br /><br /><br />';
							echo '<pre>';
							print_r($subscription);					
							echo '</pre>';*/

						}						

						//with dqpz
						// Plan Name: Seal Basic Monthly Fee with "dqpz" Coupon 
						// Plan ID: seal_basic_monthly_fee_with_dqpz_coupon 
						// Plan Price: $29.95 USD/month					
						elseif( ( isset($charge_web_seals[$i]["seal_coupon_code"]["monthly service"] ) && trim($charge_web_seals[$i]["seal_coupon_code"]["monthly service"]) === 'dqpz' ) ) {

							//print_r($charge_web_seals[$i]["seal_coupon_code"]);
							//wp_die('dqpz');

							$plan_id = 'seal_basic_monthly_fee_with_dqpz_coupon';


							// Create a subscription
							$customer     = Stripe_Customer::retrieve($customer_id);
							$subscription = $customer->subscriptions->create(array('plan' => $plan_id));

							if (isset($subscription->id)) {				
								update_user_meta($user_id, 'wp_optimizemember_subscr_id', $subscription->id);
								//add_user_meta($new_user_id, 'wp_optimizemember_custom_fields', $meta_value);

								$wpdb->update( 
									$table,
									array( 
										'subscription_id' => $subscription->id,	// string
									),	 
									//array( 'id' => $id ),
									array( 'id' => $charge_web_seals[$i]["id"] ),	
									array( 
										'%s',
									),
									array( '%d' )
								);								
							}						

							/*echo '<pre>';
							print_r($customer);					
							echo '</pre>'; 					
							echo '<br /><br /><br />';
							echo '<pre>';
							print_r($subscription);					
							echo '</pre>';*/

						}

					} else { // Empty Seal Coupon Code

						if( isset($charge_web_seals[$i]["is_additional_domain"]) && 0 === (int) $charge_web_seals[$i]["is_additional_domain"]) {
						
							$plan_id = 'seal_basic_monthly_fee';
							/*$description = "One-Time Setup Fee (".$charge_web_seals[$i]["seal_domain"].")";

							// Charge the Customer
							$customer_charge = Stripe_Charge::create(array(
							  "amount" => 9995, // 99.95 One time Setup Fee
							  "currency" => "usd",
							  "customer" => $customer_id,
							  "description" => $description
							  )		  
							);*/

							//Assign a Subscription - recurring payment of 49.95
							$customer     = Stripe_Customer::retrieve($customer_id);
							$subscription = $customer->subscriptions->create(array('plan' => $plan_id));	

							if (isset($subscription->id)) {				
								update_user_meta($user_id, 'wp_optimizemember_subscr_id', $subscription->id);
								//add_user_meta($new_user_id, 'wp_optimizemember_custom_fields', $meta_value);

								$wpdb->update( 
									$table,
									array( 
										'subscription_id' => $subscription->id,	// string
									),	 
									//array( 'id' => $id ),
									array( 'id' => $charge_web_seals[$i]["id"] ),	
									array( 
										'%s',
									),
									array( '%d' )
								);							
							}					

							/*echo '<pre>';
							print_r($customer);					
							echo '</pre>'; 					
							echo '<br /><br /><br />';
							echo '<pre>';
							print_r($subscription);					
							echo '</pre>';*/			

						} elseif( isset($charge_web_seals[$i]["is_additional_domain"]) && 1 === (int) $charge_web_seals[$i]["is_additional_domain"]) {


							//$plan_id = 'additional_domain_monthly_charges';
							$plan_id = 'additional_domain_monthly_charges_4995';


							// Create a subscription
							$customer     = Stripe_Customer::retrieve($customer_id);
							$subscription = $customer->subscriptions->create(array('plan' => $plan_id));

							if (isset($subscription->id)) {				
								update_user_meta($user_id, 'wp_optimizemember_subscr_id', $subscription->id);
								//add_user_meta($new_user_id, 'wp_optimizemember_custom_fields', $meta_value);

								$wpdb->update( 
									$table,
									array( 
										'subscription_id' => $subscription->id,	// string
									),	 
									//array( 'id' => $id ),
									array( 'id' => $charge_web_seals[$i]["id"] ),	
									array( 
										'%s',
									),
									array( '%d' )
								);								
							}						

							/*echo '<pre>';
							print_r($customer);					
							echo '</pre>'; 					
							echo '<br /><br /><br />';
							echo '<pre>';
							print_r($subscription);					
							echo '</pre>'; */


						}

					}

				}

				wp_die('Successful!');

			} else {

				wp_die('Declined!');

			}
		
		}	


		if( 'activate with dqpz' === $this->current_action() ) {

       		$seals = implode(', ', $_GET['seal']);
        	
        	//Get seal status that's not active
			$web_seals = $wpdb->get_results( 
				"
				SELECT * 
				FROM $table								
				WHERE id IN ($seals)
				",
				ARRAY_A
			);    

			//wp_die(count($web_seals)); 

			for( $i = 0; $i < sizeof($web_seals); $i++ ){   				

				$id = $web_seals[$i]["id"];
				$user_id = $web_seals[$i]["user_id"];					
				$seal_status = $web_seals[$i]["seal_status"];
				$seal_domain = $web_seals[$i]["seal_domain"];
				$seal_type = $web_seals[$i]["seal_type"];
				$seal_language = $web_seals[$i]["seal_language"];
				$is_additional_domain = $web_seals[$i]["is_additional_domain"];
				$seal_coupon_code = unserialize(base64_decode($web_seals[$i]['seal_coupon_code']));		
				$updated_at	= date('Y-m-d H:i:s');
				$user_customer_id = get_user_meta($user_id, 'wp_optimizemember_subscr_cid');

				$web_seal_update = $wpdb->update( 
					$table,
					array( 
						'seal_status' => 'active',	// string
					),	 
					array( 'id' => $id ),
					array( 
						'%s',
					),
					array( '%d' )
				);

				if ( !in_array($user_id, $exclude_user_ids) ) {

					//Charge each badge using payment api
					if( $web_seal_update === 1 ) {

						$charge_web_seals[] = array(
							'id' => $id,
							'user_id' => $user_id,
							'seal_status' => $seal_status,
							'seal_domain' => $seal_domain,
							'seal_type' => $seal_type,
							'seal_language' => $seal_language,
							'is_additional_domain' => $is_additional_domain,
							'seal_coupon_code' => $seal_coupon_code,
							'user_customer_id' => $user_customer_id						
						);

					} else {

						$charge_web_seals[] = array(
							'id' => $id,
							'user_id' => $user_id,
							'seal_status' => $seal_status,
							'seal_domain' => $seal_domain,
							'seal_type' => $seal_type,
							'seal_language' => $seal_language,
							'is_additional_domain' => $is_additional_domain,							
							'seal_coupon_code' => $seal_coupon_code,
							'user_customer_id' => $user_customer_id							
						);

					}

				}

			}


			//wp_die(count($charge_web_seals));
			if( !empty($charge_web_seals) ) {			
			
				for( $i = 0; $i < sizeof($charge_web_seals); $i++ ) {

					$customer_id = $charge_web_seals[$i]["user_customer_id"][0];

					//Array ( [one-time setup] => freesetup [monthly service] => dqpz ) 1
					//$seal_coupon_code_arr = unserialize(base64_decode($web_seals['seal_coupon_code']));

					//wp_die(print_r($seal_coupon_code_arr));

					if(!empty($charge_web_seals[$i]["seal_coupon_code"])) {

						//foreach($seal_coupon_code_arr as $seal_coupon_code_key => $seal_coupon_code_value) {}

						//print_r($charge_web_seals[$i]["seal_coupon_code"]["one-time setup"]);

						// With freesetup
						// Plan Name: Seal Basic Monthly Fee
						// Plan ID: seal_basic_monthly_fee
						// Plan Price: $49.95 USD/month
						if( ( isset($charge_web_seals[$i]["seal_coupon_code"]["one-time setup"] ) && trim($charge_web_seals[$i]["seal_coupon_code"]["one-time setup"]) === 'freesetup' ) ) {

							//print_r($charge_web_seals[$i]["seal_coupon_code"]);
							//wp_die('dqpz');

							$plan_id = 'seal_basic_monthly_fee_with_dqpz_coupon';


							// Create a subscription
							$customer     = Stripe_Customer::retrieve($customer_id);
							$subscription = $customer->subscriptions->create(array('plan' => $plan_id));

							if (isset($subscription->id)) {				
								update_user_meta($user_id, 'wp_optimizemember_subscr_id', $subscription->id);
								//add_user_meta($new_user_id, 'wp_optimizemember_custom_fields', $meta_value);

								$wpdb->update( 
									$table,
									array( 
										'subscription_id' => $subscription->id,	// string
									),	 
									//array( 'id' => $id ),
									array( 'id' => $charge_web_seals[$i]["id"] ),	
									array( 
										'%s',
									),
									array( '%d' )
								);								
							}						

							/*echo '<pre>';
							print_r($customer);					
							echo '</pre>'; 					
							echo '<br /><br /><br />';
							echo '<pre>';
							print_r($subscription);					
							echo '</pre>';*/

						}				

					} else { // Empty Seal Coupon Code
						
						if( isset($charge_web_seals[$i]["is_additional_domain"]) && 0 === (int) $charge_web_seals[$i]["is_additional_domain"]) {

							//print_r($charge_web_seals[$i]["seal_coupon_code"]);
							//wp_die('dqpz');

							$plan_id = 'seal_basic_monthly_fee_with_dqpz_coupon';


							// Create a subscription
							$customer     = Stripe_Customer::retrieve($customer_id);
							$subscription = $customer->subscriptions->create(array('plan' => $plan_id));

							if (isset($subscription->id)) {				
								update_user_meta($user_id, 'wp_optimizemember_subscr_id', $subscription->id);
								//add_user_meta($new_user_id, 'wp_optimizemember_custom_fields', $meta_value);

								$wpdb->update( 
									$table,
									array( 
										'subscription_id' => $subscription->id,	// string
									),	 
									//array( 'id' => $id ),
									array( 'id' => $charge_web_seals[$i]["id"] ),	
									array( 
										'%s',
									),
									array( '%d' )
								);								
							}						

							/*echo '<pre>';
							print_r($customer);					
							echo '</pre>'; 					
							echo '<br /><br /><br />';
							echo '<pre>';
							print_r($subscription);					
							echo '</pre>'; */	

						} elseif( isset($charge_web_seals[$i]["is_additional_domain"]) && 1 === (int) $charge_web_seals[$i]["is_additional_domain"]) {

							/*$web_seal_update = $wpdb->update( 
								$table,
								array( 
									'seal_status' => 'pending',	// string
								),	 
									//array( 'id' => $id ),
									array( 'id' => $charge_web_seals[$i]["id"] ),	
								array( 
									'%s',
								),
								array( '%d' )
							);*/

							//wp_die('This domain is additional domain - Cannot process this request');

							//$plan_id = 'additional_domain_monthly_charges';
							$plan_id = 'seal_basic_monthly_fee_with_dqpz_coupon';


							// Create a subscription
							$customer     = Stripe_Customer::retrieve($customer_id);
							$subscription = $customer->subscriptions->create(array('plan' => $plan_id));

							if (isset($subscription->id)) {				
								update_user_meta($user_id, 'wp_optimizemember_subscr_id', $subscription->id);
								//add_user_meta($new_user_id, 'wp_optimizemember_custom_fields', $meta_value);

								$wpdb->update( 
									$table,
									array( 
										'subscription_id' => $subscription->id,	// string
									),	 
									//array( 'id' => $id ),
									array( 'id' => $charge_web_seals[$i]["id"] ),	
									array( 
										'%s',
									),
									array( '%d' )
								);								
							}
								
						}					

					}

						

				}

				wp_die('Successful!');

			} else {

				wp_die('Declined!');

			}
		
		}



		if( 'deactivate' === $this->current_action() ) {

			$seals = implode(', ', $_GET['seal']);
        	
        	//Get seal status that's not active
			$web_seals = $wpdb->get_results( 
				"
				SELECT * 
				FROM $table								
				WHERE id IN ($seals)
				AND seal_status = 'active'
				",
				ARRAY_A
			);    

			//print_r($web_seals["seal_domain"]);
			//wp_die(count($web_seals)); 

			for( $i = 0; $i < sizeof($web_seals); $i++ ){   				

				$id = $web_seals[$i]["id"];
				$user_id = $web_seals[$i]["user_id"];					
				$seal_status = $web_seals[$i]["seal_status"];
				$seal_domain = $web_seals[$i]["seal_domain"];
				$seal_type = $web_seals[$i]["seal_type"];
				$seal_language = $web_seals[$i]["seal_language"];
				$is_additional_domain = $web_seals[$i]["is_additional_domain"];
				$seal_coupon_code = unserialize(base64_decode($web_seals[$i]['seal_coupon_code']));		
				$updated_at	= date('Y-m-d H:i:s');
				$user_customer_id = get_user_meta($user_id, 'wp_optimizemember_subscr_cid');
				$user_subscription_id = $web_seals[$i]["subscription_id"];


				$web_seal_update = $wpdb->update( 
					$table,
					array( 
						'seal_status' => 'inactive',	// string
					),	 
					array( 'id' => $id ),
					array( 
						'%s',
					),
					array( '%d' )
				);

				if ( !in_array($user_id, $exclude_user_ids) ) {

					//Charge each badge using payment api
					if( $web_seal_update === 1 ) {

						$charge_web_seals[] = array(
							'id' => $id,
							'user_id' => $user_id,
							'seal_status' => $seal_status,
							'seal_domain' => $seal_domain,
							'seal_type' => $seal_type,
							'seal_language' => $seal_language,
							'is_additional_domain' => $is_additional_domain,
							'seal_coupon_code' => $seal_coupon_code,
							'user_customer_id' => $user_customer_id,	
							'user_subscription_id' => $user_subscription_id						
						);

					} else {

						$charge_web_seals[] = array(
							'id' => $id,
							'user_id' => $user_id,
							'seal_status' => $seal_status,
							'seal_domain' => $seal_domain,
							'seal_type' => $seal_type,
							'seal_language' => $seal_language,
							'is_additional_domain' => $is_additional_domain,							
							'seal_coupon_code' => $seal_coupon_code,
							'user_customer_id' => $user_customer_id,
							'user_subscription_id' => $user_subscription_id														
						);

					}

				}

			}

			if( !empty($charge_web_seals) ) {

				for( $i = 0; $i < sizeof($charge_web_seals); $i++ ) {

					$customer_id = $charge_web_seals[$i]["user_customer_id"][0];
					$subscription_id = $charge_web_seals[$i]["user_subscription_id"];

					$customer     = Stripe_Customer::retrieve($customer_id);
					$subscription = $customer->subscriptions->retrieve($subscription_id);				
					//$subscription     = Stripe_Subscription::retrieve($subscription_id);
					$subscription->cancel();
					//s$subscription->cancel(array('at_period_end' => true));
	
					//print_r($subscription);

				}

				wp_die('Successful!');

			}


		}


		/*if( 'Edit' === $this->current_action() ) {

			$seals = implode(', ', $_GET['seal']);
        	
        	//Get seal status that's not active
			$web_seals = $wpdb->get_results( 
				"
				SELECT * 
				FROM $table								
				WHERE id IN ($seals)
				AND seal_status = 'active'
				",
				ARRAY_A
			);   


			wp_die('Edit Products');


		}*/		

    }


    /** ************************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     * 
     * @global WPDB $wpdb
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/
    function prepare_items() {
        global $wpdb; //This is used only if making any database queries

        $table = $wpdb->prefix . 'web_seals';
      	//$user_ID = isset($_REQUEST["user"]) ? $_REQUEST["user"] : '';
		//$user_ID = get_current_user_id();
		   

		$user_ID = isset($_GET["user"]) ? $_GET["user"] : get_current_user_id();

		$selected_user = get_userdata($user_ID);   
		

        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 15;
        
        
        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        
        /**
         * REQUIRED. Finally, we build an array to be used by the class for column 
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        
        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
        $this->process_bulk_action();
        
        
        /**
         * Instead of querying a database, we're going to fetch the example data
         * property we created for use in this plugin. This makes this example 
         * package slightly different than one you might build on your own. In 
         * this example, we'll be using array manipulation to sort and paginate 
         * our data. In a real-world implementation, you will probably want to 
         * use sort and pagination data to build a custom query instead, as you'll
         * be able to use your precisely-queried data immediately.
         */
        //$data = $this->example_data;

		if( $selected_user->roles[0] === 'administrator' || $selected_user->roles[0] === 'doctor_verified_reviewer' ) {
			
			//echo 'testing 1';
			$data = $wpdb->get_results( 
				"
				SELECT * 
				FROM $table				
				",
				ARRAY_A
			);

		} else {

			//echo 'testing 2';
			$data = $wpdb->get_results( 
				"
				SELECT * 
				FROM $table
				WHERE user_id = $user_ID	
				",
				ARRAY_A
			);


		}	

		/*if( $selected_user->roles[0] === 'doctor_verified_reviewer' ) {
			
			echo 'testing 3';
			$data = $wpdb->get_results( 
				"
				SELECT * 
				FROM $table				
				",
				ARRAY_A
			);

		}*/

		/*$data = $wpdb->get_results( 
			"
			SELECT * 
			FROM $table				
			",
			ARRAY_A
		);*/      
                
        
        /**
         * This checks for sorting input and sorts the data in our array accordingly.
         * 
         * In a real-world situation involving a database, you would probably want 
         * to handle sorting by passing the 'orderby' and 'order' values directly 
         * to a custom query. The returned data will be pre-sorted, and this array
         * sorting technique would be unnecessary.
         */
        function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'seal_status'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
        usort($data, 'usort_reorder');
        
        
        /***********************************************************************
         * ---------------------------------------------------------------------
         * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
         * 
         * In a real-world situation, this is where you would place your query.
         *
         * For information on making queries in WordPress, see this Codex entry:
         * http://codex.wordpress.org/Class_Reference/wpdb
         * 
         * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
         * ---------------------------------------------------------------------
         **********************************************************************/
        
                
        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently 
         * looking at. We'll need this later, so you should always include it in 
         * your own package classes.
         */
        $current_page = $this->get_pagenum();
        
        /**
         * REQUIRED for pagination. Let's check how many items are in our data array. 
         * In real-world use, this would be the total number of items in your database, 
         * without filtering. We'll need this later, so you should always include it 
         * in your own package classes.
         */
        $total_items = count($data);
        
        
        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to 
         */
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        
        
        
        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where 
         * it can be used by the rest of the class.
         */
        $this->items = $data;
        
        
        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    
    }


}


add_action('admin_menu', 'doctor_verified_custom_submenu_page');
 



function doctor_verified_user_row_action_links($actions, $user_object) {
	//$actions['edit_badges'] = "<a class='doctor_verified_ub_edit_seal' href='" . admin_url( "users.php?page=doctor-verified-seals&action=seals&amp;user=$user_object->ID") . "'>" . __( 'Edit Seals', 'doctor_verified_ub' ) . "</a>";
	$actions['edit_badges'] = "<a class='doctor_verified_ub_edit_seal' href='" . admin_url( "profile.php?page=doctor-verified-submenu-page&action=seals&amp;user=$user_object->ID") . "'>" . __( 'Edit Seals', 'doctor_verified_ub' ) . "</a>";
	return $actions;
}
add_filter('user_row_actions', 'doctor_verified_user_row_action_links', 10, 2);


function doctor_verified_custom_submenu_page() {
    add_submenu_page( 
        null,
        'My Custom Submenu Page',
        'My Custom Submenu Page',
        'manage_options',
        'doctor-verified-submenu-page',
        'doctor_verified_user_screen'
    );
}


function doctor_verified_user_screen(){

	global $wpdb;
	$table = $wpdb->prefix . 'web_seals';
    
    //Create an instance of our package class...
    $doctorverifiedListTable = new Doctor_Verified_User_List_Table();
    //Fetch, prepare, sort, and filter our data...
    $doctorverifiedListTable->prepare_items();

	$seal_id = isset($_GET['seal']) ? (int) trim($_GET['seal']) : '';

  	if( isset($_POST["submit"]) ) {
  		//save to db
  		//var_dump($_POST);

		$wpdb->update( 
			$table, 
			array( 
				'verified_products' => trim($_POST['verified_products'])	// string (text)
			), 
			array( 'id' => $seal_id ), 
			array( 
				'%s',	// verified_products
			), 
			array( '%d' ) 
		);

  	}

  	if(!empty($seal_id)) {
  		$get_seal_row = $wpdb->get_row( "SELECT * FROM $table WHERE id = ".$seal_id );
		//var_dump($get_seal_row->verified_products);
	}

    ?>
    <div class="wrap">

        <?php if( (isset($_GET['action']) && $_GET['action'] == 'edit') && isset($_GET['seal']) ) : ?>

	        <div id="icon-users" class="icon32"><br/></div>
	        <h2>Review Domain</h2>

	        <?php 
	        	//print_r($_GET);  
	        	
	        	$seal = $_GET["seal"];
	        	$user_id = $_GET["user"];

	        ?>
	        
	        <!--form action="<?php //echo admin_url( 'profile.php?page=doctor-verified-submenu-page&action=edit&amp;seal='.$seal.'&amp;user='.$user_id); ?>"  method="post" name="frmupdate">
	        	<input type="submit" value="Submit">
	        </form-->


				<form action="<?php echo admin_url( 'profile.php?page=doctor-verified-submenu-page&action=edit&amp;seal='.$seal.'&amp;user='.$user_id); ?>" method="post">
				  <fieldset>
				    <legend>Verified Products:</legend>
				    <textarea name="verified_products" rows="10" cols="30"><?php echo isset($get_seal_row->verified_products) ? $get_seal_row->verified_products : "Verified Products"; ?> </textarea><br />
				    <input type="submit" name="submit" value="Submit"> 
				    <a href="<?php echo admin_url( 'profile.php?page=doctor-verified-submenu-page&action=seals&amp;user='.$user_id); ?>">Back</a>
				  </fieldset>
				</form>

    	<?php else : ?>
 
	        <div id="icon-users" class="icon32"><br/></div>
	        <h2>Review Domain</h2>
	        
	        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
	        <form id="seals-filter" method="get">
	            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
	            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
	            <!-- Now we can render the completed list table -->
	            <?php $doctorverifiedListTable->display() ?>
	        </form>

    	<?php endif; ?>
        
    </div>
    <?php
}


/**
 * Redirect user after successful login.
 *
 * @param string $redirect_to URL to redirect to.
 * @param string $request URL the user is coming from.
 * @param object $user Logged user's data.
 * @return string
 */

//https://codex.wordpress.org/Plugin_API/Filter_Reference/login_redirect
//https://codex.wordpress.org/Function_Reference/site_url
//https://codex.wordpress.org/Function_Reference/home_url
function doctor_verified_login_redirect( $redirect_to, $request, $user ) {
	//is there a user to check?
	if ( isset( $user->roles ) && is_array( $user->roles ) ) {
		//check for admins
		if ( in_array( 'administrator', $user->roles ) ) {
			// redirect them to the default place
			return $redirect_to;
		} elseif ( in_array( 'doctor_verified_member', $user->roles ) ) {
			//return home_url( 'members', 'relative' );
			return site_url( 'index.php/members/', 'relative');
			//return site_url( 'index.php/members/' );
		}
	} else {
		return $redirect_to;
	}
}

add_filter( 'login_redirect', 'doctor_verified_login_redirect', 10, 3 );


add_action( 'template_redirect', function() {

  if ( is_user_logged_in() || !is_page() ) return;

  	//$restricted = array(89, 94, 91); // all your restricted pages : Members Area, Billing Information, Contact
  	$restricted = array(284, 354, 365); // all your restricted pages : Members Area, Billing Information, Contact

  if ( in_array( get_queried_object_id(), $restricted ) ) {
  	//echo "Can't Access this page";
    wp_redirect( site_url( 'index.php/login/' ) ); 
    //exit();
  }

});

/*Adding custom user fields*/
add_action( 'show_user_profile', 'doctor_verified_add_extra_social_links' );
add_action( 'edit_user_profile', 'doctor_verified_add_extra_social_links' );

function doctor_verified_add_extra_social_links( $user )
{


	$countries = array("Afghanistan", "Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua and Barbuda", "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegowina", "Botswana", "Bouvet Island", "Brazil", "British Indian Ocean Territory", "Brunei Darussalam", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Cocos (Keeling) Islands", "Colombia", "Comoros", "Congo", "Congo, the Democratic Republic of the", "Cook Islands", "Costa Rica", "Cote d'Ivoire", "Croatia (Hrvatska)", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Falkland Islands (Malvinas)", "Faroe Islands", "Fiji", "Finland", "France", "France Metropolitan", "French Guiana", "French Polynesia", "French Southern Territories", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Gibraltar", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Heard and Mc Donald Islands", "Holy See (Vatican City State)", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran (Islamic Republic of)", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, Democratic People's Republic of", "Korea, Republic of", "Kuwait", "Kyrgyzstan", "Lao, People's Democratic Republic", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libyan Arab Jamahiriya", "Liechtenstein", "Lithuania", "Luxembourg", "Macau", "Macedonia, The Former Yugoslav Republic of", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia, Federated States of", "Moldova, Republic of", "Monaco", "Mongolia", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "Netherlands Antilles", "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "Northern Mariana Islands", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Pitcairn", "Poland", "Portugal", "Puerto Rico", "Qatar", "Reunion", "Romania", "Russian Federation", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Seychelles", "Sierra Leone", "Singapore", "Slovakia (Slovak Republic)", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Georgia and the South Sandwich Islands", "Spain", "Sri Lanka", "St. Helena", "St. Pierre and Miquelon", "Sudan", "Suriname", "Svalbard and Jan Mayen Islands", "Swaziland", "Sweden", "Switzerland", "Syrian Arab Republic", "Taiwan, Province of China", "Tajikistan", "Tanzania, United Republic of", "Thailand", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "United States Minor Outlying Islands", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Vietnam", "Virgin Islands (British)", "Virgin Islands (U.S.)", "Wallis and Futuna Islands", "Western Sahara", "Yemen", "Yugoslavia", "Zambia", "Zimbabwe");


	$doctor_verified_caps[0] = user_can($user->ID, 'doctor_verified_member');
	$doctor_verified_caps[1] = user_can($user->ID, 'doctor_verified_reviewer');
    
	//if ( current_user_can('administrator') || current_user_can('doctor_verified_member') ) {
    if ( $doctor_verified_caps[0] === true || current_user_can('doctor_verified_member') ) {

    ?>

        <h3>Doctor's Profile</h3>

        <table class="form-table">
            <tr>
                <th><label for="doctor_name">Doctor Name</label></th>
                <td><input type="text" name="doctor_name" value="<?php echo esc_attr(get_the_author_meta( 'doctor_name', $user->ID )); ?>" class="regular-text" /></td>
            </tr>

            <tr>
                <th><label for="doctor_specialization">Doctor Specialization</label></th>
                <td><input type="text" name="doctor_specialization" value="<?php echo esc_attr(get_the_author_meta( 'doctor_specialization', $user->ID )); ?>" class="regular-text" /></td>
            </tr>

            <tr>
                <th><label for="doctor_place">Doctor Place</label></th>
                <td><input type="text" name="doctor_place" value="<?php echo esc_attr(get_the_author_meta( 'doctor_place', $user->ID )); ?>" class="regular-text" /></td>
            </tr>

            <tr>
                <th><label for="doctor_email">Doctor E-mail</label></th>
                <td><input type="text" name="doctor_email" value="<?php echo esc_attr(get_the_author_meta( 'doctor_email', $user->ID )); ?>" class="regular-text" /></td>
            </tr>            
        
            <!--tr>
                <th><label for="doctor_photo">Doctor Photo</label></th>
                <td><input type="text" name="doctor_photo" value="<?php //echo esc_attr(get_the_author_meta( 'doctor_photo', $user->ID )); ?>" class="regular-text" /></td>
            </tr-->

	        <tr>
	            <th><label for="user_meta_image"><?php _e( 'Doctor Photo', 'textdomain' ); ?></label></th>
	            <td>
	                <!-- Outputs the image after save -->
	                <img src="<?php echo esc_url( get_the_author_meta( 'doctor_photo', $user->ID ) ); ?>" style="width:150px;"><br />
	                <!-- Outputs the text field and displays the URL of the image retrieved by the media uploader -->
	                <input type="text" name="doctor_photo" id="doctor_photo" value="<?php echo esc_url_raw( get_the_author_meta( 'doctor_photo', $user->ID ) ); ?>" class="regular-text" />
	                <!-- Outputs the save button -->
	                <input type='button' class="media-uploader-1 button-primary" value="<?php _e( 'Upload Image', 'textdomain' ); ?>" id="media-uploader-1"/><br />
	                <span class="description"><?php _e( 'Upload an additional image for your user profile.', 'textdomain' ); ?></span>
	            </td>
	        </tr>


        </table>

	<?php //} elseif ( current_user_can('administrator') || current_user_can('doctor_verified_reviewer') ) { ?>
    <?php } elseif ( $doctor_verified_caps[1] === true || current_user_can('doctor_verified_reviewer') ) { ?>

        <h3>Reviewer's Profile</h3>

        <table class="form-table">

            <tr>
                <th><label for="reviewer_address">Address</label></th>
                <td><textarea name="txtAddress" id="txtAddress" maxlength="500" class="input-style" value="<?php echo esc_attr(get_the_author_meta( 'reviewer_address', $user->ID )); ?>" placeholder="" /><?php echo esc_attr(get_the_author_meta( 'reviewer_address', $user->ID )); ?></textarea></td>
                <!--td><input type="text" name="reviewer_address" value="<?php //echo esc_attr(get_the_author_meta( 'reviewer_address', $user->ID )); ?>" class="regular-text" /></td-->
            </tr>

            <tr>
                <th><label for="reviewer_city">City</label></th>
                <td><input type="text" name="reviewer_city" value="<?php echo esc_attr(get_the_author_meta( 'reviewer_city', $user->ID )); ?>" class="regular-text" /></td>
            </tr>

            <tr>
                <th><label for="reviewer_postcode">PostCode</label></th>
                <td><input type="text" name="reviewer_postcode" value="<?php echo esc_attr(get_the_author_meta( 'reviewer_postcode', $user->ID )); ?>" class="regular-text" /></td>
            </tr>


             <tr>
                <th><label for="reviewer_country">Country</label></th>
                <td><input type="text" name="reviewer_country" value="<?php echo esc_attr(get_the_author_meta( 'reviewer_country', $user->ID )); ?>" class="regular-text" /></td>
            </tr>

            <tr>
                <th><label for="reviewer_profession">Profession</label></th>
                <td>
                	<input type="text" name="reviewer_profession" value="<?php echo esc_attr(get_the_author_meta( 'reviewer_profession', $user->ID )); ?>" class="regular-text" />
                </td>
            
            </tr>

            <tr>
                <th><label for="doctor_contact">Contact #:</label></th>
                <td><input type="text" name="reviewer_contact_number" value="<?php echo esc_attr(get_the_author_meta( 'reviewer_contact_number', $user->ID )); ?>" class="regular-text" /></td>
            </tr>

            <!--tr>
                <th><label for="reviewer_proof_of_certificate_url">Proof of Certificate</label></th>
                <td><input type="text" name="reviewer_proof_of_certificate_url" value="<?php //echo esc_attr(get_the_author_meta( 'reviewer_proof_of_certificate_url', $user->ID )); ?>" class="regular-text" /></td>
            </tr-->

            <!--tr>
                <th><label for="reviewer_photo_url">Reviewer Photo</label></th>
                <td><input type="text" name="reviewer_photo_url" value="<?php //echo esc_attr(get_the_author_meta( 'reviewer_photo_url', $user->ID )); ?>" class="regular-text" /></td>
            </tr-->

	        <tr>
	            <th><label for="reviewer_proof_of_certificate_url"><?php _e( 'Proof of Certificate', 'textdomain' ); ?></label></th>
	            <td>
	                <!-- Outputs the image after save -->
	                <img src="<?php echo esc_url( get_the_author_meta( 'reviewer_proof_of_certificate_url', $user->ID ) ); ?>" style="width:150px;"><br />
	                <!-- Outputs the text field and displays the URL of the image retrieved by the media uploader -->
	                <input type="text" name="reviewer_proof_of_certificate_url" id="reviewer_proof_of_certificate_url" value="<?php echo esc_url_raw( get_the_author_meta( 'reviewer_proof_of_certificate_url', $user->ID ) ); ?>" class="regular-text" />
	                <!-- Outputs the save button -->
	                <input type='button' class="media-uploader-2 button-primary" value="<?php _e( 'Upload Image', 'textdomain' ); ?>" id="media-uploader-2"/><br />
	                <span class="description"><?php _e( 'Upload an additional image for your user profile.', 'textdomain' ); ?></span>
	            </td>
	        </tr>

	        <tr>
	            <th><label for="reviewer_photo_url"><?php _e( 'Reviewer Photo', 'textdomain' ); ?></label></th>
	            <td>
	                <!-- Outputs the image after save -->
	                <img src="<?php echo esc_url( get_the_author_meta( 'reviewer_photo_url', $user->ID ) ); ?>" style="width:150px;"><br />
	                <!-- Outputs the text field and displays the URL of the image retrieved by the media uploader -->
	                <input type="text" name="reviewer_photo_url" id="reviewer_photo_url" value="<?php echo esc_url_raw( get_the_author_meta( 'reviewer_photo_url', $user->ID ) ); ?>" class="regular-text" />
	                <!-- Outputs the save button -->
	                <input type='button' class="media-uploader-3 button-primary" value="<?php _e( 'Upload Image', 'textdomain' ); ?>" id="media-uploader-3"/><br />
	                <span class="description"><?php _e( 'Upload an additional image for your user profile.', 'textdomain' ); ?></span>
	            </td>
	        </tr>
        </table>




	<?php }
}


add_action( 'personal_options_update', 'doctor_verified_save_extra_social_links' );
add_action( 'edit_user_profile_update', 'doctor_verified_save_extra_social_links' );

function doctor_verified_save_extra_social_links( $user_id )
{

	//echo $user_id;
	//exit;

	$doctor_verified_caps[0] = user_can($user_id, 'doctor_verified_member');
	$doctor_verified_caps[1] = user_can($user_id, 'doctor_verified_reviewer');

	//if ( current_user_can('administrator') || current_user_can('doctor_verified_member') ) {
    if ( $doctor_verified_caps[0] === true || current_user_can('doctor_verified_member') ) {

	    update_user_meta( $user_id,'doctor_name', sanitize_text_field( $_POST['doctor_name'] ) );
	    update_user_meta( $user_id,'doctor_specialization', sanitize_text_field( $_POST['doctor_specialization'] ) );
	    update_user_meta( $user_id,'doctor_place', sanitize_text_field( $_POST['doctor_place'] ) );
	    update_user_meta( $user_id,'doctor_email', sanitize_text_field( $_POST['doctor_email'] ) );
	    update_user_meta( $user_id,'doctor_photo', sanitize_text_field( $_POST['doctor_photo'] ) );


	//} elseif ( current_user_can('administrator') || current_user_can('doctor_verified_reviewer') ) {
    } elseif ( $doctor_verified_caps[1] === true || current_user_can('doctor_verified_reviewer') ) {

		update_user_meta( $user_id,'reviewer_address', sanitize_text_field( $_POST['reviewer_address'] ) );
		update_user_meta( $user_id,'reviewer_city', sanitize_text_field( $_POST['reviewer_city'] ) );
		update_user_meta( $user_id,'reviewer_postcode', sanitize_text_field( $_POST['reviewer_postcode'] ) );
		update_user_meta( $user_id,'reviewer_country', sanitize_text_field( $_POST['reviewer_country'] ) );

	    update_user_meta( $user_id,'reviewer_profession', sanitize_text_field( $_POST['reviewer_profession'] ) );
	    update_user_meta( $user_id,'reviewer_contact_number', sanitize_text_field( $_POST['reviewer_contact_number'] ) );
	    update_user_meta( $user_id,'reviewer_proof_of_certificate_url', sanitize_text_field( $_POST['reviewer_proof_of_certificate_url'] ) );
	    update_user_meta( $user_id,'reviewer_photo_url', sanitize_text_field( $_POST['reviewer_photo_url'] ) ); 

    }

  
}

//http://stevenslack.com/add-image-uploader-to-profile-admin-page-wordpress/

/**
 * Return an ID of an attachment by searching the database with the file URL.
 *
 * First checks to see if the $url is pointing to a file that exists in
 * the wp-content directory. If so, then we search the database for a
 * partial match consisting of the remaining path AFTER the wp-content
 * directory. Finally, if a match is found the attachment ID will be
 * returned.
 *
 * http://frankiejarrett.com/get-an-attachment-id-by-url-in-wordpress/
 *
 * @return {int} $attachment
 */
function get_attachment_image_by_url( $url ) {
 
    // Split the $url into two parts with the wp-content directory as the separator.
    $parse_url  = explode( parse_url( WP_CONTENT_URL, PHP_URL_PATH ), $url );
 
    // Get the host of the current site and the host of the $url, ignoring www.
    $this_host = str_ireplace( 'www.', '', parse_url( home_url(), PHP_URL_HOST ) );
    $file_host = str_ireplace( 'www.', '', parse_url( $url, PHP_URL_HOST ) );
 
    // Return nothing if there aren't any $url parts or if the current host and $url host do not match.
    if ( !isset( $parse_url[1] ) || empty( $parse_url[1] ) || ( $this_host != $file_host ) ) {
        return;
    }
 
    // Now we're going to quickly search the DB for any attachment GUID with a partial path match.
    // Example: /uploads/2013/05/test-image.jpg
    global $wpdb;
 
    $prefix     = $wpdb->prefix;
    $attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM " . $prefix . "posts WHERE guid RLIKE %s;", $parse_url[1] ) );
 
    // Returns null if no attachment is found.
    return $attachment[0];
}


/*
 * Retrieve the appropriate image size
 */
/*function get_additional_user_meta_thumb($field = '') {
 
    $attachment_url = esc_url( get_the_author_meta( $field, $post->post_author ) );
 
     // grabs the id from the URL using Frankie Jarretts function
    $attachment_id = get_attachment_image_by_url( $attachment_url );
 
    // retrieve the thumbnail size of our image
    $image_thumb = wp_get_attachment_image_src( $attachment_id, 'thumbnail' );
 
    // return the image thumbnail
    return $image_thumb[0];
 
}*/

/*

Put this snippet in your theme file where you want to display the image. So lets call the user meta like so:

<?php
// retrieve our additional author meta info
$user_meta_image = esc_attr( get_the_author_meta( 'user_meta_image', $post--->post_author ) );
 
    // make sure the field is set
    if ( isset( $user_meta_image ) && $user_meta_image ) {
 
        // only display if function exists
        if ( function_exists( 'get_additional_user_meta_thumb' ) ) ?>
            <img alt="author photo two" src="<?php echo get_additional_user_meta_thumb(); ?>" />
 
<?php } ?>

*/

/*function file_upload($file = '') {

	if ( ! function_exists( 'wp_handle_upload' ) ) {
	    require_once( ABSPATH . 'wp-admin/includes/file.php' );
	}
	 
	$uploadedfile = $file;
	 
	$upload_overrides = array(
	    'test_form' => false
	);
	 
	$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
	 
	if ( $movefile && ! isset( $movefile['error'] ) ) {
	    echo __( 'File is valid, and was successfully uploaded.', 'textdomain' ) . "\n";
	    //var_dump( $movefile );


			// The ID of the post this attachment is for.
			$parent_post_id = 0;

			// Check the type of file. We'll use this as the 'post_mime_type'.
			$filetype = wp_check_filetype( basename( $uploadedfile ), null );

			// Get the path to the upload directory.
			$wp_upload_dir = wp_upload_dir();

			// Prepare an array of post data for the attachment.
			$attachment = array(
				'guid'           => $wp_upload_dir['url'] . '/' . basename( $uploadedfile ), 
				'post_mime_type' => $filetype['type'],
				'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $uploadedfile ) ),
				'post_content'   => '',
				'post_status'    => 'inherit'
			);

			// Insert the attachment.
			$attach_id = wp_insert_attachment( $attachment, $uploadedfile );

			// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
			require_once( ABSPATH . 'wp-admin/includes/image.php' );

			// Generate the metadata for the attachment, and update the database record.
			$attach_data = wp_generate_attachment_metadata( $attach_id, $uploadedfile );
			wp_update_attachment_metadata( $attach_id, $attach_data );

			set_post_thumbnail( $parent_post_id, $attach_id );

	} else {
	    /*
	     * Error generated by _wp_handle_upload()
	     * @see _wp_handle_upload() in wp-admin/includes/file.php
	     */

/*	    
	    echo $movefile['error'];
	}

	unset($file);

}*/


function file_upload($file = '') {


	if ( ! function_exists( 'wp_handle_upload' ) ) {
	    require_once( ABSPATH . 'wp-admin/includes/file.php' );
	}

	$uploadedfile = $file; //$_FILES['file'];

	$upload_overrides = array( 'test_form' => false );

	$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );

	if ( $movefile && ! isset( $movefile['error'] ) ) {
	    //echo "File is valid, and was successfully uploaded.\n";
	    //var_dump( $movefile );

	    return $movefile;
	} else {
	    /**
	     * Error generated by _wp_handle_upload()
	     * @see _wp_handle_upload() in wp-admin/includes/file.php
	     */
	    echo $movefile['error'];
	}

	unset($file);


}

