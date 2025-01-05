<?php
//header('Access-Control-Allow-Origin: *');
//header('Access-Control-Allow-Origin: http://www.ultrahealth-europe.com');
//header("Access-Control-Allow-Origin: http://puremaca.net");
cors();
$table_prefix = "wp_";
$web_seal_table = $table_prefix.web_seals;

$seal_type = trim($_GET["seal_type"]);

//user_id_id e.g 10_6
$domain_params = explode('_', $_GET["master_id"]);
$user_id = (int) trim($domain_params[0]);
$domain_id = (int) trim($domain_params[1]);

$conn = mysqli_connect("localhost", "", "", ""); // live
//$conn = mysqli_connect("localhost", "root", "", ""); // local

// Check connection
if (mysqli_connect_errno()) {	
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

// Perform queries
//$result = mysqli_query($conn, "SELECT * FROM {$web_seal_table} WHERE user_id = {$user_id} AND id = {$domain_id} AND seal_status = 'active'");
$result = mysqli_query($conn, "SELECT * FROM {$web_seal_table} WHERE user_id = {$user_id} AND id = {$domain_id}");

if (mysqli_num_rows($result) > 0) {
	//Active
    $row = mysqli_fetch_assoc($result);

    //print_r($row["seal_domain"]);

    echo doctor_verified_web_seal($row["user_id"], $row["id"], $row["seal_domain"], $seal_type, $row["seal_status"], $row["seal_language"]);

} else {
	//Inactive, Disable
    echo "0 results";
}

mysqli_close($conn);


// Langauge en, fr,de, es, se, it, si
function doctor_verified_web_seal($user_id, $domain_id, $seal_domain = "", $seal_type = "", $seal_status = "", $seal_language = "en") {
	
	if ($seal_type === 'version5') {
		
		if ($seal_status === 'active') {

			//$output = '<div class="web_seal seal" seal_type="'.$seal_type.'">';
			$output .= '<!--link rel="stylesheet" type="text/css" href="https://www.doctor-certified.com/web_seal/13font.css"-->';
			$output .= '<a href="http://www.nutritionist-verified.com/web-seal/doctor-verified-certificate.php?master_id='.$user_id.'&amp;domain_id='.$domain_id.'" onclick="window.open(this.href, \'mywin\', \'left=0,top=0,width=465,height=1000,toolbar=1,resizable=0,scrollbars=yes\'); return false;">';
			$output .= '<div class="doc_certi_seal" style="margin:0px;position:relative;width:130px;">';
			$output .= '<img src="http://www.nutritionist-verified.com/web-seal/img/seals/2/doctor-verified-seal-active-2_'.$seal_language.'.png" width="130px" height="91px">';
			$output .= '<div class="doc_certi_domainName" style="position: absolute; top: 0; left: 0px; width: 130px; text-align: center; color: white; font-weight: normal; font-style: normal; font-variant: normal; font-stretch: normal; line-height: normal; border: none; letter-spacing: normal; text-indent: 0px; text-transform: none; visibility: visible; white-space: nowrap; font-family: &quot;arial narrow&quot;; font-size: 14px; background-color: transparent;"><span style="font-size: 12px;">';
			$output .= $seal_domain.'</span></div>';
			$output .= '<div class="doc_certi_date" style="position:absolute;bottom:1px;left:65px;color:white;font-weight:normal;font-style:normal;font-variant:normal;font-stretch:normal;background-color:transparent;line-height: normal;border:none;letter-spacing:normal;text-indent:0;text-transform:none;visibility:visible;font:11px/1.55em arial;font-size:11px;">';
			$output .= date('m-d-Y').'</div>';
			$output .= '</div>';
			$output .= '</a>';
			//$output .= '</div>';

		} elseif ($seal_status === 'inactive' || $seal_status === 'pending') {
			
			//$output = '<div class="web_seal seal" seal_type="'.$seal_type.'">';
			$output .= '<!--link rel="stylesheet" type="text/css" href="https://www.doctor-certified.com/web_seal/13font.css"-->';
			$output .= '<a href="http://www.nutritionist-verified.com/web-seal/doctor-verified-certificate.php?master_id='.$user_id.'&amp;domain_id='.$domain_id.'" onclick="window.open(this.href, \'mywin\', \'left=0,top=0,width=465,height=1000,toolbar=1,resizable=0,scrollbars=yes\'); return false;">';
			$output .= '<div class="doc_certi_seal" style="margin:0px;position:relative;width:130px;">';
			$output .= '<img src="http://www.nutritionist-verified.com/web-seal/img/seals/2/doctor-verified-seal-inactive-2_'.$seal_language.'.png" width="130px" height="91px">';
			$output .= '<div class="doc_certi_domainName" style="position: absolute; top: 0; left: 0px; width: 130px; text-align: center; color: white; font-weight: normal; font-style: normal; font-variant: normal; font-stretch: normal; line-height: normal; border: none; letter-spacing: normal; text-indent: 0px; text-transform: none; visibility: visible; white-space: nowrap; font-family: &quot;arial narrow&quot;; font-size: 14px; background-color: transparent;"><span style="font-size: 12px;">';
			$output .= $seal_domain.'</span></div>';
			$output .= '<div class="doc_certi_date" style="position:absolute;bottom:1px;left:65px;color:white;font-weight:normal;font-style:normal;font-variant:normal;font-stretch:normal;background-color:transparent;line-height: normal;border:none;letter-spacing:normal;text-indent:0;text-transform:none;visibility:visible;font:11px/1.55em arial;font-size:11px;">';
			$output .= date('m-d-Y').'</div>';
			$output .= '</div>';
			$output .= '</a>';
			//$output .= '</div>';

		}

	} elseif ($seal_type === 'version2') {
		
		if ($seal_status === 'active') {

			//$output .= '<div class="web_seal seal" seal_type="version2">';
			$output .= '<!--link rel="stylesheet" type="text/css" href="https://www.doctor-certified.com/web_seal/13font.css"-->';
			$output .= '<a href="http://www.nutritionist-verified.com/web-seal/doctor-verified-certificate.php?master_id='.$user_id.'&amp;domain_id='.$domain_id.'" onclick="window.open(this.href, \'mywin\', \'left=0,top=0,width=465,height=1000,toolbar=1,resizable=0,scrollbars=yes\'); return false;">';
			$output .= '<div class="doc_certi_seal" style="margin:0px;position:relative;width:129px;">';
			$output .= '<img src="http://www.nutritionist-verified.com/web-seal/img/seals/1/doctor-verified-seal-active-1_'.$seal_language.'.png" width="129px" height="191px">';
			$output .= '<div class="doc_certi_domainName" style="position: absolute; top: 95px; left: 0px; width: 130px; text-align: center; color: white; font-weight: normal; font-style: normal; font-variant: normal; font-stretch: normal; line-height: normal; border: none; letter-spacing: normal; text-indent: 0px; text-transform: none; visibility: visible; white-space: nowrap; font-family: &quot;arial narrow&quot;; font-size: 14px; background-color: transparent;"><span style="font-size: 12px;">';
			$output .= $seal_domain.'</span></div>';
			$output .= '<div class="doc_certi_date" style="position:absolute;bottom:1px;left:65px;color:white;font-weight:normal;font-style:normal;font-variant:normal;font-stretch:normal;background-color:transparent;line-height: normal;border:none;letter-spacing:normal;text-indent:0;text-transform:none;visibility:visible;font:11px/1.55em arial;font-size:11px;">';
			$output .= date('m-d-Y').'</div>';
			$output .= '</div>';
			$output .= '</a>';
			//$output .= '</div>';

		} elseif ($seal_status === 'inactive' || $seal_status === 'pending') {

			//$output .= '<div class="web_seal seal" seal_type="version2">';
			$output .= '<!--link rel="stylesheet" type="text/css" href="https://www.doctor-certified.com/web_seal/13font.css"-->';
			$output .= '<a href="http://www.nutritionist-verified.com/web-seal/doctor-verified-certificate.php?master_id='.$user_id.'&amp;domain_id='.$domain_id.'" onclick="window.open(this.href, \'mywin\', \'left=0,top=0,width=465,height=1000,toolbar=1,resizable=0,scrollbars=yes\'); return false;">';
			$output .= '<div class="doc_certi_seal" style="margin:0px;position:relative;width:129px;">';
			$output .= '<img src="http://www.nutritionist-verified.com/web-seal/img/seals/1/doctor-verified-seal-inactive-1_'.$seal_language.'.png" width="129px" height="191px">';
			$output .= '<div class="doc_certi_domainName" style="position: absolute; top: 95px; left: 0px; width: 130px; text-align: center; color: white; font-weight: normal; font-style: normal; font-variant: normal; font-stretch: normal; line-height: normal; border: none; letter-spacing: normal; text-indent: 0px; text-transform: none; visibility: visible; white-space: nowrap; font-family: &quot;arial narrow&quot;; font-size: 14px; background-color: transparent;"><span style="font-size: 12px;">';
			$output .= $seal_domain.'</span></div>';
			$output .= '<div class="doc_certi_date" style="position:absolute;bottom:1px;left:65px;color:white;font-weight:normal;font-style:normal;font-variant:normal;font-stretch:normal;background-color:transparent;line-height: normal;border:none;letter-spacing:normal;text-indent:0;text-transform:none;visibility:visible;font:11px/1.55em arial;font-size:11px;">';
			$output .= date('m-d-Y').'</div>';
			$output .= '</div>';
			$output .= '</a>';
			//$output .= '</div>';

		}			

	}

	return $output;

}



/**
 *  An example CORS-compliant method.  It will allow any GET, POST, or OPTIONS requests from any
 *  origin.
 *
 *  In a production environment, you probably want to be more restrictive, but this gives you
 *  the general idea of what is involved.  For the nitty-gritty low-down, read:
 *
 *  - https://developer.mozilla.org/en/HTTP_access_control
 *  - http://www.w3.org/TR/cors/
 *
 */
function cors() {

    // Allow from any origin
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
        // you want to allow, and if so:
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }

    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

        exit(0);
    }

    //echo "You have CORS!";
    //echo $_SERVER['HTTP_ORIGIN'];
}
?>