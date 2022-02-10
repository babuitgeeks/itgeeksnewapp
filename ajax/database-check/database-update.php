<?php
require_once("../../inc/connect.php");
require_once("../../inc/functions.php");
if( isset( $_POST ) && !empty( $_POST ) ){
	$store_url = $_POST['store_url'];
	$old_products = $_POST['old_products'];
	$new_products = $_POST['new_products'];
	$old_collections = $_POST['old_collections'];
	$new_collections = $_POST['new_collections'];
	$sql = "UPDATE sunbasketitg_store_data SET old_products='$old_products', new_products='$new_products', old_collections='$old_collections', new_collections='$new_collections', status='Updated' WHERE store_url='$store_url'";
	$resultUpdate = mysqli_query( $conn, $sql );
}