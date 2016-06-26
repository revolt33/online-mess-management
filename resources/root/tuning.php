<?php
	require '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'connection.php';
	session_start();
	session_regenerate_id(true);
	if ( ( $_SESSION['type'] == 'root' ) && !empty($_SESSION['id']) && !empty($_SESSION['type']) && !empty($_SESSION['name']) && !empty($_SESSION['database']) ) {
		$str = "select * from users where id=".$_SESSION['id'];
		mysqli_select_db( $con, 'admin' );
		$query = mysqli_query( $con, $str );
		$email = "";
		$remember = 'n';
		$remaining = 0;
		if ( $query ) {
			$row = mysqli_fetch_array( $query );
			$email = $row['email'];
			$remember = $row['remember'];
			$today = new DateTime('now');
			$upto = strtotime($row['upto']);
			$valid = new DateTime();
			$valid->setTimestamp($upto);
			$diff = date_diff($today, $valid);
			$remaining = (string)$diff->format("%R%a");
			$remaining = intval($remaining);
		}
		echo "
		<!DOCTYPE html>
		<html>
		<head>
			<title>Tuning</title>
			<meta http-equiv='cache-control' content='no-cache'>
			<link rel='stylesheet' type='text/css' href='root.css' />
			<link rel='stylesheet' type='text/css' href='tuning.css' />
			<link rel='stylesheet' type='text/css' href='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR."accordion".DIRECTORY_SEPARATOR."styles.css' />
			<script type='text/javascript' src='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."jquery.js' defer></script>
			<script type='text/javascript' src='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."utility.js' defer></script>
			<script type='text/javascript' src='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."cookie.js' defer></script>
			<script type='text/javascript' src='tuning.js' defer></script>
		</head>
		<body>
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
			<div id='dialog-overlay'>
				<div id='dialog-box'>
					<div id='dialog-head'>Please enter your password.</div>
					<div id='dialog-content'><input type='password' maxlength-'20' id='password' /></div>
					<div id='dialog-foot'><button class='button2' id='submit'>Submit</div>
				</div>
			</div>
			<div id='middle'>
				<div id='container'>
					<div id='email_address'>
						<div id='email_info'>
							<p>Email address: &nbsp&nbsp<span id='email_feed'>".$email."</span> &nbsp&nbsp <a href='#' id='change'>Change Email</a></p>
						</div>
						<div id='change_email' >
						<form>
							<input type='email' placeholder='Your new email...' maxlength='100' id='email' />
							<button class='button' id='change_email_button'>Change</button>
						</form>
						</div>
					</div>
					<div id='pass'>
						<div>
							<a href='#' id='pass_text'>Change Password</a>
						</div>
						<div id='pass_change'>
						<form>
							<input placeholder='New Password' type='password' id='new_pass' maxlength='20' /><br/><br/>
							<input placeholder='Repeat Password' type='password' id='repeat_pass' maxlength='20' /><br/><br/>
							<button class='button1 button' id='change_pass_button'>Change Password</button>
						</form>
						</div>
					</div>
					<div id='remember'>
						<p>Remember me: <input type='checkbox' id='check' "; if( $remember == 'y' ) { echo "checked"; } echo "/><span id='remaining'>";if ( $remaining >= 0 ) {echo $remaining." days remaining...";} else { echo "Expired..."; } echo "</span>&nbsp&nbsp<a href='#' id='refresh'>Refresh</a></p>
					</div>
				</div>
			</div>
		</body>
		";
	} else {
		header('Location: ..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
	}
	mysqli_close( $con );
?>