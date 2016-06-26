<?php
	require '..'.DIRECTORY_SEPARATOR.'connection.php';
	require 'util.php';
	session_start();
	session_regenerate_id(true);
	if ( isset($_SESSION['id']) && isset($_SESSION['type']) && isset($_SESSION['name']) && isset($_SESSION['database']) ) {
		$type = $_SESSION['type'];
		if ( $type == 'root' ) {
			header('Location: root');
		} else if ( $type == 'admin' && checkMess( $con, $_SESSION['database'] ) ) {
			header('Location: admin');
		} else if ( $type == 'user' && checkMess( $con, $_SESSION['database'] ) ) {
			header('Location: student');
		} else {
			header('Location: ..'.DIRECTORY_SEPARATOR.'logout.php');
		}
	} else {
		header('Location: ..'.DIRECTORY_SEPARATOR.'index.php');
	}
	mysqli_close($con);
?>