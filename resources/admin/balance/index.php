<?php
	require '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'connection.php';
	require '..'.DIRECTORY_SEPARATOR.'utility.php';
	session_start();
	session_regenerate_id(true);
	if ( checkAuthToken() && checkMode( $con ) ) {
		mysqli_select_db( $con, 'admin' );
		$str = "select name from messdetails where mess='".$_SESSION['database']."'";
		$query = mysqli_query( $con, $str );
		$row = mysqli_fetch_array( $query );
		$mess = $row[0];
		$str = "select * from messdetails where mess='".$_SESSION['database']."'";
		$query = mysqli_query( $con, $str );
		$row = mysqli_fetch_array( $query );
		$total = $row['total'];
		$expense = $row['expense'];
		$status = $row['status'];
		echo "
		<!DOCTYPE html>
		<html>
		<head>
			<title>Balance | ".$mess."</title>
			<link rel='stylesheet' type='text/css' href='style.css' />
			<link rel='stylesheet' type='text/css' href='..".DIRECTORY_SEPARATOR."root.css' />
			<script type='text/javascript' src='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."jquery.js' defer></script>
			<script type='text/javascript' src='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."cookie.js' defer></script>
			<script type='text/javascript' src='..".DIRECTORY_SEPARATOR."utility.js' defer></script>
			<script type='text/javascript' src='script.js' defer></script>
		</head>
		<body>
		<div id='load-window'><div id='floating-bar'></div></div>
		<div id='dialog-overlay'>
			<div id='dialog-box'>
				<div id='dialog-head'>Alert!</div>
				<div id='dialog-content'></div>
				<div id='dialog-foot'><input type='button' id='yes' value='Yes'/><input type='button' id='no' value='No'></div>
			</div>
			<div id='transaction-box'>
				<div id='transaction-head'>Enter Transaction Password</div>
				<div id='transaction-content'><input class='prompt-input' id='password' type='password' maxlength='20' /></div>
				<div id='transaction-foot'><p><input type='checkbox' id='check' />Remember password for this session.</p><p><button class='submit' id='submit'>Submit</button></p></div>
			</div>
		</div>
		<div id='disable'></div>
		<div id='feedback'></div>
		<div id='header'>
			<h1>IET Mess Online</h1><span id='tagline'><i>one step towards transparency...</i></span><br /><span>".$mess."</span>
			<a id='logout' href='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."logout.php'>Logout</a>
		</div>
		<nav>
			<a href='..".DIRECTORY_SEPARATOR."accounts'>Accounts</a>
			<a href='..".DIRECTORY_SEPARATOR."expense' class='slide'>Expenses</a>
			<a href='..".DIRECTORY_SEPARATOR."servings' class='slide'>Servings</a>
			<a href='..".DIRECTORY_SEPARATOR."tuning' class='slide'>Tuning</a>
			<a href='..".DIRECTORY_SEPARATOR."notice' class='slide'>Notice</a>
			<a href='' class='slide'>Balance</a>
		</nav>
		<div id='middle'>
			<div id='container'>
				<div id='tab_container'>
					<div class='tab selected' serial='1'>Balance</div>
					<div class='tab border' serial='2'>Session</div>
					<div class='tab border' serial='3'>Bills</div>
					<div class='tab border' serial='4'>Coupons</div>
				</div>
				<div id='content_container'>
					<div class='content' serial='1'><p>Total: ".$total." INR<br/>Expense: ".$expense." INR</p></div>
					<div class='content' serial='2'>";
						if ( $status == 2 ) 
							echo "<a href='#' id='start'>Start Session</a>";
					echo "
					</div>
					<div class='content' serial='3'>
						Bills
					</div>
					<div class='content' serial='4'>
						Coupons
					</div>
				</div>
			</div>
		</body>
		</html>
		";
	} else {
		header('Location: ..'.DIRECTORY_SEPARATOR);
	}
	mysqli_close( $con );
?>