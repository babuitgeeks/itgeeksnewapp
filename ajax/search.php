<?php

require_once("../inc/connect.php");

require_once("../inc/functions.php");



//Retrieve all the data values that we need

$search_query = $_GET['query'];

$store_url = $_GET['url'];



$shop_urlRemove = str_replace(".myshopify.com", "_data",$store_url);

$shop_TableName = str_replace("-", "_",$shop_urlRemove);

$resultl = $conn->query("SHOW TABLES LIKE '".$shop_TableName."'");

$limitUsed = 0;

$productUsed = array();

if( $resultl->num_rows == 1 ) {	

	$sqlNumRows = "SELECT COUNT(*) AS SUM FROM $shop_TableName";

	$sqlNumRows = mysqli_query($conn, $sqlNumRows);

	$limitUsed = mysqli_fetch_assoc( $sqlNumRows );

	$limitUsed = $limitUsed['SUM'];	

}

$sql = "SELECT * FROM sunbasketitg_store WHERE store_url='". $store_url ."' LIMIT 1";

$results = mysqli_query($conn, $sql);

$row = mysqli_fetch_assoc($results);

$token = $row['access_token'];

//Create an array for the API

$array = array(

	'limit' => 20,

	'fields' => 'id,title,vendor,product_type,image',

	'title' => $search_query

);	

$pagination = 0;

$productData = shopify_call($token, $store_url, "/admin/api/2021-04/products.json", $array , 'GET');

//Get the headers

$headers = $productData['headers'];



//Create an array for link header

$link_array = array();



//Create variables for the new page infos

$prev_link = null;

$next_link = null;



//Check if there's more than one links / page infos. Otherwise, get the one and only link provided

if( !empty( $headers['link'] ) ){		

	if( strpos( $headers['link'], ',' )  !== false ) {

		$link_array = explode(',', $headers['link'] );

	} else {

		$link = $headers['link'];

	}

	//Check if the $link_array variable's size is more than one

	if( sizeof( $link_array ) > 1 ) {

		$prev_link = $link_array[0];

		$prev_link = str_btwn($prev_link, '<', '>');



		$param = parse_url($prev_link); 

		parse_str($param['query'], $prev_link); 



		$next_link = $link_array[1];

		$next_link = str_btwn($next_link, '<', '>');



		$param = parse_url($next_link); 

		parse_str($param['query'], $next_link); 

	} else {

		$prev_link = $link;

		$prev_link = str_btwn($prev_link, '<', '>');



		$param = parse_url($prev_link); 

		parse_str($param['query'], $prev_link); 



		$next_link = $link;

		$next_link = str_btwn($next_link, '<', '>');



		$param = parse_url($next_link); 

		parse_str($param['query'], $next_link); 

	}	

}

//Create and loop through the next or previous products

$html = '';



$products = json_decode($productData['response'], JSON_PRETTY_PRINT);

$html .= '<tr>';

	$html .= '<th></th>';

	$html .= '<th>Title</th>';

	$html .= '<th>Vendor</th>';

	$html .= '<th>Type</th>';

	$html .= '<th></th>';

$html .= '</tr>';	

foreach( $products as $product ){ 

	foreach( $product as $key => $value ){ 	

			

if( $resultl->num_rows == 1 ) {	

	$sqlCheck = "SELECT * FROM $shop_TableName WHERE product_id='". $value['id'] ."' LIMIT 1";

	$resultsCheck = mysqli_query( $conn, $sqlCheck );

	if( mysqli_num_rows( $resultsCheck ) < 1 ){

		$html .= '<tr>';		

	}else{

		$html .= '<tr class="ait-used">';

	}

}else{

		$html .= '<tr>';		

}

			$html .= '<td>';

				if( !empty($value['image']) ){

					$imageSrc = $value['image']['src'];

					if (strpos($imageSrc, '.png') !== false) {

						$imageSrc = str_replace(".png","_100x.png",$imageSrc);

					}else if (strpos($imageSrc, '.jpg') !== false) {

						$imageSrc = str_replace(".jpg","_100x.jpg",$imageSrc);

					}else if (strpos($imageSrc, '.gif') !== false) {

						$imageSrc = str_replace(".gif","_100x.gif",$imageSrc);

					}else{

						$imageSrc = "assets/img/placeholder-image.png";

					}

					$html .= '<img src="'.$imageSrc.'" alt="'.$value['title'].'" data-image="'. $value['image']['src'].'" width="80">';					

				}else{

					$html .= '<img src="assets/img/placeholder-image.png" alt="" width="80">';

				}

			$html .= '</td>';

			$html .= '<td><a href="#" data-store="'.$store_url.'" data-id="'.$value['id'].'" class="ait-edit-product">'.$value['title'].'</a></td>';

			$html .= '<td>'.$value['vendor'].'</td>';

			$html .= '<td>'.$value['product_type'].'</td>';

			$html .= '<td><a href="#" data-store="'.$store_url.'" data-id="'.$value['id'].'" class="ait-edit-product">Edit</a></td>';

		$html .= '</tr>';	

	}

}

//Then we return the values back to ajax

echo json_encode( array( 'prev' => $prev_link['page_info'], 'next' => $next_link['page_info'], 'html' => $html, 'pagination' => $pagination ) );		