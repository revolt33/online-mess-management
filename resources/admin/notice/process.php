<?php
	require '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'connection.php';
	require '..'.DIRECTORY_SEPARATOR.'utility.php';
	session_start();
	date_default_timezone_set("Asia/Calcutta");
	$escape = "SET sql_mode='NO_BACKSLASH_ESCAPES'";
	mysqli_query($con, $escape);
	session_regenerate_id(true);
	if ( checkAuthToken() && checkMode( $con ) ) {
		if ( $_POST ) {
			$var = json_decode($_POST['passdetails']);
			if ( checkPassword( $con, $var ) ) {
				$heading = trim(htmlentities( $_POST['heading'] ));
				$text = trim( $_POST['text'] );
				$isPhoto = trim(htmlentities( $_POST['isPhoto'] ));
				$urgency = trim(htmlentities( $_POST['urgency'] ));
				$name = "";
				$image = NUll;
				$max = 0;
				$proceed = false;
				$tmp_name = "";
				$type = "";
				$response = array();
				$status = 0;
				$msg = "";
				if ( $isPhoto == 'yes' ) {
					$name = trim(htmlentities( $_POST['name'] ));
					$image = $_FILES['image'];
					$size = $_FILES['image']['size'];
					$type = $_FILES['image']['type'];
					$tmp_name = $_FILES['image']['tmp_name'];
					if ( $size <= 512*1024 ) {
						if ( $type == 'image/jpeg' || $type == 'image/jpg' || $type == 'image/png' ) {
							$proceed = true;
						} else {
							$msg = "Allowed file formats are: jpg, jpeg, png.";
						}
					} else {
						$msg = "File size must be less than 0.5 MB";
					}
				} else {
					$proceed = true;
				}
				if ( $proceed == true ) {
					$str = "select max(id) from notice";
					mysqli_select_db( $con, $_SESSION['database'] );
					$query = mysqli_query( $con, $str );
					if ( $query ) {
						$max = mysqli_fetch_array( $query )[0] + 1;
						if ( $isPhoto == 'yes' ) {
							$str = "select serial from messdetails where mess='".$_SESSION['database']."'";
							mysqli_select_db( $con, 'admin' );
							$serial = mysqli_fetch_array( mysqli_query( $con, $str ) )[0];
							$ext = "";
							if ( $type == 'image/jpeg' )
								$ext = "jpeg";
							else if ( $type == 'image/jpg' )
								$ext = "jpg";
							else if ( $type == 'image/png' )
								$ext = "png";
							$image_name = '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'hostels'.DIRECTORY_SEPARATOR."hostel".$serial.DIRECTORY_SEPARATOR."notice".$max.".".$ext;
							move_uploaded_file($tmp_name, $image_name );
							$str = "insert into notice values(".$max.", '".date('Y-m-d')."', '".$heading."', '".$text."', '".$urgency."', 'notice".$max.".".$ext."' )";
						} else if ( $isPhoto == 'no' ) {
							$str = "insert into notice values(".$max.", '".date('Y-m-d')."', '".$heading."', '".$text."', 'l', 'none' )";
						}
						mysqli_select_db( $con, $_SESSION['database'] );
						if ( mysqli_query( $con, $str ) ) {
							$str = "select id from users";
							$query = mysqli_query( $con, $str );
							if ( $query ) {
								while ( $row = mysqli_fetch_array( $query ) ) {
									$str = "insert into notifs_".$row['id']." (status, date, time, type) values ('n', '".date('Y-m-d')."', '".date('H:i:s')."', ".$max.")";
									mysqli_query( $con, $str );
								}
							}
							$status = 0;
							$smg =  "Notice uploaded successfully!";
						} else {
							$status = 1;
							$msg = "Notice could not be uploaded.";
						}
					} else {
						$status = 1;
						$msg = "Some error Occured!";
					}
				} else {
					$status = 1;
				}
			} else {
				$status = 2;
				$msg = "Transaction password incorrect.";
			}
			$response = array();
			array_push($response, array( 'status' => $status, 'msg' => $msg ));
			if ( $status != 2 )
				array_push( $response , array( 'rememberPassword' => $var->type->rememberPassword ) );
		}
		echo json_encode($response);
	} else {
		header('Location: ..'.DIRECTORY_SEPARATOR.'notice');
	}
	mysqli_close( $con );
?>