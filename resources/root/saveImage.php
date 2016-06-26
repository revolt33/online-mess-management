<?php
	require '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'connection.php';
	session_start();
	session_regenerate_id(true);
	$escape = "SET sql_mode='NO_BACKSLASH_ESCAPES'";
	mysqli_query($con, $escape);
	if ( ($_SESSION['type'] == 'root') && !empty($_SESSION['id']) && !empty($_SESSION['type']) && !empty($_SESSION['name']) && !empty($_SESSION['database']) ) {
		if ( isset($_POST['serial']) && isset($_FILES['file']) ) {
			$serial = htmlentities($_POST['serial']);
			$tmp_name = $_FILES['file']['tmp_name'];
			$serial = intval($serial);
			$str = "select * from messdetails where serial=".$serial;
			mysqli_select_db($con, 'admin');
			$query = mysqli_query( $con, $str );
			if ( $query ) {
				if ( mysqli_num_rows( $query ) > 0 ) {
					$row = mysqli_fetch_array( $query );
					$tmp_name = $_FILES['file']['tmp_name'];
					$size = $_FILES['file']['size'];
					$type = $_FILES['file']['type'];
					if ( ( $size <= ( 512 * 1024 ) ) && ( $type == 'image/jpg' || $type == 'image/jpeg' || $type == 'image/png' ) ) {
						$name = $row['image'];
						$image = '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'messImages'.DIRECTORY_SEPARATOR.$name;
						$thumb = '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'messImages'.DIRECTORY_SEPARATOR.'thumbnails'.DIRECTORY_SEPARATOR.$name;
						if ( file_exists( $image ) )
							unlink( $image );
						if ( file_exists( $thumb ) )
							unlink( $thumb );
						move_uploaded_file($tmp_name, $image );
						$img = imagecreatefromjpeg( $image );
						$width = imagesx( $img );
						$height = imagesy( $img );
						$thumbWidth = 200;
						$new_width = $thumbWidth;
						$new_height = floor( $height * ( $thumbWidth / $width ) );
						$tmp_img = imagecreatetruecolor( $new_width, $new_height );
						imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
						imagejpeg( $tmp_img, $thumb );
					}
				}
			}
		}
	} else {
		header('Location: mess.php');
	}
	mysqli_close($con);
?>