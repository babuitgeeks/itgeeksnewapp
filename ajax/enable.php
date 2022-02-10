<?php

require_once("../inc/connect.php");

require_once("../inc/functions.php");

if( isset($_GET) && $_GET['value'] == 'true' ){	

	$sunbasketId = 1;
	
	$sql = "SELECT * FROM sunbasket_pin WHERE id='". $sunbasketId ."' LIMIT 1";

	$results = mysqli_query( $conn, $sql );

	$row = mysqli_fetch_assoc( $results );

	$grant_type = $row['grant_type'];

	$client_id = $row['client_id'];

	$client_secret = $row['client_secret'];
	
	$post = array(

		"grant_type" => $grant_type, 

		"client_id" => $client_id, 

		"client_secret" => $client_secret

	);
	
	$postText = http_build_query($post);
	
	$url = "https://sunbasket-partner-staging.auth.us-west-2.amazoncognito.com/oauth2/token";
	
	$ch = curl_init();

	$base64auth = base64_encode("$client_id:$client_secret");

	$headers = array(

	   "Content-Type: application/x-www-form-urlencoded",

	   "Authorization: Basic $base64auth",

	);

	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	curl_setopt($ch, CURLOPT_URL, $url);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	curl_setopt($ch, CURLOPT_POST, true);

	curl_setopt($ch, CURLOPT_POSTFIELDS, $postText); 

	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);



	$result = curl_exec($ch);

	curl_close($ch);

	$accessToken = json_decode($result);

	$accessTokenArray = json_decode(json_encode($accessToken), true);

	if( isset($accessTokenArray) && !empty($accessTokenArray['access_token']) ){

		$sunbasket_access = $accessTokenArray['access_token'];

		//Retrieve all the data values that we need

		$value = $_GET['value'];

		$store_url = $_GET['url'];

		$sql = "SELECT * FROM sunbasketitg_store WHERE store_url='". $store_url ."' LIMIT 1";

		$results = mysqli_query($conn, $sql);

		$row = mysqli_fetch_assoc($results);

		$sql = "UPDATE sunbasketitg_store SET sync='". $value ."', sunbasket_access='". $sunbasket_access ."' WHERE store_url='". $store_url ."'";

		if( mysqli_query( $conn, $sql ) ){

			echo json_encode( array( 'status' => 'true' ) );

		}else{

			echo json_encode( array( 'status' => 'false' ) );			

		}	

	}else{

		echo json_encode( array( 'status' => 'false' ) );	

	}	

}else{

	$value = $_GET['value'];

	$store_url = $_GET['url'];

	$sql = "SELECT * FROM sunbasketitg_store WHERE store_url='". $store_url ."' LIMIT 1";

	$results = mysqli_query($conn, $sql);

	$row = mysqli_fetch_assoc($results);

	$sql = "UPDATE sunbasketitg_store SET sync='". $value ."' WHERE store_url='". $store_url ."'";

	if( mysqli_query( $conn, $sql ) ){		

		echo json_encode( array( 'status' => 'true' ) );

	}else{

		echo json_encode( array( 'status' => 'false' ) );			

	}		

}

die();

//Then we return the values back to ajax

?>