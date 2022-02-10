<?php

$host = "localhost";

$username = "sunbasket";

$password = "ihzS193*";

$dbname = "admin_sunbasket";



$conn = mysqli_connect($host, $username, $password, $dbname);



if( !$conn ){

	die("Connection was not succesfull");

}