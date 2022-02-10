<?php
$webhook_payload = file_get_contents('php://input');
$webhook_payload = json_decode($webhook_payload, true);