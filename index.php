<?php

// Get our helper functions

require_once("inc/functions.php");

require_once("inc/connect.php");

$installation = 0;

$store_url = '';

if( isset( $_GET ) ){	

	$requests = $_GET;

	if( !empty( $requests['shop'] ) ){

		$store_url = $requests['shop'];	

		$installation = 1;		

	}

}

if( $installation == 1 ){}else{

	if( isset( $_POST ) && !empty( $_POST['storeurl'] ) && !empty( $_POST['password'] ) ){

		$store_url = $_POST['storeurl'];

		$password = $_POST['password'];

		$sql = "SELECT * FROM sunbasket_cred WHERE id='1' LIMIT 1";

		$results = mysqli_query($conn, $sql);

		if( mysqli_num_rows( $results ) < 1 ){}else{

			$row = mysqli_fetch_assoc($results);

			$password = $row['password'];

			if( $password == $_POST['password'] ){

				$installation = 1;			

			}else{

				?>

				<html>

					<head>

						<meta charset="utf-8">

						<meta http-equiv="X-UA-Compatible" content="IE=edge">

						<!--[if IE]>

						<meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'>

						<![endif]-->

						<meta name="keywords" content=""/>

						<meta name="description" content=""/>

						<meta name="author" content=""/>

						<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

						<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

						<title>Sunbasket</title>	

						<link rel="stylesheet" href="assets/css/style.css">

					</head>

					<body>				

						<div class="itg-login-detail">

							<div class="container">

								<div class="itg-login-form">

									<h3 class="title">Install Sunbasket App</h3>

									<form action="" method="post">

										<div class="itg-login-group">

											<input type="text" placeholder="Shopify Store Url..." value="<?php echo $store_url; ?>" name="storeurl">

										</div>

										<div class="itg-login-group">

											<input type="password" placeholder="Password" name="password">

											<span class="login-error">Password is wrong</span>

										</div>

										<div class="itg-login-submit">

											<button type="submit" class="button btn">Install Now</button>

										</div>

									</form>

								</div>

							</div>

						</div>

					</body>

				</html>

			<?php			

			}

		}	

	}else{

		?>

		<html>

			<head>

				<meta charset="utf-8">

				<meta http-equiv="X-UA-Compatible" content="IE=edge">

				<!--[if IE]>

				<meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'>

				<![endif]-->

				<meta name="keywords" content=""/>

				<meta name="description" content=""/>

				<meta name="author" content=""/>

				<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

				<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

				<title>Sunbasket</title>	

				<link rel="stylesheet" href="assets/css/style.css">

			</head>

			<body>				

				<div class="itg-login-detail">

					<div class="container">

						<div class="itg-login-form">

							<h3 class="title">Install Sunbasket App</h3>

							<form action="" method="post">

								<div class="itg-login-group">

									<input type="text" placeholder="Shopify Store Url..." name="storeurl">

								</div>

								<div class="itg-login-group">

									<input type="password" placeholder="Password" name="password">

								</div>

								<div class="itg-login-submit">

									<button type="submit" class="button btn">Install Now</button>

								</div>

							</form>

						</div>

					</div>

				</div>

			</body>

		</html>

	<?php

	}	

}

if( $installation == 1 && $store_url != '' ){

	$sql = "SELECT * FROM sunbasketitg_store WHERE store_url='". $store_url ."' LIMIT 1";

	$results = mysqli_query($conn, $sql);

	if( mysqli_num_rows( $results ) < 1 ){

		header('location: install.php?shop='. $store_url);

		exit();

	}else{	

		$row = mysqli_fetch_assoc($results);

		$enableSync = $row['sync'];			

		?>

			<html>

				<head>

					<meta charset="utf-8">

					<meta http-equiv="X-UA-Compatible" content="IE=edge">

					<!--[if IE]>

					<meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'>

					<![endif]-->

					<meta name="keywords" content=""/>

					<meta name="description" content=""/>

					<meta name="author" content=""/>

					<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

					<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

					<title>Sunbasket</title>	

					<link rel="stylesheet" href="assets/css/style.css">

				</head>

				<body class="itg-app-body">				

					<div class="itg-enable-sync">

						<div class="itg-admin-container">

							<div class="itg-admin-header">

								<h3 class="title">Enable/Disable Sync</h3>

								<div class="enable-disable-button">									

									<input type="checkbox" data-store="<?php echo $store_url; ?>" id="itg-enable-disable" name="enable-disable" <?php if ( $enableSync == 'true' ){echo 'checked';} ?>>

									<label for="itg-enable-disable" ></label>

								</div>

							</div>

						</div>

					</div>

					<button type="button" id="check-products" data-enable="true" class="btn button" data-store="<?php echo $store_url; ?>">Check Products</button>

					<div class="itg-loader">

						<img src="assets/img/itg-loader.svg" alt="">

					</div>

					<script src="assets/js/jquery.min.js"></script>

					<script src="assets/js/admin.js"></script>	

				</body>

			</html>

		<?php

	}

}

exit();