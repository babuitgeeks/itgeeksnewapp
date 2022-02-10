<?php

function shopify_call($token, $shop, $api_endpoint, $query = array(), $method = 'GET', $request_headers = array()) {

	// Build URL

	$url = "https://" . $shop . $api_endpoint;

	if (!is_null($query) && in_array($method, array('GET', 	'DELETE'))) $url = $url . "?" . http_build_query($query);

	// Configure cURL

	$curl = curl_init($url);

	curl_setopt($curl, CURLOPT_HEADER, TRUE);

	curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);

	curl_setopt($curl, CURLOPT_MAXREDIRS, 3);

	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);

	// curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 3);

	// curl_setopt($curl, CURLOPT_SSLVERSION, 3);

	curl_setopt($curl, CURLOPT_USERAGENT, 'Shopify App v.1');

	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);

	curl_setopt($curl, CURLOPT_TIMEOUT, 30);

	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

	// Setup headers

	$request_headers[] = "";

	if (!is_null($token)) $request_headers[] = "X-Shopify-Access-Token: " . $token;

	if ($method != 'GET' && in_array($method, array('POST', 'PUT'))) {

		if (is_array($query)){

			$query_PUT = json_encode($query);

			$request_headers[] = "Content-Type: application/json";

			$request_headers[] = "Content-Length: " . strlen($query_PUT);

		} 

	}

	curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);

	if ($method != 'GET' && in_array($method, array('POST', 'PUT'))) {

		if (is_array($query)) $query = json_encode($query);

		curl_setopt ($curl, CURLOPT_POSTFIELDS, $query);

	}    

	// Send request to Shopify and capture any errors

	$response = curl_exec($curl);

	$error_number = curl_errno($curl);

	$error_message = curl_error($curl);

	// Close cURL to be nice

	curl_close($curl);

	// Return an error is cURL has a problem

	if ( $error_number ) {

		return $error_message;

	} else {			

		// No error, return Shopify's response by parsing out the body and the headers

		$response = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);

		// Convert headers into an array

		$headers = array();

		$header_data = explode("\n",$response[0]);

		$headers['status'] = $header_data[0]; // Does not contain a key, have to explicitly set

		array_shift($header_data); // Remove status, we've already set it above

		foreach($header_data as $part) {

			$h = explode(":", $part, 2);

			$headers[trim($h[0])] = trim($h[1]);

		}

		// Return headers and Shopify's response

		return array('headers' => $headers, 'response' => $response[1]);

	}    

}

function shopify_product_call($token, $shop, $api_endpoint, $query = array(), $method = 'GET', $request_headers = array()) {

	// Build URL

	$url = "https://" . $shop . $api_endpoint;

	if (!is_null($query) && in_array($method, array('GET', 	'DELETE'))) $url = $url . "?" . http_build_query($query);

	// Configure cURL

	$curl = curl_init($url);

	curl_setopt($curl, CURLOPT_HEADER, TRUE);

	curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);

	curl_setopt($curl, CURLOPT_MAXREDIRS, 3);

	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);

	// curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 3);

	// curl_setopt($curl, CURLOPT_SSLVERSION, 3);

	curl_setopt($curl, CURLOPT_USERAGENT, 'Shopify App v.1');

	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);

	curl_setopt($curl, CURLOPT_TIMEOUT, 30);

	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

	// Setup headers

	$request_headers[] = "";

	if (!is_null($token)) $request_headers[] = "X-Shopify-Access-Token: " . $token;

	if ($method != 'GET' && in_array($method, array('POST', 'PUT'))) {

		if (is_array($query)){

			$query_PUT = json_encode($query);

			$request_headers[] = "Content-Type: application/json";

			$request_headers[] = "Content-Length: " . strlen($query_PUT);

		} 

	}

	curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);

	if ($method != 'GET' && in_array($method, array('POST', 'PUT'))) {

		if (is_array($query)) $query = json_encode($query);

		curl_setopt ($curl, CURLOPT_POSTFIELDS, $query);

	}    

	// Send request to Shopify and capture any errors

	$response = curl_exec($curl);

	$error_number = curl_errno($curl);

	$error_message = curl_error($curl);



	// Close cURL to be nice

	curl_close($curl);



	// Return an error is cURL has a problem

	if ( $error_number ) {

		return $error_message;

	} else {

			

		// No error, return Shopify's response by parsing out the body and the headers

		$response = preg_split("/\r\n\r\n|\n\n|\r\r/", $response);

		// Convert headers into an array

		$headers = array();

		$header_data = explode("\n",$response[0]);

		$headers['status'] = $header_data[0]; // Does not contain a key, have to explicitly set

		array_shift($header_data); // Remove status, we've already set it above

		foreach($header_data as $part) {

			$h = explode(":", $part, 2);

			$headers[trim($h[0])] = trim($h[1]);

		}



		// Return headers and Shopify's response

		return array('headers' => $headers, 'response' => $response[2]);



	}    

}

function sunbasket_call($token, $api_endpoint) {

	$url = "https://api.sunbasket-staging.com/partner/v1/$api_endpoint";

	$ch = curl_init();

	$accessToken = $token;

	$headers = array(

	   "Content-Type: application/json",

	   "Authorization: Bearer $accessToken",

	);

	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	curl_setopt($ch, CURLOPT_URL, $url);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	/*

	curl_setopt($ch, CURLOPT_POST, true);

	curl_setopt($ch, CURLOPT_POSTFIELDS, $postText); 

	*/

	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);



	$result = curl_exec($ch);

	curl_close($ch);

	$accessData = json_decode($result);

	$accessDataArray = json_decode(json_encode($accessData), true);

	return $accessDataArray;

}

function sunbasket_post_call( $token, $api_endpoint, $data ) {

	$url = "https://api.sunbasket-staging.com/partner/v1/$api_endpoint";

	$ch = curl_init();

	$accessToken = $token;

	$headers = array(

	   "Content-Type: application/json",

	   "Authorization: Bearer $accessToken",

	);

	$data = json_encode( $data );
	
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	curl_setopt($ch, CURLOPT_URL, $url);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	curl_setopt($ch, CURLOPT_POST, true);

	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	/*
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	*/

	$result = curl_exec($ch);

	curl_close($ch);

	$accessData = json_decode($result);

	$accessDataArray = json_decode(json_encode($accessData), true);

	return $accessDataArray;

}

function itg_call( $createdItemDetails, $createdCollectionDetails, $alreadyProductExistIDsJson, $allCollectionsIDsJson, $store_url, $token ) {

	$data = array(

		'store_url' => $store_url,

		'token' => $token,

		'createdItemDetails' => $createdItemDetails,

		'alreadyProductExistIDsJson' => $alreadyProductExistIDsJson,

		'createdCollectionDetails' => $createdCollectionDetails,

		'allCollectionsIDsJson' => $allCollectionsIDsJson

	);

	$payload = json_encode($data);

	// Prepare new cURL resource

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL,"https://itgeeksin.com/shopifyapps/public/sunbasket/ajax/database-check/database-check.php");

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	curl_setopt($ch, CURLOPT_POST, true);

	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

	// Set HTTP Header for POST request 

	// Submit the POST request

	$result = curl_exec($ch);

	curl_close($ch);

}

function itg_update_call( $old_products, $new_products, $old_collections, $new_collections, $store_url ) {

	$data = array(

		'old_products' => $old_products,

		'new_products' => $new_products,

		'old_collections' => $old_collections,

		'new_collections' => $new_collections,

		'store_url' => $store_url

	);

	$payload = json_encode($data);

	// Prepare new cURL resource

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL,"https://itgeeksin.com/shopifyapps/public/sunbasket/ajax/database-check/database-update.php");

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	curl_setopt($ch, CURLOPT_POST, true);

	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

	// Set HTTP Header for POST request 

	// Submit the POST request

	$result = curl_exec($ch);

	curl_close($ch);

}

function str_btwn($string, $start, $end){

    $string = ' ' . $string;

    $ini = strpos($string, $start);

    if ($ini == 0) return '';

    $ini += strlen($start);

    $len = strpos($string, $end, $ini) - $ini;

    return substr($string, $ini, $len);

}