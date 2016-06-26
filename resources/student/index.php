<?php
	session_start();
	session_regenerate_id(true);
	if ( ($_SESSION['type'] == 'user') && !empty($_SESSION['id']) && !empty($_SESSION['type']) && !empty($_SESSION['name']) && !empty($_SESSION['database']) ) {
		header('Location: accounts');
	} else {
		header('Location: ..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
	}
?>