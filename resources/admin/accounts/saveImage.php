<?php
	require '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'connection.php';
	require '..'.DIRECTORY_SEPARATOR.'utility.php';
	session_start();
	session_regenerate_id(true);
	$escape = "SET sql_mode='NO_BACKSLASH_ESCAPES'";
	mysqli_query($con, $escape);
	if ( checkAuthToken() && checkMode( $con ) ) {
		if ( isset($_POST['serial']) && isset($_FILES['file']) ) {
			$serial = trim(htmlentities($_POST['serial']));
			$serial = intval($serial);
			$str = "select * from users where id=".$serial;
			mysqli_select_db($con, $_SESSION['database']);
			$query = mysqli_query( $con, $str );
			if ( $query ) {
				if ( mysqli_num_rows( $query ) > 0 ) {
					$row = mysqli_fetch_array( $query );
					$tmp_name = $_FILES['file']['tmp_name'];
					$size = $_FILES['file']['size'];
					$type = $_FILES['file']['type'];
					if ( ( $size <= ( 50 * 1024 ) ) && ( $type == 'image/jpg' || $type == 'image/jpeg' || $type == 'image/png' ) ) {
						$name = $row['image'];
						mysqli_select_db($con, 'admin');
						$str = "select * from messdetails where mess='".$_SESSION['database']."'";
						$query = mysqli_query( $con, $str );
						$row = mysqli_fetch_array( $query );
						$mess = $row['serial'];
						$image = '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'hostels'.DIRECTORY_SEPARATOR."hostel".$mess.DIRECTORY_SEPARATOR.$name;
						move_uploaded_file($tmp_name, $image );
					}
				}
			}
		}
	} else {
		header('Location: index.php');
	}
	mysqli_close($con);
?>