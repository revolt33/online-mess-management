<?php
	require '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'connection.php';
	require 'utility.php';
	session_start();
	session_regenerate_id(true);
	if ( checkAuthToken() && checkMode( $con ) ) {
		header('Location: accounts');
	} else {
		header('Location: ..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
	}
?>