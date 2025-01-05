<?php
header('Content-Type: application/json');
$path = $_SERVER['DOCUMENT_ROOT'].'wpplugins'; //C:\wamp\www\wpplugins\web-seal
//echo $path . '/wp-config.php';
include_once $path . '/wp-config.php';
//include_once $path . '/wp-load.php';
include_once $path . '/wp-includes/wp-db.php';
include_once $path . '/wp-includes/pluggable.php';
include_once $path . '/wp-includes/user.php';

/*if (function_exists('email_exists')) {
    echo "merun.<br />\n";
} else {
    echo "wala.<br />\n";
}*/
//$_REQUEST['txtEmailID'])
//$_POST['txtEmailID'] = 'jayar.asrciga.jr@gmail.com';
if( isset($_REQUEST['txtEmailID']) ) {
	if( email_exists($_REQUEST['txtEmailID'])){
	    //$response = json_encode('Already registered');

	    echo 'false';
	}
	else {
	    //$response = json_encode('true');
	    echo 'true';
	}

	//echo $response;
}
?>