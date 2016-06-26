<?php
	require '../connection.php';
	$escape = "SET sql_mode='NO_BACKSLASH_ESCAPES'";
	mysqli_query($con, $escape);
	if ( !empty($_POST['user_id']) && ($_POST['user_type'] >= 0 ) && !empty($_POST['email'])) {
		$username = trim(htmlentities($_POST['user_id']));
		$usertype = trim(htmlentities($_POST['user_type']));
		$email = trim(htmlentities($_POST['email']));
		$usertype = intval($usertype);
		$db = "admin";
		$signal = true;
		$msg = "";
		$status = 3; //0 for valid email, 1 for invalid email, 2 for not a valid email, 3 for unauthentic request
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
		$regex = '/^[a-zA-z0-9][a-zA-z0-9\._\-&!?=#]*@[\w]+(\.\w{2,3})+$/';
		if ( !preg_match( $regex, $email ) ) {
			$status = 2;
			$signal = false;
		}
		if ( $signal ) {
			$row = mysqli_fetch_array($query);
			if ( strcmp($row['email'], $email) == 0 ) {
				$code = rand_string(8);
				$code = encryptPassword( $username, $code );
				$reset = "update users set fpactive='y', password='".$code."' where id=".$username;
				if ( mysqli_query($con, $reset)) {
					$status = 0;
					$msg = "Reset link sent to email.";
					// Send email.... with an embedded md5 encoded password of rand_string()
				} else {
					$status = 4;
					$msg = "Your request could not be honoured.";
				}
			} else {
				$status = 1;
				$msg = "Inavalid email!";
			}
		} else {
			$msg = "Unauthentic request!";
		}
		if ( $status == 2 ) {
			$msg = "Not a valid email";
		}
		$response = array();
		array_push($response, array( 'status' => $status, 'msg' => $msg ));
		echo json_encode(array( 'response' => $response ));
	} else {
		header('location: ../index.php');
	}
	function rand_string( $length ) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$size = strlen( $chars );
		$str = "";
		for( $i = 0; $i < $length; $i++ ) {
			$str .= $chars[ rand( 0, $size - 1 ) ];
		}
		return "password";
	}
	function encryptPassword($serial, $password) {
		$password = md5($password);
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