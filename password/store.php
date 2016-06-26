<?php
	require "../connection.php";
	$escape = "SET sql_mode='NO_BACKSLASH_ESCAPES'";
	mysqli_query($con, $escape);
	session_start();
	session_regenerate_id(true);
	if ($_POST) {
		if ( isset($_POST['user_id']) && isset($_POST['user_type']) && isset($_POST['code']) && isset($_POST['pass']) && isset($_POST['repeat']) ) {
			$username = trim(htmlentities($_POST['user_id']));
			$usertype = trim(htmlentities($_POST['user_type']));
			$code = trim(htmlentities($_POST['code']));
			$pass = trim(htmlentities($_POST['pass']));
			$repeat = trim(htmlentities($_POST['repeat']));
			$usertype = intval($usertype);
			$db = "admin";
			$signal = true;
			$status = 9;
			$msg = "";
			$type = "";
			$database = "";
			if ( $usertype > 0 ) {
				mysqli_select_db($con, $db);
				$dbstr = "select mess from messdetails where serial=".$usertype;
				$dbquery = mysqli_query($con, $dbstr);
				if ( $dbquery  ) {
					if ( mysqli_num_rows($dbquery) > 0 ) {
						$dbrow = mysqli_fetch_array($dbquery);
						$db = $dbrow[0];
						$type = "user";
					} else {
						$signal = false;
					}
				} else {
					$signal = false;
				}
			} else {
				$typestr = "select serial from users where id=".$username;
				mysqli_select_db($con, $db);
				$typequery = mysqli_query($con, $typestr);
				if ( $typequery ) {
					if ( mysqli_num_rows($typequery) == 1 ) {
						$accesstyperow = mysqli_fetch_array( $typequery );
						$accesstype = $accesstyperow[0];
						if ( $accesstype > 0 ) {
							$type = "admin";
							$database = fetchDatabase ($con, $accesstype);
						} else {
							$type = "root";
							$database = "admin";
						}
					}
				}
			}
			if ( $signal ) {
				$str = "select * from users where id=".$username;
				mysqli_select_db( $con, $db );
				$query = mysqli_query( $con, $str );
				if ( $query ) {
					if ( mysqli_num_rows( $query ) > 0 ) {
						$row = mysqli_fetch_array( $query );
						if ( $row['fpactive'] == 'y' ) {
							if ( $row['password'] == $code ) {
								if ( $pass == $repeat ) {
									if ( strlen($pass) > 7 ) {
										if ( strlen($pass) < 21 ) {
											$pass = encryptPassword( $username, $pass );
											$update = "update users set password='".$pass."', fpactive='n' where id=".$username;
											$updateQuery = mysqli_query( $con, $update );
											if ( $updateQuery ) {
												$status = 0;
												$_SESSION['id'] = $username;
												$_SESSION['type'] = $type;
												$_SESSION['name'] = $row['name'];
												if ( $usertype > 0 ) {
													$_SESSION['database'] = $db;
												} else {
													$_SESSION['database'] = $database;
												}
												$msg = "Password reset successful.";
											} else {
												$status = 7;
												$msg = "Error processing request.";
											}
										} else {
											$status = 6;
											$msg = "Maximum 20 character password allowed.";
										}
									} else {
										$status = 5;
										$msg = "Minimum 8 character Password required.";
									}
								} else {
									$status = 4;
									$msg = "Passwords do not match.";
								}
							} else {
								$status = 2;
								$msg = "Invalid code!";
							}
						} else {
							$status = 1;
							$msg = "This account is active.";
						}
					} else {
						$status = 3;
						$msg = "Invalid username!";
					}
				} else {
					$status = 8;
					$msg = "Error processing request.";
				}
			}
			if ( !$signal ) {
				$status = 9;
				$msg = "Error processing request.";
			}
			$response = array();
			array_push($response, array( 'status' => $status, 'msg' => $msg ));
			echo json_encode(array( 'response' => $response ));
		} else {
			header('locaton: ../index.php');
		}
	} else {
		header('locaton: ../index.php');
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
	function fetchDatabase($con, $serial) {
		$db = "admin";
		mysqli_select_db($con, $db) OR die(fail());
		$str_dbquery = "select mess from messdetails where serial=".$serial;
		$dbquery = mysqli_query($con, $str_dbquery);
		$result = mysqli_fetch_array($dbquery);
		return $result['mess'];
	}
	mysqli_close($con);
?>