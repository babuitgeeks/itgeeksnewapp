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

$verified = verify_webhook( $data, $hmac_header );

if( $verified == '1' ){	

	$store_url = $shop_header;
	
	$post = array(

		"grant_type" => "client_credentials", 

		"client_id" => "3cnj59cm0igbqf32ui2i4ucqmm", 

		"client_secret" => "1i9i11bs0eu92ehb0247d4t2jnitu4t3vjm5gc5tl0kipckf8qmf"

	);
	
	$postText = http_build_query($post);
	
	$url = "https://sunbasket-partner-staging.auth.us-west-2.amazoncognito.com/oauth2/token";
	
	$ch = curl_init();

	$base64auth = base64_encode("3cnj59cm0igbqf32ui2i4ucqmm:1i9i11bs0eu92ehb0247d4t2jnitu4t3vjm5gc5tl0kipckf8qmf");

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

		$value = 'true';

		$store_url = $store_url;

		$sql = "UPDATE sunbasketitg_store SET sync='". $value ."', sunbasket_access='". $sunbasket_access ."' WHERE store_url='". $store_url ."'";

		$sqls = mysqli_query( $conn, $sql );	

	}	

	$sql = "SELECT * FROM sunbasketitg_store WHERE store_url='". $store_url ."' LIMIT 1";

	$results = mysqli_query( $conn, $sql );

	$row = mysqli_fetch_assoc( $results );

	$sync = $row['sync'];

	$token = $row['access_token'];

	$sunbasket_access = $row['sunbasket_access'];	

	//error_log( print_r( $row , TRUE ) );		
	
	$lineItems = $decoded_data['line_items'];	
	
	$shopifyTransferOrder = array();
	
	$shopifyOrderId = $decoded_data['id'];	
	
	$shopifyTransferOrder['shopify_id'] = $shopifyOrderId;
	
	$shopifyTransferOrder['sunbasket'] = array();
	
	//file_put_contents( "line-item-33.txt", print_r( $decoded_data, true ) );
		
	$sql = "SELECT * FROM sunbasketitg_store_data WHERE store_url='$store_url' LIMIT 1";

	$results = mysqli_query( $conn, $sql );	
	
	$row = mysqli_fetch_assoc( $results );

	$all_products = unserialize( $row['new_products'] );	
	
	$orderId = $decoded_data['id'];
	
	$productsOrder = array();
	
	$count = 0;	
	$orderCount = 0;	
	
	foreach( $lineItems as $key => $lineItem ){
		
		$productId = $lineItem['product_id'];
		
		$quantity = $lineItem['quantity'];
		
		$array = array(
			'fields'=>'handle'
		);
		
		$getProduct = shopify_call( $token, $store_url, "/admin/api/2021-10/products/$productId.json", $array , 'GET' );
		
		$getProductArray = json_decode( $getProduct['response'], JSON_PRETTY_PRINT );
		
		$getProductHandle = $getProductArray['product']['handle'];
		
		foreach( $all_products as $key => $getTitle ):	

			if( $getTitle['handle'] == $getProductHandle ){
				
				$productsOrder[$count] = array();
				
				$productsOrder[$count]['menu_id'] = $getTitle['menu'];
				
				$productsOrder[$count]['reference_id'] = $orderId;
				
				$productsOrder[$count]['ship_email'] = $decoded_data['email'];
				
				if( !empty( $decoded_data['shipping_address'] ) ){
					
					$productsOrder[$count]['ship_name'] = $decoded_data['shipping_address']['name'];
					
					$productsOrder[$count]['ship_street1'] = $decoded_data['shipping_address']['address1'];
					
					$productsOrder[$count]['ship_city'] = $decoded_data['shipping_address']['city'];
					
					$productsOrder[$count]['ship_state'] = $decoded_data['shipping_address']['province_code'];
					
					$productsOrder[$count]['ship_zip'] = $decoded_data['shipping_address']['zip'];
					
					$productsOrder[$count]['ship_phone'] = $decoded_data['shipping_address']['phone'];
										
				}else{
					
					$productsOrder[$count]['ship_name'] = $decoded_data['billing_address']['name'];
					
					$productsOrder[$count]['ship_street1'] = $decoded_data['billing_address']['address1'];
					
					$productsOrder[$count]['ship_city'] = $decoded_data['billing_address']['city'];
					
					$productsOrder[$count]['ship_state'] = $decoded_data['billing_address']['province_code'];
					
					$productsOrder[$count]['ship_zip'] = $decoded_data['billing_address']['zip'];
					
					$productsOrder[$count]['ship_phone'] = $decoded_data['billing_address']['phone'];
					
				}	
				
				$productsOrder[$count]['item_id'] = $getTitle['sunbasket'];
				
				$productsOrder[$count]['item_quantity'] = $quantity;
				
				$count++;				
				
			}
		endforeach;			
		
	}
	$menuArray = array();
	foreach( $productsOrder as $key => $orderItem ):
		if ( in_array( $orderItem['menu_id'], $menuArray ) ){ }else{
			$menuArray[] = $orderItem['menu_id'];
		}
	endforeach;
	$sunbasketOrder = array();
	$count=0;
	if( !empty( $menuArray ) ){
		foreach( $menuArray as $key => $menuId ):			
			$sunbasketOrder[$count]=array();			
			$sunbasketOrder[$count]['menu_id']=$menuId;
			$keyCount=$key;
			$sunbasketOrder[$count]['items']=array();
			foreach( $productsOrder as $key => $orderItem ):
				if( $orderItem['menu_id'] == $menuId ){	
					$sunbasketOrder[$count]['reference_id']=$orderItem['reference_id'].''.$keyCount.''.uniqid();
					$sunbasketOrder[$count]['ship_name']=$orderItem['ship_name'];
					$sunbasketOrder[$count]['ship_street1']=$orderItem['ship_street1'];
					$sunbasketOrder[$count]['ship_city']=$orderItem['ship_city'];
					$sunbasketOrder[$count]['ship_state']=$orderItem['ship_state'];
					$sunbasketOrder[$count]['ship_zip']=$orderItem['ship_zip'];
					$sunbasketOrder[$count]['ship_email']=$orderItem['ship_email'];
					$sunbasketOrder[$count]['ship_phone']=$orderItem['ship_phone'];
					$sunbasketOrder[$count]['items'][$key]=array();
					$sunbasketOrder[$count]['items'][$key]['item_id']=$orderItem['item_id'];
					$sunbasketOrder[$count]['items'][$key]['item_quantity']=$orderItem['item_quantity'];
				}
			endforeach;
			$count++;
		endforeach;
	}
	//$sunbasket_menus = sunbasket_post_call( $sunbasket_access, 'order', $data );	
	
	foreach( $sunbasketOrder as $key => $Order ):
		$items = array();
		$OrderItems = $Order['items'];
		$count = 0;
		foreach( $OrderItems as $key => $OrderItem ):
			$items[$count]=array();
			$items[$count]['id']=$OrderItem['item_id'];
			$items[$count]['quantity']=$OrderItem['item_quantity'];
			$count++;
		endforeach;
		$array = array(
			'menu_id'=>$Order['menu_id'],
			'reference_id'=>$Order['reference_id'],
			'ship_to'=> array(
				'name'=>$Order['ship_name'],
				'street1'=>$Order['ship_street1'],
				'city'=>$Order['ship_city'],
				'state'=>$Order['ship_state'],
				'zip'=>$Order['ship_zip'],
				'email'=>$Order['ship_email'],
				'phone'=>$Order['ship_phone'],
			),
			'items'=>$items
		);		
		$sunbasket_order = sunbasket_post_call( $sunbasket_access, 'order', $array );
		$shopifyTransferOrder['sunbasket'][$orderCount]=$sunbasket_order;
		$orderCount++;	
	endforeach;
	
	$sql = "SELECT * FROM sunbasketitg_store_order WHERE store_url='$store_url' LIMIT 1";

	$results = mysqli_query( $conn, $sql );	
	
	if( mysqli_num_rows( $results ) < 1 ){		
		
		$sunbasketOrder = array();
		
		array_push( $sunbasketOrder, $shopifyTransferOrder );

		$new_order = addslashes( serialize( $sunbasketOrder ) );

		$sql = "INSERT INTO sunbasketitg_store_order (

		store_url,

		order_data,

		status ) VALUE( '". $store_url ."',

		'". $new_order ."',

		'Loaded' )";

		$resultInsert = mysqli_query( $conn, $sql );	
		
	}else{
		
		$row = mysqli_fetch_assoc($results);
		
		$order_data = unserialize( $row['order_data'] );
		
		file_put_contents( "lineitemx.txt", print_r( $order_data, true ) );
		
		array_push( $order_data, $shopifyTransferOrder );

		$sql = "UPDATE sunbasketitg_store_order SET order_data='$new_order', status='Updated' WHERE store_url='$store_url'";

		$resultUpdate = mysqli_query( $conn, $sql );
		
	}
	
	//file_put_contents( "lineitem.txt", print_r( $shopifyTransferOrder, true ) );	
}

?>