<?php
	require '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'connection.php';
	session_start();
	session_regenerate_id(true);
	if ( ( $_SESSION['type'] == 'root' ) && !empty($_SESSION['id']) && !empty($_SESSION['type']) && !empty($_SESSION['name']) && !empty($_SESSION['database']) ) {
		echo "
		<!DOCTYPE html>
		<html>
		<head>
			<title>Add or remove admin</title>
			<meta http-equiv='cache-control' content='no-cache'>
			<link rel='stylesheet' type='text/css' href='root.css' />
			<link rel='stylesheet' type='text/css' href='admin.css' />
			<link rel='stylesheet' type='text/css' href='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR."accordion".DIRECTORY_SEPARATOR."styles.css' />
			<script type='text/javascript' src='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."jquery.js' defer></script>
			<script type='text/javascript' src='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."utility.js' defer></script>
			<script type='text/javascript' src='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."cookie.js' defer></script>
			<script type='text/javascript' src='admin.js' defer></script>
		</head>
		<body>
			<div id='dialog-overlay'>
				<div id='dialog-box'>
					<div id='dialog-head'></div>
					<div id='dialog-content'></div>
					<div id='dialog-foot'><input type='button' id='yes' value='Yes'/><input type='button' id='no' value='No'></div>
				</div>
			</div>
			<div id='disable'></div>
			<div id='feedback'></div>
			<div id='header'>
				<h1>IET Mess Online</h1><span><i>one step towards transparency...</i></span>
				<a id='logout' href='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."logout.php'>Logout</a>
			</div>
			<nav>
				<a href='mess.php'>Mess</a>
				<a href='admin.php' class='slide'>Admin</a>
				<a href='tuning.php' class='slide'>Tuning</a>
			</nav>
			<div id='middle'>
				<div id='messList' scroll>";
				mysqli_select_db($con, 'admin');
				$str = "select * from messdetails where mode='e'";
				$query = mysqli_query($con, $str) or die('could not connect');
				if ($query) {
					if ( mysqli_num_rows($query) > 0 ) {
						echo "<div id='cssmenu'><ul>";
							$first = true;
							while ($row = mysqli_fetch_array( $query ) ){
								echo "<li class='"; if ($first) { echo "active "; $first = false; } echo "has-sub'><a href='#'>".$row['name']."</a><ul><li>".$row['detail']."<a href='#' class='view' serial='".$row['serial']."'>View Administrators</a></li></ul></li>";
							}
						echo "</ul></div>";
					}
				}
				echo "
				</div>
				<div id='display'></div>
			</div>
		";
	} else {
		header('Location: ..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
	}
	mysqli_close( $con );
?>