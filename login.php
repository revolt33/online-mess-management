<?php
	session_start();
	session_regenerate_id(true);
	require "connection.php";
	require 'resources'.DIRECTORY_SEPARATOR.'util.php';
	$escape = "SET sql_mode='NO_BACKSLASH_ESCAPES'";
	mysqli_query($con, $escape);
	date_default_timezone_set("Asia/Calcutta");
	if ( isset($_POST['data']) ) {
		$var = json_decode($_POST['data']);
		$type = $var->type->type;
		if ( strcmp( $type, "login" ) == 0 ) {
			$username = trim(htmlentities($var->data->username));
			$password = trim(htmlentities($var->data->password));
			$usertype = trim(htmlentities($var->data->type));
			$usertype = intval($usertype);
			$username = intval($username);
			$valid = 0;
			$db = "";
			$_SESSION['type'] = '';
			if ( $usertype > 0 ) {
				$db = fetchDatabase($con, $usertype);
				$_SESSION['database'] = $db;
				$_SESSION['type'] = 'user';
			} else if ( $usertype == 0 ) {
				$db = "admin";
			}
			mysqli_select_db($con, $db) OR die(fail());
			$str = "select * from users where id = ".$username;
			$query = mysqli_query($con, $str);
			$msg = "";
			$remember = 'n';
			$remaining = '0';
			$status = 6; //0 for successful login, 1 for incorrect password, 2 for invalid username, 3 for fpactive, 4 for connection failed.
			$response = array();
			$valid = true;
			if ( $_SESSION['type'] == 'user' ) {
				$str = "select case status when 'r' then false else true end 'status' from members where id=".$username;
				$valid = mysqli_fetch_array( mysqli_query( $con, $str ) )[0];
			}
			if ( mysqli_num_rows($query) > 0 && $valid ) {
				$row = mysqli_fetch_array($query);
				if ( $row['fpactive'] == 'y' ) {
					$msg = "Reset Password requested.";
					$status = 3;
				} else {
					$password = encryptPassword( $username, $password );
					if ( strcmp($password, $row['password']) == 0 ) {
						$msg = "Login Successful!";
						$_SESSION['id'] = $username;
						$_SESSION['name'] = $row['name'];
						if ( $usertype == 0 ) {
							if ( $row['serial'] > 0 ) {
								$database = fetchDatabase($con, $row['serial']);
								$_SESSION['database'] = $database;
								$_SESSION['type'] = 'admin';
							} else {
								$_SESSION['type'] = 'root';
								$_SESSION['database'] = 'admin';
							}
						}
						$remember = $row['remember'];
						$today = new DateTime('now');
						$upto = strtotime($row['upto']);
						$valid = new DateTime();
						$valid->setTimestamp($upto);
						$diff = date_diff($today, $valid);
						$remaining = (string)$diff->format("%R%a");
						$status = 0;
						if ( $_SESSION['type'] == 'admin' ) {
							if ( !checkMess( $con, $database ) ) {
								$status = 5;
								$msg = "Please contact administrator to activate this mess.";
							}
						}
					} else {
						$msg = "Password Incorrect!";
						$status = 1;
					}
				}	
			} else {
				$msg = "Invalid Username!";
				$status = 2;
			}
			array_push($response, array('status' => $status, 'msg' => $msg ));
			if ( $status == 0 ) {
				array_push($response, array('id' => $username, 'type' => $usertype, 'remember' => $remember, 'valid' => $remaining));
			} else {
				nullifySessions();
			}
			echo json_encode(array('response' => $response));
		} else if ( strcmp( $type, "remember") == 0 ) {
			$username = trim(htmlentities($var->data->username));
			$usertype = trim(htmlentities($var->data->usertype));
			$usertype = intval($usertype);
			$db = "";
			$accesstype = "";
			$status = 2; // 0 stands for id is valid and remember password is checked, 1 for
			// id is valid but account is not remembered, 2 stands for id is invalid.
			$response = array();
			$str = "select * from users where id=".$username;;
			if ( $usertype > 0 ) {
				$accesstype = "user";
				$db = fetchDatabase($con, $usertype);
				$dbquery = $db;
			}
			
			if ( $usertype == 0 ) {
				mysqli_select_db($con, "admin");
				$str_usertype = "select * from users where id=".$username;
				$usertype_query=mysqli_query($con, $str_usertype);
				$result = mysqli_fetch_array($usertype_query);
				if ( $result['serial'] > 0 ) {
					$accesstype = "admin";
					$db = fetchDatabase( $con, $result['serial'] );
					$dbquery = "admin";
				} else {
					$accesstype = "root";
					$db = "admin";
					$dbquery = $db;
				}
			}
			mysqli_select_db($con, $dbquery);
			$query = mysqli_query($con, $str);
			if (mysqli_num_rows($query) > 0) {
				$row = mysqli_fetch_array($query);
				if ( $row['remember'] == 'y' ) {
					$_SESSION['id'] = $username;
					$_SESSION['name'] = $row['name'];
					$_SESSION['type'] = $accesstype;
					$_SESSION['database'] = $db;
					$status = 0;
				} else {
					$status = 1;
				}
			}
			array_push($response, array('status' => $status));
			echo json_encode(array('response' => $response));
		}
	}else {
		header('location: logout.php');
	}
	mysqli_close($con);
	function fail() {
		$status = 4;
		$msg = 'Connection Failed!';
		die(json_encode(array('response' => array('status' => $status, 'msg' => $msg ))));
	}
	function fetchDatabase($con, $serial) {
		$db = "admin";
		mysqli_select_db($con, $db) OR die(fail());
		$str_dbquery = "select mess from messdetails where serial=".$serial;
		$dbquery = mysqli_query($con, $str_dbquery);
		$result = mysqli_fetch_array($dbquery);
		return $result['mess'];
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
	function nullifySessions() {
		$_SESSION = array();
		if ( ini_get("session.use_cookies") ) {
			$params = session_get_cookie_params();
	    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
		}
	}
?>