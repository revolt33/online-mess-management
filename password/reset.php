<?php
	require "../connection.php";
	$escape = "SET sql_mode='NO_BACKSLASH_ESCAPES'";
	mysqli_query($con, $escape);
	if ($_GET) {
		if ( isset($_GET['user_id']) && isset($_GET['code']) && isset($_GET['type']) && isset($_GET['set']) ) {
			$username = trim(htmlentities($_GET['user_id']));
			$code = trim(htmlentities($_GET['code']));
			$type = trim(htmlentities($_GET['type']));
			$set = intval(htmlentities($_GET['set'])); // 0 for new user(set password) 1 for reset password
			$type = intval($type);
			$state = "";
			$db = "admin";
			$title = "";
			$signal = true;
			if ( $type > 0 ) {
				mysqli_select_db($con, $db);
				$dbstr = "select mess from messdetails where serial=".$type;
				$dbquery = mysqli_query($con, $dbstr);
				if ( $dbquery ) {
					if ( mysqli_num_rows($dbquery) > 0 ) {
						$dbrow = mysqli_fetch_array($dbquery);
						$db = $dbrow[0];
					} else {
						$signal = false;
					}
				} else {
					$signal = false;
				}
			}
			if ( $signal ) {
				mysqli_select_db( $con, $db );
				$str = "select * from users where id=".$username." and fpactive='y'";
				$query = mysqli_query($con, $str);
				if ( $query ) {
					if ( mysqli_num_rows( $query ) > 0 ) {
						$row = mysqli_fetch_array( $query );
						$code = encryptPassword( $username, $code );
						if ( strcmp( $row['password'], $code ) != 0 ) {
							$signal = false;
						}
					} else {
						$signal = false;
					}
				} else {
					$signal = false;
				}
			}
			$content = "";
			$id = "";
			if ( $signal ) {
				if ( $set == 0 ) {
					$state = "Set";
					$title = "Set your password";
				} else if ( $set == 1 ) {
					$state = "Reset";
					$title = "Reset your password";
				}
				$id = "reset";
				$content = "<form action='store.php' method='POST'><p>".$state." Password</p><p><input name='pass' id='pass' placeholder='Your new password' type='password' /></p><p><input name='repeat' id='repeat' placeholder='Repeat password' type='password' /></p><input type='hidden' id='auth' code='".$code."' username='".$username."' usertype='".$type."' /><p><button id='submit' name='submit'>Reset</button></p></form>";
			} else {
				$id = "error";
				$title = "Error:: Broken Link!";
				$content = "<p>Sorry, this link is broken.</p>";
			}
			echo "
			<!DOCTYPE html>
			<html>
			<head>
				<title>".$title."</title>
				<script type='text/javascript' src='../jquery.js' defer></script>
				<script type='text/javascript' src='reset.js' defer></script>
				<link rel='stylesheet' type='text/css' href='password.css' />
			</head>
			<body>
			<div id='disable'></div>
			<div id='header'><h1>Welcome to Online Mess Management</h1></div>
			<div id='".$id."' class='dialog-box'>".$content."</div>
			<div id='feedback'></div>
			</body>
			</html>
			";
		} else {
			header('location: ../index.php');
		}
	} else {
		header('location: ../index.php');
	}
	function encryptPassword($serial, $password) {
		$temp_serial = sha1($serial);
		$options = [
		    'cost' => 11,
		    'salt' => hash('sha256', $temp_serial),
		];
		$password = password_hash( $password, PASSWORD_BCRYPT, $options );
		return $password;
	}
	mysqli_close($con);
?>