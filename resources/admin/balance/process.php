<?php 
	require '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'connection.php';
	require '..'.DIRECTORY_SEPARATOR.'utility.php';
	session_start();
	session_regenerate_id(true);
	date_default_timezone_set("Asia/Calcutta");
	$escape = "SET sql_mode='NO_BACKSLASH_ESCAPES'";
	mysqli_query($con, $escape);
	if ( checkAuthToken() && checkMode( $con ) ) {
		if ( $_POST ) {
			if ( isset( $_POST['data'] ) ) {
				$var = json_decode( $_POST['data'] );
				$type = trim( htmlentities( $var->type->name ) );
				switch ($type) {
					case "start":
						$status = 4;
						$msg = "Some Error Occured!";
						$response = array();
						if ( checkPassword( $con, $var ) ) {
							mysqli_select_db( $con, 'admin' );
							$str = "select status from messdetails where mess='".$_SESSION['database']."'";
							$query = mysqli_query( $con, $str );
							$row = mysqli_fetch_array( $query );
							$str = "select count(*) from meals where status='a'";
							mysqli_select_db( $con, $_SESSION['database'] );
							$query = mysqli_query( $con, $str );
							$entries = 0;
							if ( $query ) {
								$row1 = mysqli_fetch_array( $query );
								$entries = $row1[0];
							}
							$signal = false;
							if ( $row[0] == 2 && $entries > 0 ) {
								$signal = true;
								mysqli_select_db( $con, 'admin' );
								$str = "update messdetails set status = 1, start='".date('Y-m-d')."' where mess='".$_SESSION['database']."'";
								$query = mysqli_query( $con, $str );
							}
							if ( $query && $signal ) {
								mysqli_select_db( $con, $_SESSION['database'] );
								$str = "insert into expense (start, status, initial, current, cost) values ('".date('Y-m-d')."', 'a', 1, 0, 0)";
								mysqli_query( $con, $str );
								$str = "insert into subexpense values (1, 'w', 0, 0)";
								mysqli_query( $con, $str );
								$str = "insert into subexpense values (1, 'e', 0, 0)";
								mysqli_query( $con, $str );
								$str = "insert into subexpense values (1, 'm', 0, 0)";
								mysqli_query( $con, $str );
								$str = "create table wage_1 (eid int(3), amount int(6), comment varchar(100), date date, transaction bigint(10), uid int(5), all_users varchar(1), FOREIGN KEY (eid) REFERENCES employee(id))";
								mysqli_query( $con, $str );
								$str = "create table misc_1 (transaction bigint(10), title varchar(50), comment varchar(200), date date, uid int(5), amount decimal(8,2), all_users varchar(1))";
								mysqli_query( $con, $str );
								$str = "create table exp_1 (transaction bigint(10), image varchar(50), title varchar(50), date date, uid int(5), amount decimal(8,2), all_users varchar(1))";
								mysqli_query( $con, $str );
								$status = 0;
								$msg = "Session Started";
							}  elseif ( $entries == 0 ) {
								$status = 3;
								$msg = "Please add some meals first!";
							} else {
								$status = 1;
								$msg = "Operation Failed!";
							}
						} else {
							$status = 2;
							$msg = "Incorrect transaction password!";
						}
						array_push( $response , array( 'status' => $status, 'msg' => $msg ) );
						if ( $status != 2 ) {
							array_push( $response , array( 'rememberPassword' => $var->type->rememberPassword ) );
						}
						echo json_encode( array( 'response' => $response ) );
						break;
				}
			}
		}
	} else {
		header('Location: index.php');
	}
	mysqli_close( $con );
?>