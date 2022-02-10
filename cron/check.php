<?php

require_once("../inc/connect.php");

$sql = "SELECT * FROM sunbasketitg_store";

$results = mysqli_query( $conn, $sql );

$row = mysqli_fetch_all( $results, MYSQLI_ASSOC );

foreach ( $row as $key => $stores ):

	if( $stores['sync'] == true ){
			
		$data = array(

			'url' => $stores['store_url']

		);

		$payload = json_encode($data);

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL,"https://itgeeksin.com/shopifyapps/public/sunbasket/ajax/auto_check.php");

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		curl_setopt($ch, CURLOPT_POST, true);

		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

		$result = curl_exec($ch);

		curl_close($ch);
		
		usleep(500000);
		
	}
	
endforeach;