<?php
	require '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'connection.php';
	require '..'.DIRECTORY_SEPARATOR.'utility.php';
	session_start();
	date_default_timezone_set("Asia/Calcutta");
	$escape = "SET sql_mode='NO_BACKSLASH_ESCAPES'";
	mysqli_query($con, $escape);
	session_regenerate_id(true);
	if ( checkAuthToken() && checkMode( $con ) ) {
		if( isset($_POST['data']) ) {
			$var = json_decode($_POST['data']);
			switch ($var->head->type) {
				case 'change_pass':
					$old_pass = $var->values->old_pass;
					$new_pass = $var->values->new_pass;
					$repeat_pass = $var->values->repeat_pass;
					mysqli_select_db( $con, $_SESSION['database'] );
					$str = "select password from users where id=".$_SESSION['id'];
					$query = mysqli_query( $con, $str );
					if ( $query && mysqli_num_rows( $query ) == 1 ) {
						if ( encryptPassword( $_SESSION['id'], $old_pass ) == mysqli_fetch_array( $query )[0] ) {
							if ( strlen($new_pass) > 7 ) {
								if ( $new_pass == $repeat_pass ) {
									$str = "update users set password='".encryptPassword( $_SESSION['id'], $new_pass )."' where id=".$_SESSION['id'];
									if ( mysqli_query( $con, $str ) ) {
										echo "Password changed successfully.";
									} else {
										echo "Password could not be changed.";
									}
								} else {
									echo "Passwords should be same.";
								}
							} else {
								echo "New password should be atleast 8 characters long.";
							}
						} else {
							echo "Please provide correct password.";
						}
					} else {
						echo "Some error occured.";
					}
					break;
			}
		}
	} else {
		header('Location: ..'.DIRECTORY_SEPARATOR.'tuning');
	}
?>