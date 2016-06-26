<?php
	require '../connection.php';
	$escape = "SET sql_mode='NO_BACKSLASH_ESCAPES'";
	mysqli_query($con, $escape);
	if ($_POST) {
		$username = trim(htmlentities($_POST['username']));
		$usertype = trim(htmlentities($_POST['usertype']));
		$usertype = intval($usertype);
		$username = intval($username);
		$signal = true;
		$status = 2; // 0 for valid user, 1 for invalid user, 2 for Database not found(Connection error)
		$msg = 'Connection Error';
		mysqli_select_db($con, 'admin');
		$str = "select * from users where id=".$username;
		if ( $usertype > 0 ) {
			$db_str = "select mess from messdetails where serial=".$usertype;
			$dbquery = mysqli_query($con, $db_str);
			if ( mysqli_num_rows($dbquery) > 0 ) {
				$row = mysqli_fetch_array($dbquery);
				$db = $row['0'];
				mysqli_select_db($con, $db);
			} else {
				$signal = false;
			}
		}
		if ( $signal ) {
			$query = mysqli_query($con, $str);
			if ( mysqli_num_rows($query) > 0 ) {
				$status = 0;
				$msg = 'Redirecting...';
			} else {
				$signal = false;
				$status = 1;
				$msg = 'Invalid User!';
			}
		}
		$response = array();
		array_push($response, array('status' => $status, 'msg' => $msg ));
		echo json_encode(array('response' => $response));
	} else if ($_GET) {
		if ( !empty($_GET['user_id']) ) {
			$username = trim(htmlentities($_GET['user_id']));
			$usertype = trim(htmlentities($_GET['user_type']));
			$usertype = intval($usertype);
			$db = "admin";
			$signal = true;
			$str = "select * from  users where id=".$username;
			if ( $usertype > 0 ) {
				mysqli_select_db($con, $db);
				$db_str = "select mess from messdetails where serial=".$usertype;
				$dbquery = mysqli_query( $con, $db_str );
				if ( mysqli_num_rows($dbquery) > 0 ) {
					$result = mysqli_fetch_array( $dbquery );
					$db = $result[0];
				} else {
					$signal = false;
				}
			}
			mysqli_select_db($con, $db);
			$query = mysqli_query($con, $str);
			if (mysqli_num_rows($query) == 0) {
				$signal = false;
			}
			$id = "";
			$content = "";
			if ( $signal ) {
				$id = "success";
				$content = "<form action='send.php' method='post' /><p>Request Password Reset</p><p><input id='email' type='email' placeholder='Enter your email' /></p><input type='hidden' username='".$username."' usertype='".$usertype."' id='auth' /><button id='submit'>Sumbit</button><p></form>";
			} else {
				$id = "error";
				$content = "<p>Sorry, this link is broken.</p>";
			}
			echo "
			<!DOCTYPE html>
			<html>
			<head>
				<title>Request Password Reset</title>
				<script type='text/javascript' src='../jquery.js' defer></script>
				<script type='text/javascript' src='../utility.js' defer></script>				
				<script type='text/javascript' src='forgot.js' defer></script>
				<link rel='stylesheet' type='text/css' href='password.css' />
			</head>
			<body>
				<div id='header'><h1>Welcome to Online Mess Management System</h1></div>
				<div id='".$id."' class='dialog-box'>".$content."</div>
				<div id='feedback'></div>
			</body>
			</html>
			";
		} else {
			header('location: ../index.php');
		}
	}
	mysqli_close($con);
?>