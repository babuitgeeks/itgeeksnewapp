<?php

// Set variables for our request

$shop = $_GET['shop'];

$api_key = "a9537c21b1c64a1374919ebb0d800f43";

$scopes = "read_products,write_products,read_product_listings,read_orders,write_orders";

$redirect_uri = "https://itgeeksin.com/shopifyapps/public/sunbasket/token.php";

// Build install/approval URL to redirect to

$install_url = "https://" . $shop . "/admin/oauth/authorize?client_id=" . $api_key . "&scope=" . $scopes . "&redirect_uri=" . urlencode($redirect_uri);

// Redirect

echo '<script>top.window.location="'.$install_url.'"</script>';

die();