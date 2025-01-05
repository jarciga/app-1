<?php
//$path = $_SERVER['DOCUMENT_ROOT'].'wpplugins'; //C:\wamp\www\wpplugins\web-seal // TEST

$path = $_SERVER['DOCUMENT_ROOT']; //C:\wamp\www\wpplugins\web-seal // LIVE


include_once $path . '/wp-config.php';

include_once $path . '/wp-load.php';
include_once $path . '/wp-blog-header.php';

include_once $path . '/wp-includes/wp-db.php';
include_once $path . '/wp-includes/pluggable.php';
include_once $path . '/wp-includes/user.php';

//https://developer.wordpress.org/reference/functions/get_the_author_meta/
include_once $path . '/wp-includes/author-template.php';
include_once $path . '/wp-includes/pluggable.php';
include_once $path . '/wp-includes/plugin.php';
include_once $path . '/wp-includes/author-template.php';

$table_prefix = "wp_";

$web_seal_table = $table_prefix.web_seals;



$seal_type = trim($_GET["seal_type"]);



$user_id = (int) $_GET["master_id"];

$domain_id = (int) $_GET["domain_id"];


$conn = mysqli_connect("localhost", "", "", ""); // live

//$conn = mysqli_connect("localhost", "", "", ""); // local

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



    //echo doctor_verified_web_seal($row["user_id"], $row["id"], $row["seal_domain"], $seal_type, $row["seal_status"]);



    $seal_domain = 'www.'.$row["seal_domain"];



    if($row["seal_status"] === 'active') {



    	$seal_status = '<span style="color:#0d923f;">active</span>';



    } elseif($row["seal_status"] === 'inactive') {



    	$seal_status = '<span style="color:#ff0000;">inactive</span>';



    }

    $since_date = date('d.m.Y'); 


    $verified_products = $row["verified_products"];

} else {

	//Inactive, Disable

    echo "0 results";

}

mysqli_close($conn); 


function get_additional_user_meta_thumb($field = '', $user_id = '') {
 
    $attachment_url = esc_url( get_the_author_meta( $field, $user_id ) );
 
     // grabs the id from the URL using Frankie Jarretts function
    $attachment_id = get_attachment_image_by_url( $attachment_url );
 
    // retrieve the thumbnail size of our image
    $image_thumb = wp_get_attachment_image_src( $attachment_id, 'thumbnail' );
 
    // return the image thumbnail
    return $image_thumb[0];
 
}

?>





<!DOCTYPE>

<html xmlns="http://www.w3.org/1999/xhtml">

<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<title>Doctor Verified - Certificate</title>



<style>

* {

	margin:0; 

	border:0; 

	padding:0;

}



body { font-size:13px; font-family: Helvetica, Arial, sans-serif; color:#013e74; }



#container { width:418px; margin:0 auto; background-color: #d9f2f5;background-image: url("img/doctor-verified-bg.png"); background-repeat:no-repeat; background-position: top left;   }



#header { padding-top:10px; padding-bottom:15px; }

#header .logo { width: 211px; float:left; margin-left:12px; }

#header .secure-certificate { width: 112px; float:right; margin-right:24px; }





#content { width:332px; margin:0 auto; }



#content .box-deco-top { height:22px;  background-image: url("img/box-deco-top.png"); }



#content .box-deco-bottom { height:23px;  background-image: url("img/box-deco-bottom.png"); }



#content .box-deco-right-left { padding:12px; background-image: url("img/box-deco-right-left.png"); }



#content .box-deco-top, #content .box-deco-bottom {

	background-repeat: no-repeat; 

	background-position: top left;	

}



.clearfix:after {

    content:"";

    display:block;

    clear:both;

}





</style>



</head>



<body>



<div id="container">

	<div id="header" class="clearfix">

		<div class="logo">

			<img src="img/doctor-verified-certificate-logo.png" width="211" height="52" />

		</div>

		<div class="secure-certificate">

			<img src="img/doctor-verified-secure-certificate.png" width="112" height="42" />

		</div>



	</div>



	<div id="content" style="position:relative;">



		<div class="box-deco-top">

			

		</div>

		<div class="box-deco-right-left">



			<div style="margin-bottom:20px; text-align:center;">

				<h1><?php echo $seal_domain; ?></h1>

				<p style="color:#545f69;"><strong>is Doctor Verified<sup>TM</sup></strong></p>

			</div>



			<div style="margin-bottom:40px;" class="clearfix">

				<!--div style="background-image: url(http://www.nutritionist-verified.com/wp-content/uploads/userphoto/<?php //echo get_the_author_meta( 'userphoto_image_file', $user_id ); ?>); width:92px; height:92px; float:left; border-radius: 50%;"-->
					<!--img src="http://www.nutritionist-verified.com/wp-content/uploads/userphoto/<?php //echo get_the_author_meta( 'userphoto_image_file', $user_id ); ?>" alt="Full size image"-->
				<!--/div-->

				<?php
				// retrieve our additional author meta info
				//global $post;

				//var_dump($post);

				//echo 'photo '.$post->post_author;

				//$user_meta_image = esc_attr( get_the_author_meta( 'user_meta_image', $post->post_author ) );
				//$doctor_photo = esc_attr( get_the_author_meta( 'doctor_photo', $user_id ) );

				$doctor_photo = get_additional_user_meta_thumb( 'doctor_photo', $user_id );
				 
				    // make sure the field is setcookie(name)
				    if ( isset( $doctor_photo ) && $doctor_photo ) {
				 
				        // only display if function exists
				        //if ( function_exists( 'get_additional_user_meta_thumb' ) ) ?>

							<div style="background-image: url(<?php echo $doctor_photo; ?>); width:102px; height:102px; float:left; border-radius: 50%;">
								<!--img alt="author photo two" src="<?php //echo get_additional_user_meta_thumb(); ?>" /-->
							</div>
	 
				<?php } ?>




				<div style="float:right; width:63%;">

 					<p><strong>Doctor:</strong> <?php echo get_the_author_meta( 'doctor_name', $user_id ); ?></p>

					<p><strong>Specialist:</strong> <?php echo get_the_author_meta( 'doctor_specialization', $user_id ); ?></p>

					<p><strong>Place:</strong> <?php echo get_the_author_meta( 'doctor_place', $user_id ); ?></p>

					<p style="margin-top:18px;"><strong><?php echo get_the_author_meta( 'doctor_email', $user_id ); ?></strong></p>

				</div>

			</div>

			<p style="width:265px; margin:0 auto 30px auto; border-bottom:2px #c2c2c2 solid; padding-bottom:30px; text-align:center;"><strong>This certificate is <?php echo $seal_status; ?> since <?php echo $since_date; ?></strong></p>

			<p style="width:265px; margin:0 auto 30px auto; border-bottom:2px #c2c2c2 solid; padding-bottom:30px; text-align:center;"><strong>Verified product/s:</strong> <?php echo $verified_products; ?></p>

			<p style="margin-bottom:65px;"><strong>Aliquam sed interdum velit. Cras nulla nulla, 

			vestibulum vitae vulputate et, <u>sollicitudin a 

			tortor.</u></strong></p> 

		</div>

		<div class="box-deco-bottom">

		</div>



		<div style="width:70px; height:91px; margin: -69px auto 0 auto;"><img src="img/red-ribbon.png" width="70" height="91"/></div>		



	</div>



	<div id="footer">



		<p style="color:#545f69; padding:17px 24px 24px 24px;">Nullam malesuada ex vulputate, lacinia libero nec, 

volutpat nisi. Aliquam pellentesque <a href="" style="color:#545f69;">dignissim.com</a> rutrum. 

Cras non iaculis leo. Sed in ante neque.</p>

		

		<div style="padding:18px 24px; border-radius: 8px 8px 0 0; background: #013e74; color:#fff;"><a href="" style="color:#fff;">Doctor Verified<sup>TM</sup></a> aliquet, nisi vitae commodo luctus, nulla 

ante sodales nisi.</div>

	</div>



</div><!--// #container -->



</body>

</html>




