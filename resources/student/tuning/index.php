<?php
	require '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'connection.php';
	require '..'.DIRECTORY_SEPARATOR.'utility.php';
	session_start();
	session_regenerate_id(true);
	if ( checkAuthToken() && checkMode( $con ) ) {
		mysqli_select_db( $con, 'admin' );
		$str = "select name, serial from messdetails where mess='".$_SESSION['database']."'";
		$query = mysqli_query( $con, $str );
		$row = mysqli_fetch_array( $query );
		$mess = $row[0];
		$serial = $row[1];
		$count = getNotifsCount($con);
		$count = $count>0?"(".$count.")":"";
		echo "
		<!DOCTYPE html>
		<html>
		<head>
			<title>Tuning | ".$_SESSION['name']."</title>
			<link rel='stylesheet' type='text/css' href='..".DIRECTORY_SEPARATOR."root.css' />
			<link rel='stylesheet' type='text/css' href='style.css' />
			<script type='text/javascript' src='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."jquery.js' defer></script>
			<script type='text/javascript' src='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."utility.js' defer></script>
			<script type='text/javascript' src='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."cookie.js' defer></script>
			<script type='text/javascript' src='..".DIRECTORY_SEPARATOR."utility.js' defer></script>
			<script type='text/javascript' src='script.js' defer></script>
		</head>
		<body>
			<div id='scroll'>&#10162</div>
			<div id='feedback'></div>
			<div id='disable'></div>
			<div id='load-window'><div id='floating-bar'></div></div>
			<div id='header'>
				<h1>IET Mess Online</h1><span id='tagline'><i>one step towards transparency...</i></span><br /><span>".$mess."</span>
				<a id='logout' href='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."logout.php'>Logout</a>
			</div>
			<nav>
				<a href='..".DIRECTORY_SEPARATOR."accounts'>My Account</a>
				<a href='..".DIRECTORY_SEPARATOR."expense' class='slide'>Expenses</a>
				<a href='..".DIRECTORY_SEPARATOR."servings' class='slide'>Servings</a>
				<a href='' class='slide'>Tuning</a>
				<a href='..".DIRECTORY_SEPARATOR."notifications' class='slide'>Notifications".$count."</a>
				<a href='..".DIRECTORY_SEPARATOR."balance' class='slide'>Balance</a>
			</nav>
			<div id='middle'>
				<div id='container'>
					<div id='tab_container'>
						<div class='tab selected' serial='1'>Password</div>
						<div class='tab border' serial='2'>Tuning</div>
					</div>
					<div id='content_container'>
						<div class='content' serial='1'>
							<div id='password_container'>
								<h2>Change Password</h2>
								<form id='change' action='process.php' method='POST' >
								<table cellpadding='10px'>
									<tr>
										<td><span class='right format'>Old password:</span></td>
										<td><input type='password' id='old_pass' maxlength='20' /></td>
									</tr>
									<tr>
										<td><span class='right format'>New Password:</span></td>
										<td><input type='password' id='new_pass' maxlength='20' /></td>
									</tr>
									<tr>
										<td><span class='right format'>Repeat Password:</span></td>
										<td><input type='password' id='repeat_pass' maxlength='20' /></td>
									</tr>
									<tr>
										<th colspan='2'><button class='button'>Change Password</button></th>
									</tr>
								</table>
								</form>
							</div>
						</div>
						<div class='content' serial='2'>Tuning</div>
					</div>
				</div>
			</div>
		";
	} else {
		header('Location: ..'.DIRECTORY_SEPARATOR);
	}
	mysqli_close( $con );
?>