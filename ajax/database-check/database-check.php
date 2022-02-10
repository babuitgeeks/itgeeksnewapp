<?php
require_once("../../inc/connect.php");
require_once("../../inc/functions.php");
if( isset( $_POST ) && !empty( $_POST ) ){
	$store_url = $_POST['store_url'];
	//error_log( print_r( $store_url , TRUE ) );
	$token = $_POST['token'];
	if( !empty( $store_url ) ){
		$createdItemDetailsJson = $_POST['createdItemDetails'];	
		$createdCollectionDetailsJson = $_POST['createdCollectionDetails'];	
		$alreadyProductExistIDsJson = $_POST['alreadyProductExistIDsJson'];	
		$allCollectionsIDsJson = $_POST['allCollectionsIDsJson'];	
		if( !empty( $createdItemDetailsJson ) || !empty( $createdCollectionDetailsJson ) ){
			$createdItemDetails = json_decode( $createdItemDetailsJson, true );
			$createdCollectionDetails = json_decode( $createdCollectionDetailsJson, true );
			$alreadyProductExistIDs = json_decode( $alreadyProductExistIDsJson, true );
			$allCollectionsIDs = json_decode( $allCollectionsIDsJson, true );
			$sql = "SELECT * FROM sunbasketitg_store_data WHERE store_url='$store_url' LIMIT 1";
			$results = mysqli_query( $conn, $sql );				
			if( mysqli_num_rows( $results ) < 1 ){			
				$new_products = addslashes(serialize( $createdItemDetails ));
				$new_collections = addslashes(serialize( $createdCollectionDetails ));
				$sql = "INSERT INTO sunbasketitg_store_data (
				store_url,
				old_products,
				new_products,
				old_collections,
				new_collections,
				status ) VALUE('". $store_url ."',
				'". $new_products ."',
				'". $new_products ."',
				'". $new_collections ."',
				'". $new_collections ."',
				'Loaded' )";
				$resultInsert = mysqli_query( $conn, $sql );
				//error_log( print_r( $resultInsert , TRUE ) );
			}else{
				$row = mysqli_fetch_assoc($results);
				$old_collections = unserialize( $row['new_collections'] );
				$old_products = unserialize( $row['new_products'] );
			/* 	error_log( print_r( $token , TRUE ) );
				error_log( print_r( $store_url , TRUE ) );
				error_log( print_r( $old_collections , TRUE ) );
				error_log( print_r( $old_products , TRUE ) );
				die(); */
				$newCollection = array();
				foreach( $createdCollectionDetails as $key => $newTitle ):
					$newCollection[] = $newTitle['title'];
				endforeach;	
				$deleteCollection = array();
				foreach( $old_collections as $key => $deleteTitle ):	
					if ( in_array( $deleteTitle['title'], $newCollection ) ){}else{
						$deleteCollection[] = $deleteTitle['title'];
					}
				endforeach;
				foreach( $allCollectionsIDs as $key => $delete ):
					if ( in_array( $delete['title'], $deleteCollection ) ){
						$deleteId = $delete['id'];
						$array = array();
						$deleteMenuCollection = shopify_call( $token, $store_url, "/admin/api/2021-10/smart_collections/$deleteId.json", $array , 'DELETE' );
					}
				endforeach;	
				usleep(500000);
				$newProducts = array();
				foreach( $createdItemDetails as $key => $newTitle ):
					$newProducts[] = $newTitle['handle'];
				endforeach;				
				$deleteproducts = array();
				foreach( $old_products as $key => $deleteTitle ):	
					if ( in_array( $deleteTitle['handle'], $newProducts ) ){}else{
						$deleteproducts[] = $deleteTitle['handle'];
					}
				endforeach;	
				
				foreach( $alreadyProductExistIDs as $key => $delete ):
					if ( in_array( $delete['handle'], $deleteproducts ) ){
						$deleteId = $delete['id'];
						$array = array();
						$deleteMenuProducts = shopify_call( $token, $store_url, "/admin/api/2021-10/products/$deleteId.json", $array , 'DELETE' );
					}
				endforeach;				
				$old_products = addslashes( serialize( $old_products ) );
				$new_products = addslashes( serialize( $createdItemDetails ) );
				$old_collections = addslashes( serialize( $old_collections ) );
				$new_collections = addslashes( serialize( $createdCollectionDetails ) );
				itg_update_call( $old_products, $new_products, $old_collections, $new_collections, $store_url );
				/*
				$sql = "UPDATE sunbasketitg_store_data SET old_products='$old_products', new_products='$new_products', old_collections='$old_collections', new_collections='$new_collections', status='Updated' WHERE store_url='$store_url'";
				$resultUpdate = mysqli_query( $conn, $sql );
				error_log( print_r( mysqli_error($conn) , TRUE ) );
				error_log( print_r( $resultUpdate , TRUE ) );
				*/
			}
		}	
	}	
}