<?php
require_once("../inc/connect.php");
require_once("../inc/functions.php");

if( isset( $_POST ) && !empty( $_POST ) && !empty( $_POST['url'] ) ){

	$store_url = $_POST['url'];
	
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

	if( $sync == 'true' ){

		$sunbasket_menus = sunbasket_call( $sunbasket_access, 'menus' );

		//error_log( print_r( $sunbasket_menus , TRUE ) );

		if( $sunbasket_menus['status'] == '401' ){

			echo json_encode( array( 'status' => '401' ) );	

			die();

		}

		usleep(500000);

		$array = array(

			'limit'=>250,

			'fields'=>'id,title,handle'

		);		

		$getMenuCollection = shopify_call( $token, $store_url, "/admin/api/2021-10/smart_collections.json", $array , 'GET' );	

		$getMenuCollectionArray = json_decode($getMenuCollection['response'], JSON_PRETTY_PRINT);

		$allCollections = $getMenuCollectionArray['smart_collections'];

		$allCollectionsTitles = array();

		$allCollectionsIDs = array();

		foreach ( $allCollections as $key => $value ):

			if( !empty($value) ){

				$allCollectionsTitles[]=$value['title'];	

				$allCollectionsIDs[$key]=array();								

				$allCollectionsIDs[$key]['id']=$value['id'];								

				$allCollectionsIDs[$key]['title']=$value['title'];								

			}

		endforeach;

		$newCollectionsTitles = array();

		$newCollectionsTitlesCount = 0;

		$newCollectionsTitlesCheck = array();

		$newItemArray = array();

		foreach ( $sunbasket_menus as $key => $value ):

	//if ( $key == 0 ){

			$menuId = $value;

			$newItemArray[$key]['menu']=$menuId;

			$newItemArray[$key]['groups']=array(); 

			$keyIndex = $key;

			if( !empty( $menuId ) && $menuId != 'The incoming token has expired' ){

				if (in_array($menuId, $allCollectionsTitles)){}else{

					$array = array(

						'smart_collection' => array(

							'title' => $menuId,

							'handle' => $menuId,

							'rules' => array(array(

								  'column' => 'tag',

								  'relation' => 'equals',

								  'condition' => $menuId

							),)

						)	

					);	

					$createMenuCollection = shopify_call($token, $store_url, "/admin/api/2021-07/smart_collections.json", $array , 'POST');

					usleep(500000);

				}					

				$sunbasket_menu_data = sunbasket_call($sunbasket_access,'menu/'.$menuId);

				//error_log( print_r( $sunbasket_menu_data , TRUE ) );

				$sunbasket_items = $sunbasket_menu_data['items'];

				$count = 0;

				$arraygroup = 0;

				$menuIds = '';

				foreach ( $sunbasket_items as $key => $value ):

					if( isset( $value['type'] ) ){

						if (in_array($menuId.'-'.$value['type'], $newCollectionsTitlesCheck)){}else{

							$newCollectionsTitles[$newCollectionsTitlesCount]=array();

							$newCollectionsTitles[$newCollectionsTitlesCount]['name']=$value['type'];	

							$newCollectionsTitles[$newCollectionsTitlesCount]['id']=$menuId;

							$newCollectionsTitlesCheck[]=$menuId.'-'.$value['type'];

							$newCollectionsTitlesCount++;

						}

					}

					if( isset( $value['sub_type'] ) ){

						if (in_array($menuId.'-'.$value['sub_type'], $newCollectionsTitlesCheck)){}else{

							$newCollectionsTitles[$newCollectionsTitlesCount]=array();

							$newCollectionsTitles[$newCollectionsTitlesCount]['name']=$value['sub_type'];	

							$newCollectionsTitles[$newCollectionsTitlesCount]['id']=$menuId;	

							$newCollectionsTitlesCheck[]=$menuId.'-'.$value['sub_type'];

							$newCollectionsTitlesCount++;

						}

					}

					if( isset( $value['main_category'] ) ){

						if (in_array($menuId.'-'.$value['main_category'], $newCollectionsTitlesCheck)){}else{

							$newCollectionsTitles[$newCollectionsTitlesCount]=array();

							$newCollectionsTitles[$newCollectionsTitlesCount]['name']=$value['main_category'];

							$newCollectionsTitles[$newCollectionsTitlesCount]['id']=$menuId;

							$newCollectionsTitlesCheck[]=$menuId.'-'.$value['main_category'];

							$newCollectionsTitlesCount++;							

						}

					}

					if( isset( $value['sub_category'] ) ){

						if (in_array($menuId.'-'.$value['sub_category'], $newCollectionsTitlesCheck)){}else{

							$newCollectionsTitles[$newCollectionsTitlesCount]=array();

							$newCollectionsTitles[$newCollectionsTitlesCount]['name']=$value['sub_category'];

							$newCollectionsTitles[$newCollectionsTitlesCount]['id']=$menuId;

							$newCollectionsTitlesCheck[]=$menuId.'-'.$value['sub_category'];

							$newCollectionsTitlesCount++;

						}

					}

					if( isset( $value['protein_type'] ) ){

						if (in_array($menuId.'-'.$value['protein_type'], $newCollectionsTitlesCheck)){}else{

							$newCollectionsTitles[$newCollectionsTitlesCount]=array();

							$newCollectionsTitles[$newCollectionsTitlesCount]['name']=$value['protein_type'];	

							$newCollectionsTitles[$newCollectionsTitlesCount]['id']=$menuId;

							$newCollectionsTitlesCheck[]=$menuId.'-'.$value['protein_type'];

							$newCollectionsTitlesCount++;

						}

					}

					if( $count == 0 ){

						$menuIds .= $value['id'];							

					}else{						

						$menuIds .= ','.$value['id'];	

					}		

					$count++;

					if( $count == 19 ){

						$newItemArray[$keyIndex]['groups'][$arraygroup]=$menuIds;

						$count = 0;		

						$menuIds = '';

						$arraygroup++;

					}

				endforeach;

			}

//	}

		endforeach;		

		usleep(500000);

		$array = array();

		$getMenuProductsCount = shopify_call( $token, $store_url, "/admin/api/2021-10/products/count.json", $array , 'GET' );	

		$getMenuProductsCountArray = json_decode( $getMenuProductsCount['response'], JSON_PRETTY_PRINT );		

		$itgAllProductCount = $getMenuProductsCountArray['count'];

		$itgAllProductFrequency = 1;

		if( $itgAllProductCount > 200 ){			

			$itgAllProductFrequency	= $itgAllProductCount/200;

		}

		$totalPagination = ceil( $itgAllProductFrequency );	

		$page_info = '';

		$alreadyProductExist=array();

		$alreadyProductExistIDs=array();

		$alreadyProductExistIDCount=0;

		usleep(500000);

		for( $k = 0 ; $k < $totalPagination; $k++ ){	

			if( !empty( $page_info ) ){

				$array = array(

					'limit'=>200,

					'fields'=>'id,title,handle',

					'rel'=>'next',

					'page_info'=>$page_info

				);				

			}else{

				$array = array(

					'limit'=>200,

					'fields'=>'id,title,handle'

				);					

			}

			$getMenuProductsAll = shopify_call( $token, $store_url, "/admin/api/2021-10/products.json", $array , 'GET' );	

			$headers = $getMenuProductsAll['headers'];

			$nextPageURLLink = explode(",",$headers['Link']);

			if( isset( $nextPageURLLink[1] ) ){

				$nextPageURL = str_btwn($nextPageURLLink[1], '<', '>');				

			}else{

				$nextPageURL = str_btwn($nextPageURLLink[0], '<', '>');				

			}

			$nextPageURLparam = parse_url($nextPageURL); 

			parse_str($nextPageURLparam['query'], $value);

			$page_info = $value['page_info'];

			$getMenuProductsAllList = json_decode( $getMenuProductsAll['response'], JSON_PRETTY_PRINT );

			$getMenuProductsAllListArray = $getMenuProductsAllList['products'];

			if( !empty($getMenuProductsAllListArray) ){

				foreach ( $getMenuProductsAllListArray as $key => $value ):

					$alreadyProductExist[]=$value['handle'];

					$alreadyProductExistIDs[$alreadyProductExistIDCount]=array();

					$alreadyProductExistIDs[$alreadyProductExistIDCount]['id']=$value['id'];

					$alreadyProductExistIDs[$alreadyProductExistIDCount]['handle']=$value['handle'];

					$alreadyProductExistIDCount++;

				endforeach;

				usleep(500000);

			}

		}			

		$createdCollectionDetails = array();

		$createdCollectionCount = 0;

		foreach ( $newCollectionsTitles as $key => $value ):

			$collection_title = $value['id'].'-'.$value['name'];

			if ( in_array( $collection_title, $allCollectionsTitles ) ){

				$createdCollectionDetails[$createdCollectionCount]=array();

				$createdCollectionDetails[$createdCollectionCount]['title']=$collection_title;

				$createdCollectionCount++;

			}else{

				$array = array(

					'smart_collection' => array(

						'title' => $collection_title,

						'rules' => array(array(

							  'column' => 'tag',

							  'relation' => 'equals',

							  'condition' => $value['name']

						),array(

							  'column' => 'tag',

							  'relation' => 'equals',

							  'condition' => $value['id']

						))

					)	

				);	

				$createMenuCollection = shopify_call($token, $store_url, "/admin/api/2021-10/smart_collections.json", $array , 'POST');

				$createdCollectionDetails[$createdCollectionCount]=array();

				$createdCollectionDetails[$createdCollectionCount]['title']=$collection_title;

				$createdCollectionCount++;

				usleep(500000);

			}

		endforeach;	

		//error_log( print_r( $alreadyProductExist , TRUE ) );	

		$createdItemDetails = array();

		$createdItemCount = 0;

		foreach ( $newItemArray as $key => $value ):

			$menuId = $value['menu'];

			$productGroups = $value['groups'];

			foreach ( $productGroups as $key => $value ):

				//	if( $key == 1 ){

						$sunbasket_products_data = sunbasket_call( $sunbasket_access,'menu/'.$menuId.'/items?ids='.$value );

						foreach( $sunbasket_products_data as $key => $product ):

							$tagList = $menuId;

							$product_sunbasket_id = $product['id'];

							$product_title = $product['name'];							

							$product_handle = $product['slug'].'-'.$product_sunbasket_id;

							$product_handle = preg_replace('/-+/', '-', $product_handle);;

							if ( in_array( $product_handle, $alreadyProductExist ) ){	

								$createdItemDetails[$createdItemCount] = array();

								$createdItemDetails[$createdItemCount]['sunbasket'] = $product_sunbasket_id;

								$createdItemDetails[$createdItemCount]['menu'] = $menuId;

								$createdItemDetails[$createdItemCount]['shopify'] = '';

								$createdItemDetails[$createdItemCount]['title'] = $product_title;

								$createdItemDetails[$createdItemCount]['handle'] = $product_handle;

								$createdItemCount++;

							}else{

								//error_log( print_r( $product_handle , TRUE ) );

								if( isset( $product['type'] ) ){

									$tagList .= ','.$product['type'];					

								}

								if( isset( $product['sub_type'] ) ){

									$tagList .= ','.$product['sub_type'];					

								}

								if( isset( $product['main_category'] ) ){

									$tagList .= ','.$product['main_category'];					

								}

								if( isset( $product['sub_category'] ) ){

									$tagList .= ','.$product['sub_category'];					

								}

								if( isset( $product['protein_type'] ) ){

									$tagList .= ','.$product['protein_type'];					

								}

								if( isset( $product['tags'] ) ){

									$tagList .= ','.$product['tags'];					

								}

								$product_description = '<p>'.$product['description'].'</p><!-- split -->';

								$product_tags = $tagList;

								$product_type =$product['type'];

								$product_vendor =$product['brand_name'];

								$product_price =$product['list_price'];

								$product_quantity =$product['quantity'];

								$product_image_url =$product['image_url'];

								$product_image_url2 ='';

								if( isset( $product['second_image_url'] ) ){

									$product_image_url2 = $product['second_image_url'];							

								}

								$product_image_url3 ='';

								if( isset( $product['ingredients_image_url'] ) ){

									$product_image_url3 = $product['ingredients_image_url'];

								}

								$product_image_url4 ='';

								if( isset( $product['nutrition_image_url'] ) ){

									$product_image_url4 = $product['nutrition_image_url'];

								}						

								if( isset( $product['unit_size'] ) || isset( $product['unit_of_measure'] ) || isset( $product['calories_per_serving'] ) ){

									$product_description .= '<ul>';

										if( isset( $product['unit_size'] ) ){

											$product_description .= '<li><label>Unit :</label><span>'.$product['unit_size'].'</span></li>';

										}

										if( isset( $product['unit_of_measure'] ) ){

											$product_description .= '<li><label>Unit of measure :</label><span>'.$product['unit_of_measure'].'</span></li>';

										}

										if( isset( $product['calories_per_serving'] ) ){

											$product_description .= '<li><label>Calories per serving :</label><span>'.$product['calories_per_serving'].'</span></li>';

										}

									$product_description .= '</ul><!-- split -->';

								}

								if( isset( $product['allergens'] ) ){

									$allergensArray = $product['allergens'];

									$product_description .= '<label>Allergens</label>';

									$product_description .= '<ol>';

									foreach( $allergensArray as $key => $allergens ):

										$product_description .= '<li>'.$allergens['name'].'</li>';

									endforeach;

									$product_description .= '</ol><!-- split -->';

								}

								if( isset( $product['diet_types'] ) ){

									$diet_typesArray = $product['diet_types'];

									$product_description .= '<label>Diet types</label>';

									$product_description .= '<ol>';

									foreach( $diet_typesArray as $key => $diet_type ):

										$product_description .= '<li>'.$diet_type.'</li>';

									endforeach;

									$product_description .= '</ol><!-- split -->';

								}

								if( isset( $product['ingredients'] ) ){

									$ingredientsArray = $product['ingredients'];

									$product_description .= '<label>Ingredients</label>';

									$product_description .= '<ol>';

									foreach( $ingredientsArray as $key => $ingredient ):

										$product_description .= '<li>'.$ingredient.'</li>';

									endforeach;

									$product_description .= '</ol><!-- split -->';

								}

								if( isset( $product['instructions'] ) ){

									$instructionsArray = $product['instructions'];

									$product_description .= '<h3>Instructions</h3>';

									$product_description .= '<div>';

									foreach( $instructionsArray as $key => $instruction ):

										$product_description .= '<h4><label>Step '.$instruction['step'].' : </label><span>'.$instruction['title'].'</span></h4>';

										$product_description .= '<div>'.$instruction['details'].'</div>';

									endforeach;

									$product_description .= '</div><!-- split -->';

								}

								if( isset( $product['cook_times'] ) ){

									$cook_timesArray = $product['cook_times'];

									$product_description .= '<h3>Cook times</h3>';

									$product_description .= '<table>';

									$product_description .= '<tr><th>Method</th><th>Low</th><th>High</th></tr>';

									foreach( $cook_timesArray as $key => $cook_time ):

										$product_description .= '<tr><td>'.$cook_time['cooking_method'].'</td><td>'.$cook_time['low'].'</td><td>'.$cook_time['high'].'</td></tr>';

									endforeach;

									$product_description .= '</table><!-- split -->';

								}

								if( isset( $product['nutrition'] ) ){

									$nutritionArray = $product['nutrition'];

									$product_description .= '<h3>Nutritions</h3>';

									$product_description .= '<table>';

									$product_description .= '<tr><th>Name</th><th>Quantity</th><th>Unit</th></tr>';

									foreach( $nutritionArray as $key => $nutrition ):

										$product_description .= '<tr><td>'.$nutrition['name'].'</td><td>'.$nutrition['quantity'].'</td><td>'.$nutrition['unit_size'].''.$nutrition['unit_of_measure'].'</td></tr>';

									endforeach;

									$product_description .= '</table><!-- split -->';

								}	

								if( isset( $product['nutrition_disclaimer'] ) ){

									$product_description .= '<label>Nutrition disclaimer</label>';

									$product_description .= '<p>'.$product['nutrition_disclaimer'].'</p>';

								}	

								if( isset( $product['consumption_disclaimer'] ) ){

									$product_description .= '<label>Consumption disclaimer</label>';

									$product_description .= '<p>'.$product['consumption_disclaimer'].'</p>';

								}	

								if( isset( $product['tips'] ) ){

									$product_description .= '<label>Tips</label>';

									$product_description .= '<p>'.$product['tips'].'</p>';

								}	

								if( isset( $product['chefs_tip'] ) ){

									$product_description .= "<label>Chef's tip</label>";

									$product_description .= '<p>'.$product['chefs_tip'].'</p>';

								}

								if( isset( $product['make_it_leaner'] ) ){

									$product_description .= "<label>Make it leaner</label>";

									$product_description .= '<p>'.$product['make_it_leaner'].'</p>';

								}

								if( isset( $product['kids_can'] ) ){

									$product_description .= "<label>Kids can</label>";

									$product_description .= '<p>'.$product['kids_can'].'</p>';

								}

								if( isset( $product['grill_it'] ) ){

									$product_description .= "<label>Grill it</label>";

									$product_description .= '<p>'.$product['grill_it'].'</p>';

								}

								if( isset( $product['brand_description'] ) ){

									$product_description .= "<label>Brand description</label>";

									$product_description .= '<p>'.$product['brand_description'].'</p>';

								}

								$array = array(

									'product' => array(

										'title' => $product_title,

										'handle' => $product_handle,

										'body_html' => $product_description,

										'tags' => $tagList,

										'product_type' => $product_type,

										'vendor' => $product_vendor,

										'variants'=> array(array(

											  'price' => $product_price,

											  'inventory_policy' => 'continue',

										),),

										'images'=> array(

											array(

												  'src' => $product_image_url,

											)

										)

									)	

								);	

								$createMenuProducts = shopify_product_call($token, $store_url, "/admin/api/2021-10/products.json", $array , 'POST');	

								$createdItemDetails[$createdItemCount] = array();

								$createdItemDetails[$createdItemCount]['sunbasket'] = $product_sunbasket_id;

								$createdItemDetails[$createdItemCount]['menu'] = $menuId;

								$createdItemDetails[$createdItemCount]['shopify'] = '';

								$createdItemDetails[$createdItemCount]['title'] = $product_title;

								$createdItemDetails[$createdItemCount]['handle'] = $product_handle;

								$createdItemCount++;	

								usleep(500000);

							}	

						endforeach;	

				//	}						

			endforeach;

		endforeach;

		usleep(500000);

		

		if( !empty( $createdItemDetails ) && !empty( $createdCollectionDetails ) ){

			$createdItemDetailsJson = json_encode($createdItemDetails);

			$createdCollectionDetailsJson = json_encode($createdCollectionDetails);

			$alreadyProductExistIDsJson = json_encode($alreadyProductExistIDs);

			$allCollectionsIDsJson = json_encode($allCollectionsIDs);

			$itg_database_check = itg_call( $createdItemDetailsJson, $createdCollectionDetailsJson, $alreadyProductExistIDsJson, $allCollectionsIDsJson, $store_url, $token );

		}

		echo json_encode( array( 'status' => 'true' ) );	

	}else{

		echo json_encode( array( 'status' => 'false' ) );			

	}

}else{

	echo json_encode( array( 'status' => 'false' ) );	

}

die();