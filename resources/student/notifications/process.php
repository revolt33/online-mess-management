<?php
	require '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'connection.php';
	require '..'.DIRECTORY_SEPARATOR.'utility.php';
	session_start();
	date_default_timezone_set("Asia/Calcutta");
	$escape = "SET sql_mode='NO_BACKSLASH_ESCAPES'";
	mysqli_query($con, $escape);
	session_regenerate_id(true);
	if ( checkAuthToken() && checkMode( $con ) ) {
		if ( isset($_POST['data']) ) {
			$serial = intval($_POST['data']);
			mysqli_select_db( $con, $_SESSION['database'] );
			$str = "update notifs_".$_SESSION['id']." set status='y' where serial=".$serial;
			if ( mysqli_query( $con, $str ) ) {
				$str = "select table_1.notifs, table_2.notice from (select count(*) as notifs from notifs_".$_SESSION['id']." where type=0 and status='n') as table_1, (select count(*) as notice from notifs_".$_SESSION['id']." where type>0 and status='n') as table_2";
				$query = mysqli_query( $con, $str );
				if ( $query ) {
					$row = mysqli_fetch_array( $query );
					echo json_encode(array($row[0], $row[1]));
				}
			}
		}
	} else {
		header('Location: ..'.DIRECTORY_SEPARATOR.'notifications');
	}
?>