<?php
$webhook_payload = file_get_contents('php://input');
$webhook_payload = json_decode($webhook_payload, true);

$shop_domain = $webhook_payload['shop_domain'];

require_once("../inc/connect.php");
	

die();