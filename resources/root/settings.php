<?php
	require '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'connection.php';
	session_start();
	session_regenerate_id(true);
	$escape = "SET sql_mode='NO_BACKSLASH_ESCAPES'";
	date_default_timezone_set("Asia/Calcutta");
	mysqli_query($con, $escape);
	if ( ( $_SESSION['type'] == 'root' ) && !empty($_SESSION['id']) && !empty($_SESSION['type']) && !empty($_SESSION['name']) && !empty($_SESSION['database']) ) {
		if ( $_POST ){
			if ( isset($_POST['data']) ) {
				$var = json_decode($_POST['data']);
				$type = htmlentities( $var->type->name );
				if ( strcmp($type, 'email' ) == 0 ) {
					$email = htmlentities( $var->values->email );
					$pass = htmlentities( $var->values->pass );
					$id = $_SESSION['id'];
					$status = -1;
					$msg = "";
					mysqli_select_db( $con, 'admin' );
					if ( checkPassword( $con, $pass ) ) {
						$str = "update users set email='".$email."' where id=".$id;
						if ( validateEmail( $email ) ) {
							$query = mysqli_query( $con, $str );
							if ( $query ) {
								$status = 0;
								$msg = "Email changed successfully.";
							} else {
								$status = 2;
								$msg = "Some error occured.";
							}
						} else {
							$status = 1;
							$msg = "Invalid email.";
						}
					} else {
						$status = 3;
						$msg = "Incorrect password.";
					}
					$response = array();
					array_push($response, array( 'status' => $status, 'msg' => $msg ));
					if ( $status == 0 ) {
						array_push($response, array( 'email' => $email ));
					}
					echo json_encode(array( 'response' => $response ));
				} else if ( strcmp( $type, 'password' ) == 0 ) {
					$password = trim(htmlentities( $var->values->pass ));
					$new_pass = trim(htmlentities( $var->values->new_pass ));
					$repeat_pass = trim(htmlentities( $var->values->repeat_pass ));
					$id = $_SESSION['id'];
					$status = -1;
					$msg = "";
					mysqli_select_db( $con, 'admin' );
					if ( checkPassword( $con, $password ) ) {
						if ( ( strlen( $new_pass ) > 0 ) && ( strlen( $new_pass ) < 20 ) ) {
							if ( strcmp( $new_pass , $repeat_pass ) == 0 ) {
								$new_pass = encryptPassword( $id, $new_pass );
								$str = "update users set password='".$new_pass."' where id=".$id;
								$query = mysqli_query( $con, $str );
								if ( $query ) {
									$status = 0;
									$msg = "Password changed successfully.";
								} else {
									$status = 1;
									$msg = "Error changing password.";
								}
							} else {
								$status = 2;
								$msg = "Passwords do not match.";
							}
						} else {
							$status = 4;
							$msg = "Password's length inappropriate.";
						}
					} else {
						$status = 3;
						$msg = "Incorrect password.";
					}
					$response = array();
					array_push($response, array( 'status' => $status, 'msg' => $msg ));
					echo json_encode(array( 'response' => $response ));
				} elseif ( strcmp( $type , 'refresh' ) == 0 ) {
					$check = htmlentities( $var->values->check );
					$next = "0000-00-00";
					$remember = "n";
					if ( $check ) {
						$date = strtotime("+7 days");
						$next = date('Y-m-d', $date);
						$remember = "y";
					} else {
						$date = strtotime("-1 days");
						$next = date('Y-m-d', $date);
						$remember = "n";
					}
					$id = $_SESSION['id'];
					mysqli_select_db( $con, 'admin' );
					$str = "update users set upto='".$next."', remember='".$remember."' where id=".$id;
					$query = mysqli_query( $con, $str );
					$status = -1;
					$msg = "";
					$response = array();
					if ( $query ) {
						if ( $remember == "y" ) {
							$status = 0;
							$msg = "6 days remaining...";
						} elseif ( $remember == "n" ) {
							$status = 1;
							$msg = "Expired...";
						}
					} else {
						$status = 2;
						$msg = "Refresh failed.";
					}
					$response = array();
					array_push($response, array( 'status' => $status, 'msg' => $msg ));
					if ( ( $status == 0 ) || ( $status == 1 ) ) {
						array_push($response, array( 'username' => $id, 'type' => 0, 'remember' => $remember ));
					}
					echo json_encode(array( 'response' => $response ));
				}
			}
		} else {
			header('Loocation: mess.php');
		}
	} else {
		header('Location: mess.php');
	}
	mysqli_close( $con );
	function validateEmail($email) {
		$regex = '/^[a-zA-z0-9][a-zA-z0-9\._\-&!?=#]*@[\w]+(\.\w{2,3})+$/';
		if ( preg_match( $regex, $email ) && ( strlen($email) < 101 ) ) {
			return true;
		} else {
			return false;
		}
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
	function checkPassword( $con, $password ) {
		$id = $_SESSION['id'];
		$str = "select password from users where id=".$id;
		$query = mysqli_query( $con, $str );
		$password = encryptPassword( $id, $password );
		$val = false;
		if ( $query ) {
			if ( mysqli_num_rows( $query ) > 0 ) {
				$row = mysqli_fetch_array( $query );
				if ( strcmp( $password, $row[0] ) == 0 ) {
					$val = true;
				}
			}
		}
		return $val;
	}
?>