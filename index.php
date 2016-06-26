<?php
	session_start();
	session_regenerate_id(true);
	if (isset($_SESSION['id']) && isset($_SESSION['type'])) {
		header('Location: resources');
	} else {
		echo "
		<!DOCTYPE html>
		<html>
		<head>
			<title>IET Lucknow Mess</title>
			<meta http-equiv='cache-control' content='no-cache' />
			<script type='text/javascript' src='jquery.js' defer></script>
			<script type='text/javascript' src='cookie.js' defer></script>
			<script type='text/javascript' src='init.js' defer></script>
			<link rel='stylesheet' type='text/css' href='style.css' />
			<link rel='stylesheet' type='text/css' href='plugins/accordion/styles.css' />
		</head>
		<body>
		
		</body>
		</html>
		";
	}
?>