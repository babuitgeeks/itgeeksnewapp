<?php

// Get our helper functions

require_once("inc/functions.php");

require_once("inc/connect.php");

// Set variables for our request

$api_key = "a9537c21b1c64a1374919ebb0d800f43";

$shared_secret = "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";

$params = $_GET; // Retrieve all request parameters

$hmac = $_GET['hmac']; // Retrieve HMAC request parameter

$params = array_diff_key($params, array('hmac' => '')); // Remove hmac from params

ksort($params); // Sort params lexographically

$shop_url = $params['shop'];	

$computed_hmac = hash_hmac('sha256', http_build_query($params), $shared_secret);

// Use hmac data to check that the response is from Shopify or not

if (hash_equals($hmac, $computed_hmac)) {

	// Set variables for our request

	$query = array(

		"client_id" => $api_key, // Your API key

		"client_secret" => $shared_secret, // Your app credentials (secret key)

		"code" => $params['code'] // Grab the access key from the URL

	);

	// Generate access token URL

	$access_token_url = "https://" . $params['shop'] . "/admin/oauth/access_token";

	// Configure curl client and execute request

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	curl_setopt($ch, CURLOPT_URL, $access_token_url);

	curl_setopt($ch, CURLOPT_POST, count($query));

	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query));

	$result = curl_exec($ch);

	curl_close($ch);

	// Store the access token

	$result = json_decode($result, true);

	$access_token = $result['access_token'];

	// Show the access token (don't do this in production!)	

	$sql = "INSERT INTO sunbasketitg_store ( store_url, access_token, install_date ) VALUE('". $shop_url ."', '". $access_token ."', NOW() )";		

	if( mysqli_query( $conn, $sql ) ){	

		$array = array(

			'webhook' => array(

				'topic' => 'app/uninstalled', 

				'address' => 'https://itgeeksin.com/shopifyapps/public/sunbasket/webhooks/delete.php?shop=' . $shop_url,

				'format' => 'json'

			)

		);		

		$webhook = shopify_call($access_token, $shop_url, "/admin/api/2021-04/webhooks.json", $array, 'POST');

		$webhook = json_decode($webhook['response'], JSON_PRETTY_PRINT);

		//error_log( print_r( $webhook, true ) );

		$array = array(

			'webhook' => array(

				'topic' => 'orders/create', 

				'address' => 'https://itgeeksin.com/shopifyapps/public/sunbasket/webhooks/order.php?shop=' . $shop_url,

				'format' => 'json'

			)

		);		

		$webhook = shopify_call($access_token, $shop_url, "/admin/api/2021-04/webhooks.json", $array, 'POST');

		$webhook = json_decode($webhook['response'], JSON_PRETTY_PRINT);

	//	error_log('Response: '. $webhook);

	//	error_log( print_r( $webhook, true ) );

		/*

		$scriptArray = array(

			'script_tag' => array(

				'event' => 'onload',

				'src' => 'appUrl/script/itg.js'

			)

		);

		$scriptTag = shopify_call($access_token, $shop_url, "/admin/api/2021-04/script_tags.json", $scriptArray , 'POST');

		$scriptTag = json_decode($scriptTag['response'], JSON_PRETTY_PRINT);

		*/		

		header('location: https://'. $shop_url .'/admin/apps/sunbasket');

	}else{

		echo "Error installation";

	}

} else {

	// Someone is trying to be shady!

	die('This request is NOT from Shopify!');

}