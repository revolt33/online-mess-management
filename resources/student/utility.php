<?php
function validateEmail($email) {
	$regex = '/^[a-zA-z0-9][a-zA-z0-9\._\-&!?=#]*@[\w]+(\.\w{2,3})+$/';
	if ( preg_match( $regex, $email ) ) {
		return true;
	} else {
		return false;
	}
}
function rand_string( $length ) {
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	$size = strlen( $chars );
	$str = "";
	for( $i = 0; $i < $length; $i++ ) {
		$str .= $chars[ rand( 0, $size - 1 ) ];
	}
	return $str;
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
function validateDate( $date ) {
	$temp_date = DateTime::createFromFormat('Y-m-d', $date);
	if ( $temp_date ) {
		$temp_date = strval($temp_date->format('Y-m-d'));
		if ( strcmp($temp_date, $date) == 0 ) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}
function checkPassword($con, $var) {
	$rememberPassword = trim(htmlentities($var->type->rememberPassword));
	$password = "";
	if ( $rememberPassword == 'c' ) {
		$password = $_SESSION['password'];
	} else {
		$password = trim(htmlentities($var->values->password));
	}
	mysqli_select_db( $con, 'admin' );
	$str = "select * from messdetails where mess='".$_SESSION['database']."'";
	$query = mysqli_query( $con, $str );
	if ( $query ) {
		$row = mysqli_fetch_array( $query );
		if ( $rememberPassword == 'c' ) {
			$password = $_SESSION['password'];
		} else {
			$password = encryptPassword($row['serial'], $password);
		}
		if ( $row['password'] == $password ) {
			if ( $rememberPassword == 'y' ) {
				$_SESSION['password'] = $password;
			}
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}
function checkSession($con) {
	mysqli_select_db( $con, 'admin' );
	$str = "select status from messdetails where mess='".$_SESSION['database']."'";
	$query = mysqli_query( $con, $str );
	if ( $query ) {
		$row = mysqli_fetch_array( $query );
		if ( $row[0] == 1 )
			return true;
		else
			return false;
	}
	return false;
}
function checkMode( $con ) {
	mysqli_select_db( $con, 'admin' );
	$str = "select mode from messdetails where mess='".$_SESSION['database']."'";
	$query = mysqli_query( $con, $str );
	if ( $query ) {
		$row = mysqli_fetch_array( $query );
		if ( $row[0] == 'e' )
			return true;
		else
			return false;
	}
	return false;
}
function checkAuthToken() {
	if ( ($_SESSION['type'] == 'user') && !empty($_SESSION['id']) && !empty($_SESSION['type']) && !empty($_SESSION['name']) && !empty($_SESSION['database']) )
		return true;
	else
		return false;
}
function getNotifsCount($con) {
	mysqli_select_db( $con, $_SESSION['database'] );
	$str = "select count(*) from notifs_".$_SESSION['id']." where status='n'";
	$query = mysqli_query( $con, $str );
	$count = 0;
	if ( $query ) {
		$count = mysqli_fetch_array( $query )[0];
	}
	return $count;
}
?>