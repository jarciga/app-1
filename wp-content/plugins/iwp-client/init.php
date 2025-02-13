<?php
/* 
Plugin Name: InfiniteWP - Client
Plugin URI: http://infinitewp.com/
Description: This is the client plugin of InfiniteWP that communicates with the InfiniteWP Admin panel.
Author: Revmakx
Version: 1.6.3.2
Author URI: http://www.revmakx.com
*/
/************************************************************
 * This plugin was modified by Revmakx						*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/

/*************************************************************
 * 
 * init.php
 * 
 * Initialize the communication with master
 * 
 * 
 * Copyright (c) 2011 Prelovac Media
 * www.prelovac.com
 **************************************************************/
if(basename($_SERVER['SCRIPT_FILENAME']) == "init.php"):
    exit;
endif;
if(!defined('IWP_MMB_CLIENT_VERSION'))
	define('IWP_MMB_CLIENT_VERSION', '1.6.3.2');
	


if ( !defined('IWP_MMB_XFRAME_COOKIE')){
	$siteurl = function_exists('get_site_option') ? get_site_option( 'siteurl' ) : get_option('siteurl');
	define('IWP_MMB_XFRAME_COOKIE', $xframe = 'wordpress_'.md5($siteurl).'_xframe');
}
global $wpdb, $iwp_mmb_plugin_dir, $iwp_mmb_plugin_url, $wp_version, $iwp_mmb_filters, $_iwp_mmb_item_filter;
if (version_compare(PHP_VERSION, '5.0.0', '<')) // min version 5 supported
    exit("<p>InfiniteWP Client plugin requires PHP 5 or higher.</p>");


$iwp_mmb_wp_version = $wp_version;
$iwp_mmb_plugin_dir = WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__));
$iwp_mmb_plugin_url = WP_PLUGIN_URL . '/' . basename(dirname(__FILE__));

require_once("$iwp_mmb_plugin_dir/helper.class.php");
require_once("$iwp_mmb_plugin_dir/core.class.php");
require_once("$iwp_mmb_plugin_dir/activities_log.class.php");
require_once("$iwp_mmb_plugin_dir/stats.class.php");
//require_once("$iwp_mmb_plugin_dir/backup.class.php");
//require_once("$iwp_mmb_plugin_dir/backup.class.singlecall.php");
//require_once("$iwp_mmb_plugin_dir/backup.class.multicall.php");
require_once("$iwp_mmb_plugin_dir/installer.class.php");

require_once("$iwp_mmb_plugin_dir/addons/manage_users/user.class.php");
//require_once("$iwp_mmb_plugin_dir/addons/backup_repository/backup_repository.class.php");
require_once("$iwp_mmb_plugin_dir/addons/comments/comments.class.php");

require_once("$iwp_mmb_plugin_dir/addons/post_links/link.class.php");
require_once("$iwp_mmb_plugin_dir/addons/post_links/post.class.php");

require_once("$iwp_mmb_plugin_dir/addons/wp_optimize/optimize.class.php");

require_once("$iwp_mmb_plugin_dir/api.php");
require_once("$iwp_mmb_plugin_dir/plugins/search/search.php");
require_once("$iwp_mmb_plugin_dir/plugins/cleanup/cleanup.php");



if( !function_exists ( 'iwp_mmb_filter_params' )) {
	function iwp_mmb_filter_params( $array = array() ){
		
		$filter = array( 'current_user', 'wpdb' );
		$return = array();
		foreach ($array as $key => $val) { 
			if( !is_int($key) && in_array($key, $filter) )
				continue;
				
			if( is_array( $val ) ) { 
				$return[$key] = iwp_mmb_filter_params( $val );
			} else {
				$return[$key] = $val;
			}
		} 
		
		return $return;
	}
}

if( !function_exists ('iwp_mmb_parse_request')) {
	function iwp_mmb_parse_request()
	{
		global $HTTP_RAW_POST_DATA, $iwp_mmb_activities_log;
		$HTTP_RAW_POST_DATA_LOCAL = NULL;
		$HTTP_RAW_POST_DATA_LOCAL = file_get_contents('php://input');
		if(empty($HTTP_RAW_POST_DATA_LOCAL)){
			if (isset($HTTP_RAW_POST_DATA)) {
				$HTTP_RAW_POST_DATA_LOCAL = $HTTP_RAW_POST_DATA;
			}
		}
		
		ob_start();
		
		global $current_user, $iwp_mmb_core, $new_actions, $wp_db_version, $wpmu_version, $_wp_using_ext_object_cache;
		if (strrpos($HTTP_RAW_POST_DATA_LOCAL, '_IWP_JSON_PREFIX_') !== false) {
			$request_data_array = explode('_IWP_JSON_PREFIX_', $HTTP_RAW_POST_DATA_LOCAL);
			$request_raw_data = $request_data_array[1];
			$data = trim(base64_decode($request_raw_data));
			$request_data = NULL;
			$GLOBALS['IWP_JSON_COMMUNICATION'] = 1;
		} else{
			$request_raw_data = $HTTP_RAW_POST_DATA_LOCAL;
			$data = trim(base64_decode($request_raw_data));
			if (is_serialized($data)) {
				if ((strpos($data, 'get_stats') || strpos($data, 'add_site') || strpos($data, 'remove_site')) && (strpos($data,'2.10.1') || strpos($data,'2.10.0') || strpos($data,'2.10.0.1'))) {
					$request_data = iwp_mmb_safe_unserialize($data);
				}else{
					iwp_mmb_response(array('error' => 'Please update your IWP Admin Panel to version 2.10.1', 'error_code' => 'update_panel'), false, true);
				}
			}else{
				return false;
			}
		}
		if ($data){

			//$num = @extract(unserialize($data));
			if (!isset($request_data) && $request_data['iwp_action'] != 'add_site' && $request_data['iwp_action'] != 'get_stats' && $request_data['iwp_action'] != 'remove_site') {
				$request_data = json_decode($data, true);
			}
			
			if(isset($request_data['params'])){ 
				$request_data['params'] = iwp_mmb_filter_params($request_data['params']);
			}
			if (isset($GLOBALS['IWP_JSON_COMMUNICATION']) && $GLOBALS['IWP_JSON_COMMUNICATION']) {
				$signature  = base64_decode($request_data['signature']);
			}else{
				$signature  = $request_data['signature'];
			}

			$iwp_action 					= $request_data['iwp_action'];
			$params 						= $request_data['params'];
			$id 							= $request_data['id'];
			if(isset($request_data['is_save_activity_log'])) {
				$is_save_activity_log	= $request_data['is_save_activity_log'];
			}
			$GLOBALS['activities_log_datetime'] = $request_data['activities_log_datetime'];
		}
		
		if (isset($iwp_action)) {
			
			if(!defined('IWP_AUTHORISED_CALL')) define('IWP_AUTHORISED_CALL', 1);
			if(function_exists('register_shutdown_function')){ register_shutdown_function("iwp_mmb_shutdown"); }
			$GLOBALS['IWP_MMB_PROFILING']['ACTION_START'] = microtime(1);
		
			error_reporting(E_ALL ^ E_NOTICE);
			@ini_set("display_errors", 1);
			
			iwp_mmb_backup_db_changes();
			
			run_hash_change_process();
			
			$action = $iwp_action;
			$_wp_using_ext_object_cache = false;
			@set_time_limit(600);
			
			if (!$iwp_mmb_core->check_if_user_exists($params['username']))
				iwp_mmb_response(array('error' => 'Username <b>' . $params['username'] . '</b> does not have administrative access. Enter the correct username in the site options.', 'error_code' => 'username_does_not_have_administrative_access'), false);
			
			if ($action == 'add_site') {
				$params['is_save_activity_log'] = $is_save_activity_log;
				iwp_mmb_add_site($params);
				iwp_mmb_response(array('error' => 'You should never see this.', 'error_code' => 'you_should_never_see_this'), false);
			}
			if ($action == 'readd_site') {
                $params['id'] = $id;
                $params['signature'] = $signature;
				$params['is_save_activity_log'] = $is_save_activity_log;				
				iwp_mmb_readd_site($params);
				iwp_mmb_response(array('error' => 'You should never see this.', 'error_code' => 'you_should_never_see_this'), false);
			}
			
			$auth = $iwp_mmb_core->authenticate_message($action . $id, $signature, $id);
			if ($auth === true) {
				if ($action == 'maintain_site') {
					iwp_mmb_maintain_site($params);
					iwp_mmb_response(array('error' => 'You should never see this.', 'error_code' => 'you_should_never_see_this'), false);
				}
				@ignore_user_abort(true);
				$GLOBALS['IWP_CLIENT_HISTORY_ID'] = $id;
				
				if(isset($params['username']) && !is_user_logged_in()){
					$user = function_exists('get_user_by') ? get_user_by('login', $params['username']) : get_userdatabylogin( $params['username'] );
					wp_set_current_user($user->ID);
					//For WPE or Reload Data
					//if(@getenv('IS_WPE') || $iwp_action == 'get_stats')
					$SET_14_DAYS_VALIDITY = true;
					wp_set_auth_cookie($user->ID, $SET_14_DAYS_VALIDITY);
				}
				if ($action == 'get_cookie') {
					iwp_mmb_response(true, true);
				}
				/* in case database upgrade required, do database backup and perform upgrade ( wordpress wp_upgrade() function ) */
				if( strlen(trim($wp_db_version)) && !defined('ACX_PLUGIN_DIR') ){
					if ( get_option('db_version') != $wp_db_version ) {
						/* in multisite network, please update database manualy */
						if (empty($wpmu_version) || (function_exists('is_multisite') && !is_multisite())){
							if( ! function_exists('wp_upgrade'))
								include_once(ABSPATH.'wp-admin/includes/upgrade.php');
							
							ob_clean();
							@wp_upgrade();
							@do_action('after_db_upgrade');
							ob_end_clean();
						}
					}
				}
				
				if(isset($params['secure'])){
					if (isset($GLOBALS['IWP_JSON_COMMUNICATION']) && $GLOBALS['IWP_JSON_COMMUNICATION']) {
						$params['secure'] = iwp_mmb_safe_unserialize(base64_decode($params['secure']));
					}
					if($decrypted = $iwp_mmb_core->_secure_data($params['secure'])){
						if (is_serialized($decrypted)) {
							$decrypted = iwp_mmb_safe_unserialize($decrypted);
						}
						if(is_array($decrypted)){
									
							foreach($decrypted as $key => $val){
								if(!is_numeric($key))
									$params[$key] = $val;							
													
							}
							unset($params['secure']);
						} else $params['secure'] = $decrypted;
					}
					elseif(isset($params['secure']['account_info'])){
						$params['account_info'] = $params['secure']['account_info'];
					}
				}
				
				if( !$iwp_mmb_core->register_action_params( $action, $params ) ){
					global $_iwp_mmb_plugin_actions;					
					$_iwp_mmb_plugin_actions[$action] = $params;
				}
				$iwp_mmb_activities_log->iwp_mmb_update_is_save_activity_log($is_save_activity_log);
				$iwp_mmb_activities_log->iwp_mmb_save_options_for_activity_log('parse_request');
			} else {
				iwp_mmb_response($auth, false);
			}
		} else {
			//IWP_MMB_Stats::set_hit_count();
            // $GLOBALS['HTTP_RAW_POST_DATA'] =  $HTTP_RAW_POST_DATA_LOCAL;
            $HTTP_RAW_POST_DATA =  $HTTP_RAW_POST_DATA_LOCAL;
		}
		ob_end_clean();
	}
}

if( !function_exists('iwp_mmb_convert_wperror_obj_to_arr')){
	function iwp_mmb_convert_wperror_obj_to_arr($obj,$state="initial"){
		$result = array();
		if( is_array($obj) ){
			foreach ($obj as $key => $value) {
				$result[$key] = iwp_mmb_convert_wperror_obj_to_arr($value,"intermediate");
			}
		}elseif(is_object($obj) && is_wp_error($obj)){
				$result['error_codes'] = $obj->get_error_codes();
				$result['errors'] = $obj->get_error_messages();
				$result['error_data'] = $obj->get_error_data();
		}else{
			return $obj;
		}
		if($state == 'initial' ){
			if(isset($obj['error']) && is_wp_error($obj['error'])){
				$errMsgTemp = $result['error']['errors'];
				$errCodesTemp = $result['error']['error_codes'];
				if(!empty($result['error']['error_data']) ){
					$errData = ":::".$result['error']['error_data'];
				}else{
					$errData = '';
				}

				$errMsg ='';
				$errCode = '';

				if(count($errMsgTemp) > 1 ){$errMsg = implode("|&|",$errMsgTemp);}elseif(count($errMsgTemp) == 1){$errMsg = $errMsgTemp[0];}
				if(count($errCodesTemp) > 1 ){$errCode = implode("|&|",$errCodesTemp);}elseif(count($errCodesTemp) == 1){$errCode = $errCodesTemp[0];}

				$wpErr = array('error'=>$errMsg.$errData,'error_code'=>$errCode,'error_data'=>$errData);
				return $wpErr;
			}
		}
		return $result;
	}
}

/* Main response function */
if( !function_exists ( 'iwp_mmb_response' )) {

	function iwp_mmb_response($response = false, $success = true, $send_serialize_response=false)
	{	
		$return = array();

		$response = iwp_mmb_convert_wperror_obj_to_arr($response,'initial');
		
		if ((is_array($response) && empty($response)) || (!is_array($response) && strlen($response) == 0)){
			$return['error'] = 'Empty response.';
			$return['error_code'] = 'empty_response';
		}
		else if ($success){
			$return['success'] = $response;
		}
		else{
			$return['error'] = $response['error'];
			$return['error_code'] = $response['error_code'];
		}
		
		if( !headers_sent() ){
			header('HTTP/1.0 200 OK');
			header('Content-Type: text/plain');
		}
		if (!$send_serialize_response) {
			$GLOBALS['IWP_RESPONSE_SENT'] = true;
			$response_data = '_IWP_JSON_PREFIX_'.base64_encode(iwp_mmb_json_encode($return));
		}else{
			$GLOBALS['IWP_RESPONSE_SENT'] = true;
			$response_data = base64_encode(serialize($return));
		}
		exit("<IWPHEADER>" .$response_data."<ENDIWPHEADER>");
	}
}



if( !function_exists ( 'iwp_mmb_add_site' )) {
	function iwp_mmb_add_site($params)
	{
		global $iwp_mmb_core, $iwp_mmb_activities_log;
		$num = extract($params);
		
		if ($num) {
			if (!get_option('iwp_client_action_message_id') && !get_option('iwp_client_public_key')) {
				$public_key = base64_decode($public_key);
				
				
				if(trim($activation_key) != get_option('iwp_client_activate_key')){ //iwp
					iwp_mmb_response(array('error' => 'Invalid activation key', 'error_code' => 'iwp_mmb_add_site_invalid_activation_key'), false);
					return;
				}
				
				if (checkOpenSSL() && !$user_random_key_signing) {
					$verify = openssl_verify($action . $id, base64_decode($signature), $public_key);
					if ($verify == 1) {
						$iwp_mmb_core->set_admin_panel_public_key($public_key);
						$iwp_mmb_core->set_client_message_id($id);
						$iwp_mmb_core->get_stats_instance();
						if(isset($notifications) && is_array($notifications) && !empty($notifications)){
							$iwp_mmb_core->stats_instance->set_notifications($notifications);
						}
						if(isset($brand) && is_array($brand) && !empty($brand)){
							update_option('iwp_client_brand',$brand);
						}
						
						iwp_mmb_response($iwp_mmb_core->stats_instance->get_initial_stats(), true);
						$iwp_mmb_activities_log->iwp_mmb_update_is_save_activity_log($params['is_save_activity_log']);
						$iwp_mmb_activities_log->iwp_mmb_save_options_for_activity_log('add_site');
						delete_option('iwp_client_activate_key');//iwp
					} else if ($verify == 0) {
						iwp_mmb_response(array('error' => 'Invalid message signature. Please contact us if you see this message often.', 'error_code' => 'iwp_mmb_add_site_invalid_message_signature'), false);
					} else {
						iwp_mmb_response(array('error' => 'Command not successful. Please try again.', 'error_code' => 'iwp_mmb_add_site_command_not_successful'), false);
					}
				} else {
					if (!get_option('iwp_client_nossl_key')) {
						srand();
						
						$random_key = md5(base64_encode($public_key) . rand(0, getrandmax()));
						
						$iwp_mmb_core->set_random_signature($random_key);
						$iwp_mmb_core->set_client_message_id($id);
						$iwp_mmb_core->set_admin_panel_public_key($public_key);
						$iwp_mmb_core->get_stats_instance();						
						if(is_array($notifications) && !empty($notifications)){
							$iwp_mmb_core->stats_instance->set_notifications($notifications);
						}
						
						if(is_array($brand) && !empty($brand)){
							update_option('iwp_client_brand',$brand);
						}
						$iwp_mmb_activities_log->iwp_mmb_update_is_save_activity_log($params['is_save_activity_log']);
						$iwp_mmb_activities_log->iwp_mmb_save_options_for_activity_log('add_site');
						iwp_mmb_response($iwp_mmb_core->stats_instance->get_initial_stats(), true);
						delete_option('iwp_client_activate_key');//IWP
					} else
						iwp_mmb_response(array('error' => 'Please deactivate & activate InfiniteWP Client plugin on your site, then add the site again.', 'error_code' => 'deactivate_ctivate_InfiniteWP_Client_plugin_add_site_again_not_iwp_client_nossl_key'), false);
				}
			} else {
				iwp_mmb_response(array('error' => 'Please deactivate &amp; activate InfiniteWP Client plugin on your site, then add the site again.', 'error_code' => 'deactivate_ctivate_InfiniteWP_Client_plugin_add_site_again_not_iwp_client_nossl_key'), false);
			}
		} else {
			iwp_mmb_response(array('error' => 'Invalid parameters received. Please try again.', 'error_code' => 'iwp_mmb_add_site_invalid_parameters_received'), false);
		}
	}
}

if( !function_exists ( 'iwp_mmb_readd_site' )) {
	function iwp_mmb_readd_site($params){
		global $iwp_mmb_core,$iwp_mmb_activities_log;
		$num = extract($params);
		if ($num) {
			if (!get_option('iwp_client_action_message_id') && !get_option('iwp_client_public_key')) {
				$public_key = base64_decode($public_key);
				if(trim($activation_key) != get_option('iwp_client_activate_key')){ //iwp
					iwp_mmb_response(array('error' => 'Invalid activation key', 'error_code' => 'iwp_mmb_readd_site_invalid_activation_key'), false);
					return;
				}
				if (checkOpenSSL() && !$user_random_key_signing) {

					$verify = openssl_verify($action . $id, $signature, $public_key);
					if ($verify == 1) {
						$iwp_mmb_core->set_admin_panel_public_key($public_key);
						$iwp_mmb_core->set_client_message_id($id);
						$iwp_mmb_core->get_stats_instance();
						if(isset($notifications) && is_array($notifications) && !empty($notifications)){
							$iwp_mmb_core->stats_instance->set_notifications($notifications);
						}
						if(isset($brand) && is_array($brand) && !empty($brand)){
							update_option('iwp_client_brand',$brand);
						}
						$iwp_mmb_activities_log->iwp_mmb_update_is_save_activity_log($params['is_save_activity_log']);
						$iwp_mmb_activities_log->iwp_mmb_save_options_for_activity_log('readd_site');
						iwp_mmb_response($iwp_mmb_core->stats_instance->get_initial_stats(), true);
						delete_option('iwp_client_activate_key');//iwp
					} else if ($verify == 0) {
						iwp_mmb_response(array('error' => 'Invalid message signature. Please contact us if you see this message often.', 'error_code' => 'iwp_mmb_readd_site_invalid_message_signature'), false);
					} else {
						iwp_mmb_response(array('error' => 'Command not successful. Please try again.', 'error_code' => 'iwp_mmb_readd_site_command_not_successful'), false);
					}
				} else {
					if (!get_option('iwp_client_nossl_key')) {
						srand();

						$random_key = md5(base64_encode($public_key) . rand(0, getrandmax()));

						$iwp_mmb_core->set_random_signature($random_key);
						$iwp_mmb_core->set_client_message_id($id);
						$iwp_mmb_core->set_admin_panel_public_key($public_key);
						$iwp_mmb_core->get_stats_instance();						
						if(is_array($notifications) && !empty($notifications)){
							$iwp_mmb_core->stats_instance->set_notifications($notifications);
						}

						if(is_array($brand) && !empty($brand)){
							update_option('iwp_client_brand',$brand);
						}
						$iwp_mmb_activities_log->iwp_mmb_update_is_save_activity_log($params['is_save_activity_log']);
						$iwp_mmb_activities_log->iwp_mmb_save_options_for_activity_log('readd_site');
						iwp_mmb_response($iwp_mmb_core->stats_instance->get_initial_stats(), true);
						delete_option('iwp_client_activate_key');//IWP
					} else
						iwp_mmb_response(array('error' => 'Please deactivate & activate InfiniteWP Client plugin on your site, then add the site again.', 'error_code' => 'deactivate_ctivate_InfiniteWP_Client_plugin_add_site_again_not_iwp_client_nossl_key'), false);
				}
			} else {
				iwp_mmb_response(array('error' => 'Please deactivate &amp; activate InfiniteWP Client plugin on your site, then add the site again.', 'error_code' => 'deactivate_ctivate_InfiniteWP_Client_plugin_add_site_again_not_iwp_client_nossl_key'), false);
			}
		} else {
			iwp_mmb_response(array('error' => 'Invalid parameters received. Please try again.', 'error_code' => 'iwp_mmb_add_site_invalid_parameters_received'), false);
		}
	}
}

if(!function_exists('iwp_mmb_maintain_site')){
	function iwp_mmb_maintain_site($params){
		$check = 1;
		if(get_option('iwp_mmb_maintenance_mode') != $params['maintenance_mode'])
			if(update_option('iwp_mmb_maintenance_mode',$params['maintenance_mode']) ){ $check = 1;}else{$check = 0;}
		if(get_option('iwp_mmb_maintenance_html') != $params['maintenance_html'])
			if(update_option('iwp_mmb_maintenance_html',$params['maintenance_html']) ){ $check = 1;}else{$check = 0;}
		if($check == 1){
			iwp_mmb_response($params, true);
		}else{
			iwp_mmb_response(array('error' => 'Some error with database connection in client site', 'error_code' => 'database_connection_in_client_site'), false);
		}
	}
}


if( !function_exists ( 'iwp_mmb_remove_site' )) {
	function iwp_mmb_remove_site($params)
	{
		extract($params);
		global $iwp_mmb_core;
		$iwp_mmb_core->uninstall( $deactivate );
		
		include_once(ABSPATH . 'wp-admin/includes/plugin.php');
		$plugin_slug = basename(dirname(__FILE__)) . '/' . basename(__FILE__);
		
		if ($deactivate) {
			deactivate_plugins($plugin_slug, true);
		}
		
		if (!is_plugin_active($plugin_slug))
			iwp_mmb_response(array(
				'deactivated' => 'Site removed successfully. <br /><br />InfiniteWP Client plugin successfully deactivated.'
			), true);
		else
			iwp_mmb_response(array(
				'removed_data' => 'Site removed successfully. <br /><br /><b>InfiniteWP Client plugin was not deactivated.</b>'
			), true);
		
	}
}
if( !function_exists ( 'iwp_mmb_stats_get' )) {
	function iwp_mmb_stats_get($params)
	{
		global $iwp_mmb_core;
		$iwp_mmb_core->get_stats_instance();
		iwp_mmb_response($iwp_mmb_core->stats_instance->get($params), true);
	}
}

if( !function_exists ( 'iwp_mmb_client_header' )) {
	function iwp_mmb_client_header()
	{	global $iwp_mmb_core, $current_user;
		
		if(!headers_sent()){
			if(isset($current_user->ID))
				$expiration = time() + apply_filters('auth_cookie_expiration', 10800, $current_user->ID, false);
			else 
				$expiration = time() + 10800;
				
			setcookie(IWP_MMB_XFRAME_COOKIE, md5(IWP_MMB_XFRAME_COOKIE), $expiration, COOKIEPATH, COOKIE_DOMAIN, false, true);
			$_COOKIE[IWP_MMB_XFRAME_COOKIE] = md5(IWP_MMB_XFRAME_COOKIE);
		}
	}
}

if( !function_exists ( 'iwp_mmb_pre_init_stats' )) {
	function iwp_mmb_pre_init_stats( $params )
	{
		global $iwp_mmb_core;
		$iwp_mmb_core->get_stats_instance();
		return $iwp_mmb_core->stats_instance->pre_init_stats($params);
	}
}

if( !function_exists ( 'iwp_mmb_trigger_check' )) {
//backup multi call trigger and status check.
	function iwp_mmb_trigger_check($params)
	{
		global $iwp_mmb_core;
			$iwp_mmb_core->get_backup_instance($params['mechanism']);
		$return = $iwp_mmb_core->backup_instance->trigger_check($params);
		
		if (is_array($return) && array_key_exists('error', $return))
			iwp_mmb_response($return, false);
		else {
			iwp_mmb_response($return, true);
		}
	}
}


if( !function_exists ( 'iwp_mmb_backup_now' )) {
//backup
	function iwp_mmb_backup_now($params)
	{
		global $iwp_mmb_core;
		
		$iwp_mmb_core->get_backup_instance();
		$return = $iwp_mmb_core->backup_instance->backup($params);
		
		if (is_array($return) && array_key_exists('error', $return))
			iwp_mmb_response($return, false);
		else {
			iwp_mmb_response($return, true);
		}
	}
}

if( !function_exists ( 'iwp_mmb_run_task_now' )) {
	function iwp_mmb_run_task_now($params)
	{
		global $iwp_mmb_core;
		$iwp_mmb_core->get_backup_instance($params['mechanism']);
		//$return = $iwp_mmb_core->backup_instance->task_now(); //set_backup_task($params)
		$return = $iwp_mmb_core->backup_instance->set_backup_task($params);
		if (is_array($return) && array_key_exists('error', $return))
			iwp_mmb_response($return, false);
		else {
			iwp_mmb_response($return, true);
		}
	}
}

if( !function_exists ( 'iwp_mmb_delete_task_now' )) {
	function iwp_mmb_delete_task_now($params)
	{
		global $iwp_mmb_core;
		$iwp_mmb_core->get_backup_instance();
		$return = $iwp_mmb_core->backup_instance->delete_task_now($params['task_name']);
		if (is_array($return) && array_key_exists('error', $return))
			iwp_mmb_response($return, false);
		else {
			iwp_mmb_response($return, true);
		}
	}
}
if( !function_exists ( 'iwp_mmb_check_backup_compat' )) {
	function iwp_mmb_check_backup_compat($params)
	{
		global $iwp_mmb_core;
		$iwp_mmb_core->get_backup_instance();
		$return = $iwp_mmb_core->backup_instance->check_backup_compat($params);
		
		if (is_array($return) && array_key_exists('error', $return))
			iwp_mmb_response($return, false);
		else {
			iwp_mmb_response($return, true);
		}
	}
}

if( !function_exists ( 'iwp_mmb_get_backup_req' )) {
	function iwp_mmb_get_backup_req( $params )
	{
		global $iwp_mmb_core;
		$iwp_mmb_core->get_stats_instance();
		$return = $iwp_mmb_core->stats_instance->get_backup_req($params);
		
		if (is_array($return) && array_key_exists('error', $return))
			iwp_mmb_response($return, false);
		else {
		iwp_mmb_response($return, true);
		}
	}
}


if( !function_exists ( 'iwp_mmb_scheduled_backup' )) {
	function iwp_mmb_scheduled_backup($params)
	{
		global $iwp_mmb_core;
		$iwp_mmb_core->get_backup_instance($params['mechanism']);
		$return = $iwp_mmb_core->backup_instance->set_backup_task($params);
		iwp_mmb_response($return, $return);
	}
}


if( !function_exists ( 'iwp_mmb_delete_backup' )) {
	function iwp_mmb_delete_backup($params)
	{
		global $iwp_mmb_core;
		$iwp_mmb_core->get_backup_instance();
		$return = $iwp_mmb_core->backup_instance->delete_backup($params);
		iwp_mmb_response($return, $return);
	}
}

if( !function_exists ( 'iwp_mmb_optimize_tables' )) {
	function iwp_mmb_optimize_tables($params)
	{
		global $iwp_mmb_core;
		$iwp_mmb_core->get_backup_instance();
		$return = $iwp_mmb_core->backup_instance->optimize_tables();
		if ($return)
			iwp_mmb_response($return, true);
		else
			iwp_mmb_response(false, false);
	}
}
if( !function_exists ( 'iwp_mmb_restore_now' )) {
	function iwp_mmb_restore_now($params)
	{
		global $iwp_mmb_core;
		$iwp_mmb_core->get_backup_instance('multiCall');
		$return = $iwp_mmb_core->backup_instance->restore($params);
		
		if (is_array($return) && array_key_exists('error', $return))
			iwp_mmb_response($return, false);
		else
			iwp_mmb_response($return, true);
		
	}
}


if( !function_exists ( 'iwp_mmb_backup_repository' )) {
	function iwp_mmb_backup_repository($params)
	{
		global $iwp_mmb_core;
		$iwp_mmb_core->get_backup_repository_instance();
		$return = $iwp_mmb_core->backup_repository_instance->backup_repository($params);
		if (is_array($return) && array_key_exists('error', $return))
			iwp_mmb_response($return, false);
		else
			iwp_mmb_response($return, true);
	}
}


if( !function_exists ( 'iwp_mmb_clean_orphan_backups' )) {
	function iwp_mmb_clean_orphan_backups()
	{
		global $iwp_mmb_core;
		$backup_instance = $iwp_mmb_core->get_backup_instance();
		$return = $iwp_mmb_core->backup_instance->cleanup();
		if(is_array($return))
			iwp_mmb_response($return, true);
		else
			iwp_mmb_response($return, false);
	}
}



add_filter( 'iwp_website_add', 'iwp_mmb_readd_backup_task' );

if (!function_exists('iwp_mmb_readd_backup_task')) {
	function iwp_mmb_readd_backup_task($params = array()) {
		global $iwp_mmb_core;
		$backup_instance = $iwp_mmb_core->get_backup_instance();
		$settings = $backup_instance->readd_tasks($params);
		return $settings;
	}
}

if( !function_exists ( 'iwp_mmb_update_client_plugin' )) {
	function iwp_mmb_update_client_plugin($params)
	{
		global $iwp_mmb_core;
		iwp_mmb_response($iwp_mmb_core->update_client_plugin($params), true);
	}
}

if( !function_exists ( 'iwp_mmb_wp_checkversion' )) {
	function iwp_mmb_wp_checkversion($params)
	{
		include_once(ABSPATH . 'wp-includes/version.php');
		global $iwp_mmb_wp_version, $iwp_mmb_core;
		iwp_mmb_response($iwp_mmb_wp_version, true);
	}
}
if( !function_exists ( 'iwp_mmb_search_posts_by_term' )) {
	function iwp_mmb_search_posts_by_term($params)
	{
		global $iwp_mmb_core;
		$iwp_mmb_core->get_search_instance();
		
		$search_type = trim($params['search_type']);
		$search_term = strtolower(trim($params['search_term']));

		switch ($search_type){		
			case 'plugin':
				$plugins = get_option('active_plugins');
				
				$have_plugin = false;
				foreach ($plugins as $plugin) {
					if(strpos($plugin, $search_term)>-1){
						$have_plugin = true;
					}
				}
				if($have_plugin){
					iwp_mmb_response(serialize($plugin), true);
				}else{
					iwp_mmb_response(false, false);
				}
				break;
			case 'theme':
				$theme = strtolower(get_option('template'));
				if(strpos($theme, $search_term)>-1){
					iwp_mmb_response($theme, true);
				}else{
					iwp_mmb_response(false, false);
				}
				break;
			default: iwp_mmb_response(false, false);		
		}
		$return = $iwp_mmb_core->search_instance->iwp_mmb_search_posts_by_term($params);
		
		
		
		if ($return_if_true) {
			iwp_mmb_response($return_value, true);
		} else {
			iwp_mmb_response($return_if_false, false);
		}
	}
}

if( !function_exists ( 'iwp_mmb_install_addon' )) {
	function iwp_mmb_install_addon($params)
	{
		global $iwp_mmb_core;
		$iwp_mmb_core->get_installer_instance();
		$return = $iwp_mmb_core->installer_instance->install_remote_file($params);
		iwp_mmb_response($return, true);
		
	}
}

if( !function_exists ( 'iwp_mmb_do_upgrade' )) {
	function iwp_mmb_do_upgrade($params)
	{
		global $iwp_mmb_core, $iwp_mmb_upgrading;
		$iwp_mmb_core->get_installer_instance();
		$return = $iwp_mmb_core->installer_instance->do_upgrade($params);
		iwp_mmb_response($return, true);
		
	}
}

if( !function_exists ( 'iwp_mmb_add_user' )) {
	function iwp_mmb_add_user($params)
	{
		global $iwp_mmb_core;
		$iwp_mmb_core->get_user_instance();
			$return = $iwp_mmb_core->user_instance->add_user($params);
		if (is_array($return) && array_key_exists('error', $return)){
			iwp_mmb_response($return, false);
		}
		else {
			iwp_mmb_response($return, true);
		}
		
	}
}

if( !function_exists ('iwp_mmb_get_users')) {
	function iwp_mmb_get_users($params)
	{
		global $iwp_mmb_core;
		$iwp_mmb_core->get_user_instance();
		$return = $iwp_mmb_core->user_instance->get_users($params);
		if (is_array($return) && array_key_exists('error', $return))
			iwp_mmb_response($return, false);
		else {
			iwp_mmb_response($return, true);
		}
	}
}

if( !function_exists ('iwp_mmb_edit_users')) {
	function iwp_mmb_edit_users($params)
	{
		global $iwp_mmb_core;
		$iwp_mmb_core->get_user_instance();
		$return = $iwp_mmb_core->user_instance->edit_users($params);
		iwp_mmb_response($return, true);
	}
}

if( !function_exists ( 'iwp_mmb_iframe_plugins_fix' )) {
	function iwp_mmb_iframe_plugins_fix($update_actions)
	{
		foreach($update_actions as $key => $action)
		{
			$update_actions[$key] = str_replace('target="_parent"','',$action);
		}
		
		return $update_actions;
		
	}
}

if( !function_exists ( 'iwp_mmb_set_notifications' )) {
	function iwp_mmb_set_notifications($params)
	{
		global $iwp_mmb_core;
		$iwp_mmb_core->get_stats_instance();
			$return = $iwp_mmb_core->stats_instance->set_notifications($params);
		if (is_array($return) && array_key_exists('error', $return))
			iwp_mmb_response($return, false);
		else {
			iwp_mmb_response($return, true);
		}
		
	}
}

if( !function_exists ( 'iwp_mmb_set_alerts' )) {
	function iwp_mmb_set_alerts($params)
	{
		global $iwp_mmb_core;
			$iwp_mmb_core->get_stats_instance();
			$return = $iwp_mmb_core->stats_instance->set_alerts($params);
			iwp_mmb_response(true, true);
	}		
}

/*
if(!function_exists('iwp_mmb_more_reccurences')){
	//Backup Tasks
	add_filter('cron_schedules', 'iwp_mmb_more_reccurences');
	function iwp_mmb_more_reccurences($schedules) {
		$schedules['halfminute'] = array('interval' => 30, 'display' => 'Once in a half minute');
		$schedules['minutely'] = array('interval' => 60, 'display' => 'Once in a minute');
		$schedules['fiveminutes'] = array('interval' => 300, 'display' => 'Once every five minutes');
		$schedules['tenminutes'] = array('interval' => 600, 'display' => 'Once every ten minutes');
		
		return $schedules;
	}
}
	
	add_action('iwp_client_backup_tasks', 'iwp_client_check_backup_tasks');

if( !function_exists('iwp_client_check_backup_tasks') ){
 	function iwp_client_check_backup_tasks() {
		global $iwp_mmb_core, $_wp_using_ext_object_cache;
		$_wp_using_ext_object_cache = false;
		
		$iwp_mmb_core->get_backup_instance();
		$iwp_mmb_core->backup_instance->check_backup_tasks();
	}
}
*/
	
if( !function_exists('iwp_check_notifications') ){
 	function iwp_check_notifications() {
		global $iwp_mmb_core, $_wp_using_ext_object_cache;
		$_wp_using_ext_object_cache = false;
		
		$iwp_mmb_core->get_stats_instance();
		$iwp_mmb_core->stats_instance->check_notifications();
	}
}


if( !function_exists('iwp_mmb_get_plugins_themes') ){
 	function iwp_mmb_get_plugins_themes($params) {
		global $iwp_mmb_core;
		$iwp_mmb_core->get_installer_instance();
		$return = $iwp_mmb_core->installer_instance->get($params);
		iwp_mmb_response($return, true);
	}
}

if( !function_exists('iwp_mmb_edit_plugins_themes') ){
 	function iwp_mmb_edit_plugins_themes($params) {
		global $iwp_mmb_core;
		$iwp_mmb_core->get_installer_instance();
		$return = $iwp_mmb_core->installer_instance->edit($params);
		iwp_mmb_response($return, true);
	}
}

//post
if( !function_exists ( 'iwp_mmb_post_create' )) {
	function iwp_mmb_post_create($params)
	{
		global $iwp_mmb_core;
		$iwp_mmb_core->get_post_instance();
		$return = $iwp_mmb_core->post_instance->create($params);
		if (is_int($return))
			iwp_mmb_response($return, true);
		else{
			if(isset($return['error'])){
				iwp_mmb_response($return, false);
			} else {
				iwp_mmb_response($return, false);
			}
		}
	}
}

if( !function_exists ( 'iwp_mmb_change_post_status' )) {
	function iwp_mmb_change_post_status($params)
	{
		global $iwp_mmb_core;
		$iwp_mmb_core->get_post_instance();
		$return = $iwp_mmb_core->post_instance->change_status($params);
		//mmb_response($return, true);

	}
}

if( !function_exists ('iwp_mmb_get_posts')) {
	function iwp_mmb_get_posts($params)
	{
		global $iwp_mmb_core;
		$iwp_mmb_core->get_post_instance();
		
			$return = $iwp_mmb_core->post_instance->get_posts($params);
		if (is_array($return) && array_key_exists('error', $return))
			iwp_mmb_response($return, false);
		else {
			iwp_mmb_response($return, true);
		}
	}
}

if( !function_exists ('iwp_mmb_delete_post')) {
	function iwp_mmb_delete_post($params)
	{
		global $iwp_mmb_core;
		$iwp_mmb_core->get_post_instance();
		
			$return = $iwp_mmb_core->post_instance->delete_post($params);
		if (is_array($return) && array_key_exists('error', $return))
			iwp_mmb_response($return, false);
		else {
			iwp_mmb_response($return, true);
		}
	}
}

if( !function_exists ('iwp_mmb_delete_posts')) {
	function iwp_mmb_delete_posts($params)
	{
		global $iwp_mmb_core;
		$iwp_mmb_core->get_post_instance();
		
			$return = $iwp_mmb_core->post_instance->delete_posts($params);
		if (is_array($return) && array_key_exists('error', $return))
			iwp_mmb_response($return, false);
		else {
			iwp_mmb_response($return, true);
		}
	}
}

if( !function_exists ('iwp_mmb_edit_posts')) {
	function iwp_mmb_edit_posts($params)
	{
		global $iwp_mmb_core;
		$iwp_mmb_core->get_posts_instance();
		$return = $iwp_mmb_core->posts_instance->edit_posts($params);
		iwp_mmb_response($return, true);
	}
}

if( !function_exists ('iwp_mmb_get_pages')) {
	function iwp_mmb_get_pages($params)
	{
		global $iwp_mmb_core;
		$iwp_mmb_core->get_post_instance();
		
			$return = $iwp_mmb_core->post_instance->get_pages($params);
		if (is_array($return) && array_key_exists('error', $return))
			iwp_mmb_response($return, false);
		else {
			iwp_mmb_response($return, true);
		}
	}
}

if( !function_exists ('iwp_mmb_delete_page')) {
	function iwp_mmb_delete_page($params)
	{
		global $iwp_mmb_core;
		$iwp_mmb_core->get_post_instance();
		
			$return = $iwp_mmb_core->post_instance->delete_page($params);
		if (is_array($return) && array_key_exists('error', $return))
			iwp_mmb_response($return, false);
		else {
			iwp_mmb_response($return, true);
		}
	}
}


//links
if( !function_exists ('iwp_mmb_get_links')) {
	function iwp_mmb_get_links($params)
	{
		global $iwp_mmb_core;
		$iwp_mmb_core->get_link_instance();
			$return = $iwp_mmb_core->link_instance->get_links($params);
		if (is_array($return) && array_key_exists('error', $return))
			iwp_mmb_response($return, false);
		else {
			iwp_mmb_response($return, true);
		}
	}
}

if( !function_exists ( 'iwp_mmb_add_link' )) {
	function iwp_mmb_add_link($params)
	{
		global $iwp_mmb_core;
		$iwp_mmb_core->get_link_instance();
			$return = $iwp_mmb_core->link_instance->add_link($params);
		if (is_array($return) && array_key_exists('error', $return)){
			iwp_mmb_response($return, false);
		}
		else {
			iwp_mmb_response($return, true);
		}
		
	}
}

if( !function_exists ('iwp_mmb_delete_link')) {
	function iwp_mmb_delete_link($params)
	{
		global $iwp_mmb_core;
		$iwp_mmb_core->get_link_instance();
		
			$return = $iwp_mmb_core->link_instance->delete_link($params);
		if (is_array($return) && array_key_exists('error', $return)){
			iwp_mmb_response($return, false);
		}
		else {
			iwp_mmb_response($return, true);
		}
	}
}

if( !function_exists ('iwp_mmb_delete_links')) {
	function iwp_mmb_delete_links($params)
	{
		global $iwp_mmb_core;
		$iwp_mmb_core->get_link_instance();
		
			$return = $iwp_mmb_core->link_instance->delete_links($params);
		if (is_array($return) && array_key_exists('error', $return)){
			iwp_mmb_response($return, false);
		}
		else {
			iwp_mmb_response($return, true);
		}
	}
}


//comments
if( !function_exists ( 'iwp_mmb_change_comment_status' )) {
	function iwp_mmb_change_comment_status($params)
	{
		global $iwp_mmb_core;
		$iwp_mmb_core->get_comment_instance();
		$return = $iwp_mmb_core->comment_instance->change_status($params);
		//mmb_response($return, true);
		if ($return){
			$iwp_mmb_core->get_stats_instance();
			iwp_mmb_response($iwp_mmb_core->stats_instance->get_comments_stats($params), true);
		}else
			iwp_mmb_response(array('error' => 'Comment not updated', 'error_code' => 'comment_not_updated'), false);
	}

}
if( !function_exists ( 'iwp_mmb_comment_stats_get' )) {
	function iwp_mmb_comment_stats_get($params)
	{
		global $iwp_mmb_core;
		$iwp_mmb_core->get_stats_instance();
		iwp_mmb_response($iwp_mmb_core->stats_instance->get_comments_stats($params), true);
	}
}

if( !function_exists ('iwp_mmb_get_comments')) {
	function iwp_mmb_get_comments($params)
	{
		global $iwp_mmb_core;
		$iwp_mmb_core->get_comment_instance();
			$return = $iwp_mmb_core->comment_instance->get_comments($params);
		if (is_array($return) && array_key_exists('error', $return))
			iwp_mmb_response($return, false);
		else {
			iwp_mmb_response($return, true);
		}
	}
}

if( !function_exists ('iwp_mmb_action_comment')) {
	function iwp_mmb_action_comment($params)
	{
		global $iwp_mmb_core;
		$iwp_mmb_core->get_comment_instance();
		
			$return = $iwp_mmb_core->comment_instance->action_comment($params);
		if (is_array($return) && array_key_exists('error', $return))
			iwp_mmb_response($return, false);
		else {
			iwp_mmb_response($return, true);
		}
	}
}

if( !function_exists ('iwp_mmb_bulk_action_comments')) {
	function iwp_mmb_bulk_action_comments($params)
	{
		global $iwp_mmb_core;
		$iwp_mmb_core->get_comment_instance();
		
			$return = $iwp_mmb_core->comment_instance->bulk_action_comments($params);
		if (is_array($return) && array_key_exists('error', $return))
			iwp_mmb_response($return, false);
		else {
			iwp_mmb_response($return, true);
		}
	}
}

if( !function_exists ('iwp_mmb_reply_comment')) {
	function iwp_mmb_reply_comment($params)
	{
		global $iwp_mmb_core;
		$iwp_mmb_core->get_comment_instance();
		
		$return = $iwp_mmb_core->comment_instance->reply_comment($params);
		if (is_array($return) && array_key_exists('error', $return))
			iwp_mmb_response($return, false);
		else {
			iwp_mmb_response($return, true);
		}
	}
}

//Comments-End-

//WP-Optimize

if( !function_exists('iwp_mmb_wp_optimize')){
	function iwp_mmb_wp_optimize($params){
		global $iwp_mmb_core;
		$iwp_mmb_core->wp_optimize_instance();
		
		$return = $iwp_mmb_core->optimize_instance->cleanup_system($params);
		if (is_array($return) && array_key_exists('error', $return))
			iwp_mmb_response($return, false);
		else {
			iwp_mmb_response($return, true);
		}
	}
}

//WP-Optimize_end

/*
 *WordFence Addon Start 
 */

if( !function_exists('iwp_mmb_wordfence_scan')){
	function iwp_mmb_wordfence_scan($params){
		global $iwp_mmb_core,$iwp_mmb_plugin_dir;
                require_once("$iwp_mmb_plugin_dir/addons/wordfence/wordfence.class.php");
		$iwp_mmb_core->get_wordfence_instance();
		
		$return = $iwp_mmb_core->wordfence_instance->scan($params);
		if (is_array($return) && array_key_exists('error', $return))
			iwp_mmb_response($return, false);
		else {
			iwp_mmb_response($return, true);
		}
	}
}

if( !function_exists('iwp_mmb_wordfence_load')){
	function iwp_mmb_wordfence_load($params){
		global $iwp_mmb_core,$iwp_mmb_plugin_dir;
                require_once("$iwp_mmb_plugin_dir/addons/wordfence/wordfence.class.php");
		$iwp_mmb_core->get_wordfence_instance();
		
		$return = $iwp_mmb_core->wordfence_instance->load($params);
		if (is_array($return) && array_key_exists('error', $return))
			iwp_mmb_response($return, false);
		else {
			iwp_mmb_response($return, true);
		}
	}
}
 
/*
 *WordFence Addon End 
 */


/*
 * iTheams Security Addon Start here
 */

if(!function_exists('iwp_mmb_ithemes_security_load')) {
    function iwp_mmb_ithemes_security_load() {
        if(iwp_mmb_ithemes_security_check()) {
            include_once(ABSPATH . "wp-includes/pluggable.php"); 
            //$ITSECdashboard = new ITSEC_Dashboard_Admin( new ITSEC_Core(WP_PLUGIN_DIR . '/better-wp-security/better-wp-security.php', __( 'iThemes Security', 'it-l10n-better-wp-security' )) );
            //add_action( 'itsec_add_admin_meta_boxes', array( $ITSECdashboard, 'add_admin_meta_boxes' ) );
            $statuses = array(
			'safe-high'   => array(),
			'high'        => array(),
			'safe-medium' => array(),
			'medium'      => array(),
			'safe-low'    => array(),
			'low'         => array(),
		);

		$statuses = apply_filters( 'itsec_add_dashboard_status', $statuses );
                iwp_mmb_response($statuses, true);
        }
    }
}

/*
* return the iTheams Security is load or not
*/
if(!function_exists('iwp_mmb_ithemes_security_check')) {
	function iwp_mmb_ithemes_security_check() {
		  include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		  if ( is_plugin_active( 'better-wp-security/better-wp-security.php' ) ) {
				  @include_once(WP_PLUGIN_DIR . '/better-wp-security/better-wp-security.php');
				  if (class_exists('ITSEC_Core')) {
						return true;
				  } else {
						return false;
				  }
		  }
		  elseif ( is_plugin_active( 'ithemes-security-pro/ithemes-security-pro.php' ) ) {
				  @include_once(WP_PLUGIN_DIR . '/ithemes-security-pro/ithemes-security-pro.php');
				  if (class_exists('ITSEC_Core')) {
						return true;
				  } else {
						return false;
				  }
		  }
		  else {
				return false;
		  }
	}
}

/*
 * iTheams Security Addon End here
 */

//WP-BrokenLinks start

if( !function_exists('iwp_mmb_get_all_links')){
	function iwp_mmb_get_all_links(){
		global $iwp_mmb_core;
		$iwp_mmb_core->wp_blc_get_blinks();
		$return = $iwp_mmb_core->blc_get_blinks->blc_get_all_links($params);
		if (is_array($return) && array_key_exists('error', $return))
			iwp_mmb_response($return, false);
		else {
			iwp_mmb_response($return, true);
		}
	}
}

if( !function_exists('iwp_mmb_update_broken_link')){
	function iwp_mmb_update_broken_link($params){
		global $iwp_mmb_core;
		$iwp_mmb_core->wp_blc_get_blinks();
		$return = $iwp_mmb_core->blc_get_blinks->blc_update_link($params);
		if (is_array($return) && array_key_exists('error', $return))
			iwp_mmb_response($return, false);
		else {
			iwp_mmb_response($return, true);
		}
	}
}

if( !function_exists('iwp_mmb_unlink_broken_link')){
	function iwp_mmb_unlink_broken_link($params){
		global $iwp_mmb_core;
		$iwp_mmb_core->wp_blc_get_blinks();
		$return = $iwp_mmb_core->blc_get_blinks->blc_unlink($params);
		if (is_array($return) && array_key_exists('error', $return))
			iwp_mmb_response($return, false);
		else {
			iwp_mmb_response($return, true);
		}
	}
}

if( !function_exists('iwp_mmb_markasnot_broken_link')){
	function iwp_mmb_markasnot_broken_link($params){
		global $iwp_mmb_core;
		$iwp_mmb_core->wp_blc_get_blinks();
		$return = $iwp_mmb_core->blc_get_blinks->blc_mark_as_not_broken($params);
		if (is_array($return) && array_key_exists('error', $return))
			iwp_mmb_response($return, false);
		else {
			iwp_mmb_response($return, true);
		}
	}
}

if( !function_exists('iwp_mmb_dismiss_broken_link')){
	function iwp_mmb_dismiss_broken_link($params){
		global $iwp_mmb_core;
		$iwp_mmb_core->wp_blc_get_blinks();
		$return = $iwp_mmb_core->blc_get_blinks->blc_dismiss_link($params);
		if (is_array($return) && array_key_exists('error', $return))
			iwp_mmb_response($return, false);
		else {
			iwp_mmb_response($return, true);
		}
	}
}

if( !function_exists('iwp_mmb_undismiss_broken_link')){
	function iwp_mmb_undismiss_broken_link($params){
		global $iwp_mmb_core;
		$iwp_mmb_core->wp_blc_get_blinks();
		$return = $iwp_mmb_core->blc_get_blinks->blc_undismiss_link($params);
		if (is_array($return) && array_key_exists('error', $return))
			iwp_mmb_response($return, false);
		else {
			iwp_mmb_response($return, true);
		}
	}
}

if( !function_exists('iwp_mmb_bulk_actions_processor')){
	function iwp_mmb_bulk_actions_processor($params){
		global $iwp_mmb_core;
		$iwp_mmb_core->wp_blc_get_blinks();
		$return = $iwp_mmb_core->blc_get_blinks->blc_bulk_actions($params);
		if (is_array($return) && array_key_exists('error', $return))
			iwp_mmb_response($return, false);
		else {
			iwp_mmb_response($return, true);
		}
	}
}

//WP-BrokenLinks end

//WP-GWMTools start

if( !function_exists('iwp_mmb_gwmt_redirect_url')){
	function iwp_mmb_gwmt_redirect_url($params){
		global $iwp_mmb_core;
		$iwp_mmb_core->wp_google_webmasters_crawls();
		$return = $iwp_mmb_core->get_google_webmasters_crawls->google_webmasters_redirect($params);
		if (is_array($return) && array_key_exists('error', $return))
			iwp_mmb_response($return, false);
		else {
			iwp_mmb_response($return, true);
		}
	}
}

if( !function_exists('iwp_mmb_gwmt_redirect_url_again')){
	function iwp_mmb_gwmt_redirect_url_again($params){
		global $iwp_mmb_core;
		$iwp_mmb_core->wp_google_webmasters_crawls();
		$return = $iwp_mmb_core->get_google_webmasters_crawls->google_webmasters_redirect_again($params);
		if (is_array($return) && array_key_exists('error', $return))
			iwp_mmb_response($return, false);
		else {
			iwp_mmb_response($return, true);
		}
	}
}


//WP-GWMTools end

//fileEditor start

if( !function_exists('iwp_mmb_file_editor_upload')){
	function iwp_mmb_file_editor_upload($params){
		global $iwp_mmb_core;
		$iwp_mmb_core->wp_get_file_editor();
		$return = $iwp_mmb_core->get_file_editor->file_editor_upload($params);
		if (is_array($return) && array_key_exists('error', $return))
			iwp_mmb_response($return, false);
		else {
			iwp_mmb_response($return, true);
		}
	}
}


//fileEditor end

//yoastWpSeo start
if( !function_exists('iwp_mmb_yoast_get_seo_info')){
	function iwp_mmb_yoast_get_seo_info($params){
		global $iwp_mmb_core;
		$iwp_mmb_core->wp_get_yoast_seo();
		$return = $iwp_mmb_core->get_yoast_seo->get_seo_info($params);
		if (is_array($return) && array_key_exists('error', $return))
			iwp_mmb_response($return, false);
		else {
			iwp_mmb_response($return, true);
		}
	}
}
if( !function_exists('iwp_mmb_yoast_save_seo_info')){
	function iwp_mmb_yoast_save_seo_info($params){
		global $iwp_mmb_core;
		$iwp_mmb_core->wp_get_yoast_seo();
		$return = $iwp_mmb_core->get_yoast_seo->save_seo_info($params);
		if (is_array($return) && array_key_exists('error', $return))
			iwp_mmb_response($return, false);
		else {
			iwp_mmb_response($return, true);
		}
	}
}
//yoastWpSeo end

if( !function_exists('iwp_mmb_maintenance_mode')){
 	function iwp_mmb_maintenance_mode( $params ) {
		global $wp_object_cache;
		
		$default = get_option('iwp_client_maintenace_mode');
		$params = empty($default) ? $params : array_merge($default, $params);
		update_option("iwp_client_maintenace_mode", $params);
		
		if(!empty($wp_object_cache))
			@$wp_object_cache->flush(); 
		iwp_mmb_response(true, true);
	}
}

if( !function_exists('iwp_mmb_plugin_actions') ){
 	function iwp_mmb_plugin_actions() {
		global $iwp_mmb_actions, $iwp_mmb_core;
		
		if(!empty($iwp_mmb_actions)){
			global $_iwp_mmb_plugin_actions;
			if(!empty($_iwp_mmb_plugin_actions)){
				$failed = array();
				foreach($_iwp_mmb_plugin_actions as $action => $params){
					if(isset($iwp_mmb_actions[$action]))
						call_user_func($iwp_mmb_actions[$action], $params);
					else 
						$failed[] = $action;
				}
				if(!empty($failed)){
					$f = implode(', ', $failed);
					$s = count($f) > 1 ? 'Actions "' . $f . '" do' : 'Action "' . $f . '" does';
					iwp_mmb_response(array('error' => $s.' not exist. Please update your IWP Client plugin.', 'error_code' => 'update_your_client_plugin'), false);
				}
					
			}
		}
		
		global $pagenow, $current_user, $mmode;
		if( !is_admin() && !in_array($pagenow, array( 'wp-login.php' ))){
			$mmode = get_option('iwp_client_maintenace_mode');
			if( !empty($mmode) ){
				if(isset($mmode['active']) && $mmode['active'] == true){
					if(isset($current_user->data) && !empty($current_user->data) && isset($mmode['hidecaps']) && !empty($mmode['hidecaps'])){
						$usercaps = array();
						if(isset($current_user->caps) && !empty($current_user->caps)){
							$usercaps = $current_user->caps;
						}
						foreach($mmode['hidecaps'] as $cap => $hide){
							if(!$hide)
								continue;
							
							foreach($usercaps as $ucap => $val){
								if($ucap == $cap){
									ob_end_clean();
									ob_end_flush();
									die($mmode['template']);
								}
							}
						}
					} else
						die($mmode['template']);
				}
			}
		}
	}
} 

if( !function_exists ( 'iwp_mmb_execute_php_code' )) {
	function iwp_mmb_execute_php_code($params)
	{ 		
		ob_start();
		eval($params['code']);
		$return = ob_get_flush();
		iwp_mmb_response(print_r($return, true), true);
	}
}

if( !function_exists('iwp_mmb_client_brand')){
 	function iwp_mmb_client_brand($params) {
		update_option("iwp_client_brand",$params['brand']);
		iwp_mmb_response(true, true);
	}
}


if(!function_exists('checkOpenSSL')){
	function checkOpenSSL(){
	if(!function_exists('openssl_verify')){
		return false;
	}
	else{
		//$ossl_err = @openssl_error_string();if($ossl_err!=false) return false;
		$key = @openssl_pkey_new();

		//$ossl_err = @openssl_error_string();if($ossl_err!=false) return false;
		@openssl_pkey_export($key, $privateKey);
		$privateKey	= base64_encode($privateKey);

		//$ossl_err = @openssl_error_string();if($ossl_err!=false) return false;
		$publicKey = @openssl_pkey_get_details($key);
		
		//$ossl_err = @openssl_error_string();if($ossl_err!=false) return false;
		$publicKey 	= $publicKey["key"];
		
		if(empty($publicKey) || empty($privateKey)){
			return false;
		}
	}
	return true;
  }
}


if(!function_exists('iwp_mmb_shutdown')){
	function iwp_mmb_shutdown(){
		$isError = false;
	
		if ($error = error_get_last()){
		switch($error['type']){
			/*case E_PARSE:*/
			case E_ERROR:
			case E_CORE_ERROR:
			case E_COMPILE_ERROR:
			case E_USER_ERROR:
				$isError = true;
				break;
			}
		}
		if ($isError){
			
			$response = '<span style="font-weight:700;">PHP Fatal error occurred:</span> '.$error['message'].' in '.$error['file'].' on line '.$error['line'].'.';
			if(stripos($error['message'], 'allowed memory size') !== false){
				$response .= '<br>Try <a href="http://infinitewp.com/knowledge-base/increase-memory-limit/?utm_source=application&utm_medium=userapp&utm_campaign=kb" target="_blank">increasing the PHP memory limit</a> for this WP site.';
			}
			if(!$GLOBALS['IWP_RESPONSE_SENT']){
				iwp_mmb_response(array('error' => $response, 'error_code' => 'iwp_mmb_shutdown'), false);
			}
			
		}
	}
}


if(!function_exists('iwp_mmb_print_flush')){
	function iwp_mmb_print_flush($print_string){// this will help responding web server, will keep alive the script execution
		
		echo $print_string." ||| ";
		echo "TT:".(microtime(1) - $GLOBALS['IWP_MMB_PROFILING']['ACTION_START'])."\n";
		ob_flush();
		flush();
	}
}

if(!function_exists('iwp_mmb_auto_print')){
	function iwp_mmb_auto_print($unique_task){// this will help responding web server, will keep alive the script execution
		$print_every_x_secs = 5;
		
		$current_time = microtime(1);
		if(!$GLOBALS['IWP_MMB_PROFILING']['TASKS'][$unique_task]['START']){
			$GLOBALS['IWP_MMB_PROFILING']['TASKS'][$unique_task]['START'] = $current_time;	
		}
		
		if(!$GLOBALS['IWP_MMB_PROFILING']['LAST_PRINT'] || ($current_time - $GLOBALS['IWP_MMB_PROFILING']['LAST_PRINT']) > $print_every_x_secs){
			
			//$print_string = "TT:".($current_time - $GLOBALS['IWP_MMB_PROFILING']['ACTION_START'])."\n";
			$print_string = $unique_task." TT:".($current_time - $GLOBALS['IWP_MMB_PROFILING']['TASKS'][$unique_task]['START']);
			iwp_mmb_print_flush($print_string);
			$GLOBALS['IWP_MMB_PROFILING']['LAST_PRINT'] = $current_time;		
		}
	}
}

if(!function_exists('iwp_mmb_check_maintenance')){
	function iwp_mmb_check_maintenance(){
		global $wpdb;
		if(get_option('iwp_mmb_maintenance_mode')){
			$html_maintenance = get_option('iwp_mmb_maintenance_html');
			echo $html_maintenance;
			exit;
		}
	}
}

if(!function_exists('iwp_mmb_check_redirects')){
	function iwp_mmb_check_redirects(){
		global $wpdb;
		$current_url = ($_SERVER['SERVER_PORT']=='443'?'https://':'http://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$current_url = rtrim($current_url,'/');
		$table_name = $wpdb->base_prefix."iwp_redirects";
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
			$success = $wpdb -> get_col($wpdb->prepare("SELECT redirectLink FROM ".$wpdb->base_prefix."iwp_redirects WHERE oldLink = %s LIMIT 1",$current_url));
			if(count($success)){
				if(function_exists(wp_redirect)){
					wp_redirect($success[0]);	
				}
			}
		}
	}
}

if(!function_exists('iwp_mmb_convert_data')){
	function iwp_mmb_convert_data(){
		
		//Schedule backup key need to save .
		global $wpdb;
		
		$client_backup_tasks = get_option('iwp_client_backup_tasks');
		
		$type = $action = $category = '';
		
		if(!empty($client_backup_tasks) && is_array($client_backup_tasks)){
			foreach($client_backup_tasks as $key){
				if(!is_array($key) || !is_array($key['task_args'])){
					continue;
				}
				$task_name = $key['task_args']['task_name'];
				
				if($task_name == 'Backup Now'){
					$type = 'backup';
					$action = 'now';
					$category = $key['task_args']['what'];
				}
				else{
					$type = 'scheduleBackup';
					$action = 'runTask';
					$category = $key['task_args']['what'];
				}
				if(is_array($key['task_results'])){
					$taskResultData = array();
					foreach($key['task_results'] as $keys => $task_results){
												
						$historyID = $task_results['backhack_status']['adminHistoryID'];
						
						$taskResultData = array('task_results' => array($historyID => $task_results));
						$taskResultData['task_results'][$historyID]['adminHistoryID'] = $historyID;
						
						$insert  = $wpdb->insert($wpdb->base_prefix.'iwp_backup_status',array( 'stage' => 'finished', 'status' => 'completed',  'action' => $action, 'type' => $type,'category' => $category ,'historyID' => $task_results['backhack_status']['adminHistoryID'],'finalStatus' => 'completed','startTime' => $task_results['time'],'endTime' => $task_results['time'],'statusMsg' => $statusArray['statusMsg'],'requestParams' => serialize($key),'taskName' => $task_name, 'responseParams' => '', 'taskResults' =>  serialize($taskResultData)), array( '%s', '%s','%s', '%s', '%s', '%s', '%d', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s') );
						
					}
				}
			}
		}
	}
}

if (!function_exists('iwp_mmb_backup_db_changes')) {
	function iwp_mmb_backup_db_changes(){
		$IWP_MMB_BACKUP_TABLE_VERSION =	iwp_mmb_get_site_option('iwp_backup_table_version');
		if (empty($IWP_MMB_BACKUP_TABLE_VERSION) || $IWP_MMB_BACKUP_TABLE_VERSION == false ) {
			iwp_mmb_create_backup_status_table();
		}
		if(version_compare(iwp_mmb_get_site_option('iwp_backup_table_version'), '1.1.2', '<')){
			iwp_mmb_change_collation_backup_status_table();
		}
		if(version_compare(iwp_mmb_get_site_option('iwp_backup_table_version'), '1.1.3', '<')){
			iwp_mmb_add_lastUpdateTime_column_backup_status_table();
		}
	}
}

if(!function_exists('iwp_mmb_create_backup_status_table')){
	//write new backup_status_table changes also in this function.
	function iwp_mmb_create_backup_status_table(){
		global $wpdb;
		if(method_exists($wpdb, 'get_charset_collate')){
			$charset_collate = $wpdb->get_charset_collate();
		}

		$table_name = $wpdb->base_prefix . "iwp_backup_status";

		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name){
			if (!empty($charset_collate)){
				$cachecollation = $charset_collate;
			}
			else{
				$cachecollation = ' DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci ';
			}

			$sql = "
				CREATE TABLE IF NOT EXISTS $table_name (
				  `ID` int(11) NOT NULL AUTO_INCREMENT,
				  `historyID` int(11) NOT NULL,
				  `taskName` varchar(255) NOT NULL,
				  `action` varchar(50) NOT NULL,
				  `type` varchar(50) NOT NULL,
				  `category` varchar(50) NOT NULL,
				  `stage` varchar(255) NOT NULL,
				  `status` varchar(255) NOT NULL,
				  `finalStatus` varchar(50) DEFAULT NULL,
				  `statusMsg` varchar(255) NOT NULL,
				  `requestParams` text NOT NULL,
				  `responseParams` longtext,
				  `taskResults` text,
				  `startTime` int(11) DEFAULT NULL,
				  `lastUpdateTime` int(10) unsigned DEFAULT NULL,
				  `endTime` int(11) NOT NULL,
				  PRIMARY KEY (`ID`)
				)".$cachecollation." ;
			";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );

			if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
				update_option( "iwp_backup_table_version", '1.1.3');
			}
		}
	}
}

if(!function_exists('iwp_mmb_change_collation_backup_status_table')){
	function iwp_mmb_change_collation_backup_status_table(){
		global $wpdb;
		if(method_exists($wpdb, 'get_charset_collate')){
			$charset_collate = $wpdb->get_charset_collate();
		}
		
		$table_name = $wpdb->base_prefix . "iwp_backup_status";
		
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
			if (!empty($charset_collate)){
			    $cachecollation_table = $charset_collate;
				$cachecollation = str_ireplace('DEFAULT ', '', $charset_collate);
			}
			else{
				$cachecollation = ' CHARACTER SET utf8 COLLATE utf8_general_ci ';
				$cachecollation_table = $cachecollation;
			}
	
			$sql = array();
			
			$sql[] = "alter table " . $table_name . " change `taskName` `taskName` VARBINARY(255);";
			$sql[] = "alter table " . $table_name . " change `taskName` `taskName` VARCHAR(255) $cachecollation not null;";
				
			$sql[] = "alter table " . $table_name . " change action action VARBINARY(50);";
			$sql[] = "alter table " . $table_name . " change action action VARCHAR(50) $cachecollation not null ;";
			
			$sql[] = "alter table " . $table_name . " change type type VARBINARY(50);";
			$sql[] = "alter table " . $table_name . " change type type VARCHAR(50) $cachecollation not null ;";
			
			$sql[] = "alter table " . $table_name . " change category category VARBINARY(50);";
			$sql[] = "alter table " . $table_name . " change category category VARCHAR(50) $cachecollation not null ;";
			
			$sql[] = "alter table " . $table_name . " change stage stage VARBINARY(255);";
			$sql[] = "alter table " . $table_name . " change stage stage VARCHAR(255) $cachecollation not null ;";
			
			$sql[] = "alter table " . $table_name . " change status status VARBINARY(255);";
			$sql[] = "alter table " . $table_name . " change status status VARCHAR(255) $cachecollation not null ;";
			
			$sql[] = "alter table " . $table_name . " change finalStatus finalStatus VARBINARY(50);";
			$sql[] = "alter table " . $table_name . " change finalStatus finalStatus VARCHAR(50) $cachecollation default null ;";
			
			$sql[] = "alter table " . $table_name . " change statusMsg statusMsg VARBINARY(255);";
			$sql[] = "alter table " . $table_name . " change statusMsg statusMsg VARCHAR(255) $cachecollation not null ;";
			
			$sql[] = "alter table " . $table_name . " change requestParams requestParams BLOB;";
			$sql[] = "alter table " . $table_name . " change requestParams requestParams TEXT $cachecollation not null;";
			
			$sql[] = "alter table " . $table_name . " change responseParams responseParams LONGBLOB;";
			$sql[] = "alter table " . $table_name . " change responseParams responseParams LONGTEXT $cachecollation ;";
			
			$sql[] = "alter table " . $table_name . " change taskResults taskResults BLOB;";
			$sql[] = "alter table " . $table_name . " change taskResults taskResults TEXT $cachecollation ;";
			
			$sql[] = "ALTER TABLE " . $table_name . " $cachecollation_table ;";
			$this_reurn = array();
			foreach($sql as $v){
				//global $wpdb;
				$this_reurn[] = $wpdb->query($v);
			}
			update_option( "iwp_backup_table_version", '1.1.2');
		}
	}
}

if(!function_exists('iwp_mmb_add_lastUpdateTime_column_backup_status_table')){
	function iwp_mmb_add_lastUpdateTime_column_backup_status_table(){
		global $wpdb;
		$table_name = $wpdb->base_prefix . "iwp_backup_status";
		if($wpdb->get_var("SHOW COLUMNS FROM `$table_name` WHERE Field = 'lastUpdateTime'")){
			update_option( "iwp_backup_table_version", '1.1.3');
			return false;
		}
		$sql = "ALTER TABLE ".$table_name." ADD `lastUpdateTime` INT(10) UNSIGNED NULL;";
		$isDone = $wpdb->query($sql);
		if ($isDone) {
			update_option( "iwp_backup_table_version", '1.1.3');
		}

	}
}

//-------------------------------------------------------------------

//-Function name - iwp_mmb_get_file_size()
//-This is the alternate function to calculate file size 
//-This function is introduced to support the filesize calculation for the files which are larger than 2048MB

//----------------------------------------------------------------------

if(!function_exists('iwp_mmb_get_file_size')){
	function iwp_mmb_get_file_size($file)
	{
		clearstatcache();
		$normal_file_size = filesize($file);
		if(($normal_file_size !== false)&&($normal_file_size >= 0))
		{
			return $normal_file_size;
		}
		else
		{
			$file = realPath($file);
			if(!$file)
			{
				echo 'iwp_mmb_get_file_size_error : realPath error';
			}
			$ch = curl_init("file://" . $file);
			curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_FILE);
			curl_setopt($ch, CURLOPT_NOBODY, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, true);
			$data = curl_exec($ch);
			$curl_error = curl_error($ch);
			curl_close($ch);
			if ($data !== false && preg_match('/Content-Length: (\d+)/', $data, $matches)) {
				return (string) $matches[1];
			}
			else
			{
				echo 'iwp_mmb_get_file_size_error : '.$curl_error;
				return $normal_file_size;
			}
		}
	}
}

if( !function_exists('iwp_mmb_backup_test_site')){
	function iwp_mmb_backup_test_site($params){
		global $iwp_mmb_core,$iwp_mmb_plugin_dir;
                $return = array();
                
                $iwp_mmb_core->get_backup_instance();
		$return = $iwp_mmb_core->backup_instance->check_backup_compat($params);
                
		if (is_array($return) && array_key_exists('error', $return))
			iwp_mmb_response($return, false);
		else {
			iwp_mmb_response($return, true);
		}
	}
}

//add_action( 'plugins_loaded', 'iwp_mmb_create_backup_table' );

//register_activation_hook( __FILE__, 'iwp_mmb_create_backup_table' );

$iwp_mmb_core = new IWP_MMB_Core();
$GLOBALS['iwp_mmb_activities_log'] = new IWP_MMB_Activities_log();
$mmb_core = 1;
$GLOBALS['iwp_activities_log_post_type'] = 'iwp_log';

if(isset($_GET['auto_login']))
	$iwp_mmb_core->automatic_login();	

if (function_exists('register_activation_hook'))
    register_activation_hook( __FILE__ , array( $iwp_mmb_core, 'install' ));

if (function_exists('register_deactivation_hook'))
    register_deactivation_hook(__FILE__, array( $iwp_mmb_core, 'uninstall' ));

if (function_exists('add_action'))
	add_action('init', 'iwp_mmb_plugin_actions', 99999);

if (function_exists('add_action'))
	add_action('template_redirect', 'iwp_mmb_check_maintenance', 99999);

if (function_exists('add_action'))
	add_action('template_redirect', 'iwp_mmb_check_redirects', 99999);

if (function_exists('add_filter'))
	add_filter('install_plugin_complete_actions','iwp_mmb_iframe_plugins_fix');
	
if(	isset($_COOKIE[IWP_MMB_XFRAME_COOKIE]) ){
	remove_action( 'admin_init', 'send_frame_options_header');
	remove_action( 'login_init', 'send_frame_options_header');
}

//added for jQuery compatibility
if(!function_exists('iwp_mmb_register_ext_scripts')){
	function iwp_mmb_register_ext_scripts(){
		wp_register_script( 'iwp-zero-clipboard', plugins_url( '/ZeroClipboard.js', __FILE__ ) );
	}
}

add_action( 'admin_init', 'iwp_mmb_register_ext_scripts' );


if(!function_exists('iwp_mmb_add_zero_clipboard_scripts')){
	function iwp_mmb_add_zero_clipboard_scripts(){	
		if (!wp_script_is( 'iwp-zero-clipboard', 'enqueued' )) {
			if(file_exists(WP_PLUGIN_DIR.'/iwp-client/ZeroClipboard.js') ){
				wp_enqueue_script(
					'iwp-zero-clipboard',
					plugins_url( '/ZeroClipboard.js', __FILE__ ),
					array( 'jquery' )
				);
			}
		}
	}
}

if (!function_exists('run_hash_change_process')) {
	function run_hash_change_process(){
		//code to check whether old hash files are already changed from wp_option table flag
		$is_replaced = get_option('iwp_client_replaced_old_hash_backup_files');
		if($is_replaced){
			return true;
		}
		
		global $wpdb;
		$table_name = $wpdb->base_prefix . "iwp_backup_status";
		$rows = $wpdb->get_results("SELECT historyID,taskResults FROM ".$table_name,  ARRAY_A);
		
		$hash_changed_files = array();
		$hash_changed_urls = array();
		foreach($rows as $k => $v){
			$this_his_id = $v['historyID'];
			$this_task_result = unserialize($v['taskResults']);
			if(!empty($this_task_result) && !empty($this_task_result['task_results']) && !empty($this_task_result['task_results'][$this_his_id]) && !empty($this_task_result['task_results'][$this_his_id]['server']) && !empty($this_task_result['task_results'][$this_his_id]['server']) && !empty($this_task_result['task_results'][$this_his_id]['server']['file_path']) && !empty($this_task_result['task_results'][$this_his_id]['server']['file_url'])){
				$new_task_result_server = modify_task_result_server($this_task_result['task_results'][$this_his_id]['server']);
				if(is_array($new_task_result_server) && array_key_exists("error")){
					continue;
				}
				$this_task_result['task_results'][$this_his_id]['server'] = $new_task_result_server;
			}
			if(!empty($this_task_result) && !empty($this_task_result['server']) && !empty($new_task_result_server['hash'])){
				$new_task_result_server = modify_task_result_server($this_task_result['server'], $new_task_result_server['hash']);
				if(is_array($new_task_result_server) && array_key_exists("error")){
					return $new_task_result_server;
					break;
				}
				$this_task_result['server'] = $new_task_result_server;
				
			}
			
			//updating table with new fileNames
			$new_task_result = serialize($this_task_result);
			$update = $wpdb->update($wpdb->base_prefix.'iwp_backup_status',array('taskResults' =>  $new_task_result ),array( 'historyID' => $this_his_id),array('%s'),array('%d'));
		}
		update_option('iwp_client_replaced_old_hash_backup_files', true);
		return true;
	}
}

if (!function_exists('modify_task_result_server')) {
	function modify_task_result_server($task_result_server, $useThisHash=''){
		if(!is_array($task_result_server['file_path'])){
			$current_file = $task_result_server['file_path'];
			$task_result_server['file_path'] = array();
			$task_result_server['file_path'][0] = $current_file;
		}
		if(!is_array($task_result_server['file_url'])){
			$current_url = $task_result_server['file_url'];
			$task_result_server['file_url'] = array();
			$task_result_server['file_url'][0] = $current_url;
		}

		$old_file_path = $task_result_server['file_path'];
		$old_file_url = $task_result_server['file_url'];
		
		$new_file_path = replace_old_hash_with_new_hash($old_file_path, $useThisHash);
		foreach($new_file_path['files'] as $ke => $va){
			
			//rename file
			$rename_result = rename_old_backup_file_name($va['old'], $va['new']);
			if(is_array($rename_result) && array_key_exists("error")){
				return $rename_result;
				break;
			}
			$task_result_server['file_path'][$ke] = $va['new'];
		}
		$task_result_server['hash'] = $new_file_path['hash'];
		
		$new_file_url = replace_old_hash_with_new_hash($old_file_url, $new_file_path['hash']);
		foreach($new_file_url['files'] as $ke => $va){
			$task_result_server['file_url'][$ke] = $va['new'];
		}
		
		//for single backup fix
		if(count($task_result_server['file_path']) === 1){
			$temp_val = $task_result_server['file_path'][0];
			unset($task_result_server['file_path']);
			$task_result_server['file_path'] = $temp_val;
		}
		if(count($task_result_server['file_url']) === 1){
			$temp_val = $task_result_server['file_url'][0];
			unset($task_result_server['file_url']);
			$task_result_server['file_url'] = $temp_val;
		}
		return $task_result_server;
	}
}

if (!function_exists('replace_old_hash_with_new_hash')) {
	
	function replace_old_hash_with_new_hash($backFileArr, $useThisHash='') {
		$newbackupfileArr = array();
		$newbackupfileArr['files'] = array();
		$newbackupfileArr['hash'] = '';
		
		if(empty($useThisHash)){
			$newBackupHash = md5(microtime(true).uniqid('',true).substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, rand(20,60)));
			$useThisHash = $newBackupHash;
		}
		else{
			$newBackupHash = $useThisHash;
		}
		foreach($backFileArr as $k => $backFile){
			$iwpPart = '.zip';
			$tempBackupFile = $backFile;
			
			$iwpPartIndex = strpos($backFile, '_iwp_part');
			if($iwpPartIndex !== false){
				$iwpPart = substr($backFile, $iwpPartIndex);
				$backFile = substr($backFile, 0, $iwpPartIndex);
			}
			
			$backFileInArray = explode("_", $backFile);
			$hashIndex = count($backFileInArray) - 1;
			
			$backupHashWithZip = $backFileInArray[$hashIndex];
			$backupHash = substr($backupHashWithZip, 0, 32);
			
			$newBackupHashWithZip = $newBackupHash . $iwpPart;
			$newBackupFile = substr($backFile, 0, strpos($backFile, $backupHashWithZip));
			$newBackupFile = $newBackupFile . $newBackupHashWithZip; 
			$newbackupfileArr['files'][$k]['new'] = $newBackupFile;
			$newbackupfileArr['files'][$k]['old'] = $tempBackupFile;
		}
		$newbackupfileArr['hash'] = $newBackupHash;
		return $newbackupfileArr;
	}
	
}


if (!function_exists('rename_old_backup_file_name')) {
	
	function rename_old_backup_file_name($oldName, $newName) {
		if (!@rename($oldName, $newName)) {
			return array('error' => 'Unable to rename old files', 'error_code' => 'unable_to_remane_old_backup_files');
		}
		return true;
	}
}

if(!function_exists('iwp_mmb_get_site_option')) {

	function iwp_mmb_get_site_option($option_name){
		if(is_multisite()){
			$blog_id = get_current_blog_id();
			$option_value = get_blog_option($blog_id,$option_name);
		}
		else {
			$option_value = get_site_option($option_name);
		}
		return $option_value;
	}
}

if ( !get_option('iwp_client_public_key')  && function_exists('add_action')){
	add_action('admin_enqueue_scripts', 'iwp_mmb_add_zero_clipboard_scripts');
}

if (!function_exists('iwp_mmb_json_encode')) {
	function iwp_mmb_json_encode($data, $options = 0, $depth = 512){
		if ( version_compare( PHP_VERSION, '5.5', '>=' ) ) {
			$args = array( $data, $options, $depth );
		} elseif ( version_compare( PHP_VERSION, '5.3', '>=' ) ) {
			$args = array( $data, $options );
		} else {
			$args = array( $data );
		}
		$json = @call_user_func_array( 'json_encode', $args );
		
		if ( false !== $json && ( version_compare( PHP_VERSION, '5.5', '>=' ) || false === strpos( $json, 'null' ) ) )  {
			return $json;
		}

		$args[0] = iwp_mmb_json_compatible_check( $data, $depth );
		return @call_user_func_array( 'json_encode', $args );
		}
}

if (!function_exists('json_encode'))
{
  function json_encode($a=false)
  {
    if (is_null($a)) return 'null';
    if ($a === false) return 'false';
    if ($a === true) return 'true';
    if (is_scalar($a))
    {
      if (is_float($a))
      {
        // Always use "." for floats.
        return floatval(str_replace(",", ".", strval($a)));
      }

      if (is_string($a))
      {
        static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
        return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
      }
      else
        return $a;
    }
    $isList = true;
    for ($i = 0, reset($a); $i < count($a); $i++, next($a))
    {
      if (key($a) !== $i)
      {
        $isList = false;
        break;
      }
    }
    $result = array();
    if ($isList)
    {
      foreach ($a as $v) $result[] = iwp_mmb_json_encode($v);
      return '[' . join(',', $result) . ']';
    }
    else
    {
      foreach ($a as $k => $v) $result[] = iwp_mmb_json_encode($k).':'.iwp_mmb_json_encode($v);
      return '{' . join(',', $result) . '}';
    }
  }
}
if (!function_exists('iwp_mmb_json_compatible_check')) {
	function iwp_mmb_json_compatible_check( $data, $depth ) {
		if ( $depth < 0 ) {
			return false;
		}

		if ( is_array( $data ) ) {
			$output = array();
			foreach ( $data as $key => $value ) {
				if ( is_string( $key ) ) {
					$id = iwp_mmb_json_convert_string( $key );
				} else {
					$id = $key;
				}
				if ( is_array( $value ) || is_object( $value ) ) {
					$output[ $id ] = iwp_mmb_json_compatible_check( $value, $depth - 1 );
				} elseif ( is_string( $value ) ) {
					$output[ $id ] = iwp_mmb_json_convert_string( $value );
				} else {
					$output[ $id ] = $value;
				}
			}
		} elseif ( is_object( $data ) ) {
			$output = new stdClass;
			foreach ( $data as $key => $value ) {
				if ( is_string( $key ) ) {
					$id = iwp_mmb_json_convert_string( $key );
				} else {
					$id = $key;
				}

				if ( is_array( $value ) || is_object( $value ) ) {
					$output->$id = iwp_mmb_json_compatible_check( $value, $depth - 1 );
				} elseif ( is_string( $value ) ) {
					$output->$id = iwp_mmb_json_convert_string( $value );
				} else {
					$output->$id = $value;
				}
			}
		} elseif ( is_string( $data ) ) {
			return iwp_mmb_json_convert_string( $data );
		} else {
			return $data;
		}

		return $output;
	}
}
if (!function_exists('iwp_mmb_json_convert_string')) {
	function iwp_mmb_json_convert_string( $string ) {
		if ( function_exists( 'mb_convert_encoding' ) ) {
			$encoding = mb_detect_encoding( $string, mb_detect_order(), true );
			if ( $encoding ) {
				return mb_convert_encoding( $string, 'UTF-8', $encoding );
			} else {
				return mb_convert_encoding( $string, 'UTF-8', 'UTF-8' );
			}
		} else {
			return check_invalid_UTF8( $string, $true);
		}
	}
}

if ( !function_exists('mb_detect_encoding') ) { 
	function mb_detect_encoding ($string, $enc=null, $ret=null) { 

		static $enclist = array( 
		'UTF-8',
		// 'ASCII', 
		// 'ISO-8859-1', 'ISO-8859-2', 'ISO-8859-3', 'ISO-8859-4', 'ISO-8859-5', 
		// 'ISO-8859-6', 'ISO-8859-7', 'ISO-8859-8', 'ISO-8859-9', 'ISO-8859-10', 
		// 'ISO-8859-13', 'ISO-8859-14', 'ISO-8859-15', 'ISO-8859-16', 
		// 'Windows-1251', 'Windows-1252', 'Windows-1254', 
		);

		$result = false; 

		foreach ($enclist as $item) { 
			$sample = $string;
			if(function_exists('iconv'))
				$sample = iconv($item, $item, $string); 
			if (md5($sample) == md5($string)) { 
				if ($ret === NULL) { $result = $item; } else { $result = true; } 
				break; 
			}
		}

		return $result; 
	}
}

if (!function_exists('check_invalid_UTF8')) {
	function check_invalid_UTF8( $string, $strip = false ) {
		$string = (string) $string;

		if ( 0 === strlen( $string ) ) {
			return '';
		}

		// Check for support for utf8 in the installed PCRE library once and store the result in a static
		static $utf8_pcre = null;
		if ( ! isset( $utf8_pcre ) ) {
			$utf8_pcre = @preg_match( '/^./u', 'a' );
		}
		// We can't demand utf8 in the PCRE installation, so just return the string in those cases
		if ( !$utf8_pcre ) {
			return $string;
		}

		// preg_match fails when it encounters invalid UTF8 in $string
		if ( 1 === @preg_match( '/^./us', $string ) ) {
			return $string;
		}

		// Attempt to strip the bad chars if requested (not recommended)
		if ( $strip && function_exists( 'iconv' ) ) {
			return iconv( 'utf-8', 'utf-8', $string );
		}

		return '';
	}
}

define('MAX_SERIALIZED_INPUT_LENGTH', 8192);
define('MAX_SERIALIZED_ARRAY_LENGTH', 512);
define('MAX_SERIALIZED_ARRAY_DEPTH', 20);
function _iwp_mmb_safe_unserialize($str)
{
	if(strlen($str) > MAX_SERIALIZED_INPUT_LENGTH)
	{
		// input exceeds MAX_SERIALIZED_INPUT_LENGTH
		return false;
	}
	if(empty($str) || !is_string($str))
	{
		return false;
	}
	$stack = array();
	$expected = array();
	/*
	 * states:
	 *   0 - initial state, expecting a single value or array
	 *   1 - terminal state
	 *   2 - in array, expecting end of array or a key
	 *   3 - in array, expecting value or another array
	 */
	$state = 0;
	while($state != 1)
	{
		$type = isset($str[0]) ? $str[0] : '';
		if($type == '}')
		{
			$str = substr($str, 1);
		}
		else if($type == 'N' && $str[1] == ';')
		{
			$value = null;
			$str = substr($str, 2);
		}
		else if($type == 'b' && preg_match('/^b:([01]);/', $str, $matches))
		{
			$value = $matches[1] == '1' ? true : false;
			$str = substr($str, 4);
		}
		else if($type == 'i' && preg_match('/^i:(-?[0-9]+);(.*)/s', $str, $matches))
		{
			$value = (int)$matches[1];
			$str = $matches[2];
		}
		else if($type == 'd' && preg_match('/^d:(-?[0-9]+\.?[0-9]*(E[+-][0-9]+)?);(.*)/s', $str, $matches))
		{
			$value = (float)$matches[1];
			$str = $matches[3];
		}
		else if($type == 's' && preg_match('/^s:([0-9]+):"(.*)/s', $str, $matches) && substr($matches[2], (int)$matches[1], 2) == '";')
		{
			$value = substr($matches[2], 0, (int)$matches[1]);
			$str = substr($matches[2], (int)$matches[1] + 2);
		}
		else if($type == 'a' && preg_match('/^a:([0-9]+):{(.*)/s', $str, $matches) && $matches[1] < MAX_SERIALIZED_ARRAY_LENGTH)
		{
			$expectedLength = (int)$matches[1];
			$str = $matches[2];
		}
		else
		{
			// object or unknown/malformed type
			return false;
		}
		switch($state)
		{
			case 3: // in array, expecting value or another array
				if($type == 'a')
				{
					if(count($stack) >= MAX_SERIALIZED_ARRAY_DEPTH)
					{
						// array nesting exceeds MAX_SERIALIZED_ARRAY_DEPTH
						return false;
					}
					$stack[] = &$list;
					$list[$key] = array();
					$list = &$list[$key];
					$expected[] = $expectedLength;
					$state = 2;
					break;
				}
				if($type != '}')
				{
					$list[$key] = $value;
					$state = 2;
					break;
				}
				// missing array value
				return false;
			case 2: // in array, expecting end of array or a key
				if($type == '}')
				{
					if(count($list) < end($expected))
					{
						// array size less than expected
						return false;
					}
					unset($list);
					$list = &$stack[count($stack)-1];
					array_pop($stack);
					// go to terminal state if we're at the end of the root array
					array_pop($expected);
					if(count($expected) == 0) {
						$state = 1;
					}
					break;
				}
				if($type == 'i' || $type == 's')
				{
					if(count($list) >= MAX_SERIALIZED_ARRAY_LENGTH)
					{
						// array size exceeds MAX_SERIALIZED_ARRAY_LENGTH
						return false;
					}
					if(count($list) >= end($expected))
					{
						// array size exceeds expected length
						return false;
					}
					$key = $value;
					$state = 3;
					break;
				}
				// illegal array index type
				return false;
			case 0: // expecting array or value
				if($type == 'a')
				{
					if(count($stack) >= MAX_SERIALIZED_ARRAY_DEPTH)
					{
						// array nesting exceeds MAX_SERIALIZED_ARRAY_DEPTH
						return false;
					}
					$data = array();
					$list = &$data;
					$expected[] = $expectedLength;
					$state = 2;
					break;
				}
				if($type != '}')
				{
					$data = $value;
					$state = 1;
					break;
				}
				// not in array
				return false;
		}
	}
	if(!empty($str))
	{
		// trailing data in input
		return false;
	}
	return $data;
}
/**
 * Wrapper for _safe_unserialize() that handles exceptions and multibyte encoding issue
 *
 * @param string $str
 * @return mixed
 */
function iwp_mmb_safe_unserialize( $str )
{
	// ensure we use the byte count for strings even when strlen() is overloaded by mb_strlen()
	if (function_exists('mb_internal_encoding') &&
		(((int) ini_get('mbstring.func_overload')) & 2))
	{
		$mbIntEnc = mb_internal_encoding();
		mb_internal_encoding('ASCII');
	}
	$out = _iwp_mmb_safe_unserialize($str);
	if (isset($mbIntEnc))
	{
		mb_internal_encoding($mbIntEnc);
	}
	return $out;
}

?>