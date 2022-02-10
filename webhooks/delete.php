<?php
require_once("../inc/functions.php");
require_once("../inc/connect.php");

define('SHOPIFY_APP_SECRET', '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'); // Replace with your SECRET KEY

function verify_webhook($data, $hmac_header){
  $calculated_hmac = base64_encode(hash_hmac('sha256', $data, SHOPIFY_APP_SECRET, true));
  return hash_equals($hmac_header, $calculated_hmac);
}

$res = '';
$hmac_header = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'];
$topic_header = $_SERVER['HTTP_X_SHOPIFY_TOPIC'];
$shop_header = $_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'];
$data = file_get_contents('php://input');
$decoded_data = json_decode($data, true);

//$verified = verify_webhook($data, $hmac_header);


if( $topic_header == 'app/uninstalled' || $topic_header == 'shop/update') {
	if( $topic_header == 'app/uninstalled' ) {		
		$sql = "DELETE FROM sunbasketitg_store WHERE store_url='".$shop_header."' LIMIT 1";
		$result = mysqli_query($conn, $sql);
		$res = 'is successfully deleted from the database';
	} else {
		$res = $data;
	}
}

//error_log('Response: '. $res); //check error.log to see the result
?>