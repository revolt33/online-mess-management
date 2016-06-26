<?php
	require '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'connection.php';
	require '..'.DIRECTORY_SEPARATOR.'utility.php';
	session_start();
	session_regenerate_id(true);
	$escape = "SET sql_mode='NO_BACKSLASH_ESCAPES'";
	mysqli_query($con, $escape);
	if ( checkAuthToken() && checkMode( $con ) ) {
		if ( $_POST ) {
			if ( isset($_POST['name']) && isset($_FILES['image']) ) {
				$name = trim(htmlentities( $_POST['name'] ));
				$str = "select serial from messdetails where mess='".$_SESSION['database']."'";
				mysqli_select_db( $con, 'admin' );
				$query = mysqli_query( $con, $str );
				if ( $query && mysqli_num_rows( $query ) > 0 ) {
					$row = mysqli_fetch_array( $query );
					$serial = $row[0];
					$tmp_name = $_FILES['image']['tmp_name'];
					$size = $_FILES['image']['size'];
					$type = $_FILES['image']['type'];
					if ( ( $size <= ( 512 * 1024 ) ) && ( $type == 'image/jpg' || $type == 'image/jpeg' || $type == 'image/png' ) ) {
						$image = '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'hostels'.DIRECTORY_SEPARATOR."hostel".$serial.DIRECTORY_SEPARATOR.$name;
						move_uploaded_file($tmp_name, $image );
						echo "Photo uploaded successfully.";
					} else
						echo "Invalid photo type.";
				} else
					echo "Some Error Occured.";
			}
		}
	} else {
		header('Location: ..'.DIRECTORY_SEPARATOR.'expense');
	}
	mysqli_close( $con );
?>