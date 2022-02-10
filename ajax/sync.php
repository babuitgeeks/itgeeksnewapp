<?php

require_once("../inc/connect.php");

require_once("../inc/functions.php");

$shop_url = $_GET['url'];

$sql = "SELECT * FROM sunbasketitg_store WHERE store_url='". $shop_url ."' LIMIT 1";

$results = mysqli_query($conn, $sql);

$token = '';

if( mysqli_num_rows( $results ) < 1 ){}else{	

	$srow = mysqli_fetch_assoc($results);

	$token = $srow['access_token'];

}

$shop_urlRemove = str_replace(".myshopify.com", "_data",$shop_url);

$shop_TableName = str_replace("-", "_",$shop_urlRemove);

$result = $conn->query("SHOW TABLES LIKE '".$shop_TableName."'");

$limitUsed = 0;

if( $result->num_rows == 1 ) {

	$sqlNumRows = "SELECT COUNT(*) AS SUM FROM $shop_TableName";

	$sqlNumRows = mysqli_query($conn, $sqlNumRows);

	$limitUsed = mysqli_fetch_assoc( $sqlNumRows );

	$limitUsed = $limitUsed['SUM'];	

	

	if( $limitUsed < 1 ){}else{		

		while($row = mysqli_fetch_array($sqlNumRows)) {	

			if( !empty( $row['product_id'] ) ){

				$product_id = $row['product_id'];				

				$array = array(

					'ids' => $product_id

				);					

				$productData = shopify_call($token, $shop_url, "/admin/api/2021-04/products.json", $array , 'GET');

				$products = json_decode($productData['response'], JSON_PRETTY_PRINT);			

				if( !empty($products['products']) ){}else{

					$deletesql = "DELETE FROM $shop_TableName WHERE product_id='". $product_id ."'";

					$deletesqlRows = mysqli_query($conn, $deletesql);	

				}	

			}

		}		

		$sqlNumRows = "SELECT COUNT(*) AS SUM FROM $shop_TableName";

		$sqlNumRows = mysqli_query($conn, $sqlNumRows);

		$limitUsed = mysqli_fetch_assoc( $sqlNumRows );

		$limitUsed = $limitUsed['SUM'];	

	}

	echo json_encode( array( 'status' => 'true', 'limitUsed' => $limitUsed ) );					

}else{

	echo json_encode( array( 'status' => 'true', 'limitUsed' => $limitUsed ) );	

}

?>