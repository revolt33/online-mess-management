<?php
	require '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'connection.php';
	session_start();
	session_regenerate_id(true);
	$escape = "SET sql_mode='NO_BACKSLASH_ESCAPES'";
	mysqli_query($con, $escape);
	if ( !empty($_SESSION['id']) && !empty($_SESSION['type']) && !empty($_SESSION['name']) && !empty($_SESSION['database']) ) {
		if ( $_POST ){
			if ( isset($_POST['data']) ) {
				$data = $_POST['data'];
				$var = json_decode($data);
				$type = htmlentities( $var->type->name );
				if ( strcmp($type, 'edit') == 0 ) {
					mysqli_select_db( $con, 'admin' );
					$serial = htmlentities( $var->values->serial );
					$serial = intval( $serial );
					$name = trim(htmlentities( $var->values->name ));
					$detail = trim(htmlentities( $var->values->detail ));
					$password = trim(htmlentities( $var->values->password ));
					$mode = trim(htmlentities( $var->values->status ));
					$response = array();
					$status = 3;
					
					$msg = "";
					if ( ( strlen($name) > 0 ) && ( strlen($name) < 51 ) && ( strlen($detail) > 0 ) && ( strlen($detail) < 201 ) && ( strlen($password) > 0 ) && ( strlen($password) < 21 ) ) {
						$password = encryptPassword( $serial, $password );
						$str = "update messdetails set name='".$name."', detail='".$detail."', password='".$password."', mode='".$mode."' where serial=".$serial;
						$query = mysqli_query( $con, $str );
						if ( $query ) {
							$status = 0;
							$msg = "Update successful.";
						} else {
							$status = 1;
							$msg = "Update failed.";
						}
					} else{
						$status = 2;
						$msg = "Inappropriate input provided.";
					}
					array_push($response, array( 'status' => $status, 'msg' => $msg ));
					echo json_encode(array( 'response' => $response ));
				} elseif ( strcmp($type, 'delete') == 0 ) {
					mysqli_select_db( $con, 'admin' );
					$serial = trim(htmlentities( $var->values->serial ));
					$serial = intval( $serial );
					$response = array();
					$status = 3;
					$msg = "";
					$removestr = "select * from messdetails where serial=".$serial;
					$removeQuery = mysqli_query( $con, $removestr );
					if ( $removeQuery ) {
						if ( mysqli_num_rows( $removeQuery ) > 0 ) {
							$row = mysqli_fetch_array( $removeQuery );
							$name = $row['image'];
							$image = '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'messImages'.DIRECTORY_SEPARATOR.$name;
							$thumb = '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'messImages'.DIRECTORY_SEPARATOR.'thumbnails'.DIRECTORY_SEPARATOR.$name;
							unlink( $image );
							unlink( $thumb );
							$dropstr = "drop database ".$row['mess'];
							mysqli_query($con, $dropstr);
							$path = "..".DIRECTORY_SEPARATOR."hostels".DIRECTORY_SEPARATOR."hostel".$serial;
							rrmdir($path);
						}
						$str = "delete from users where serial=".$serial;
						mysqli_query( $con, $str );
						$str = "delete from messdetails where serial=".$serial;
						$query = mysqli_query( $con, $str );
						if ( $query ) {
							$status = 0;
							$msg = "Mess deleted successfully.";
						} else {
							$status = 1;
							$msg = "Error removing mess.";
						}
					} else {
						$status = 2;
						$msg = "Error removing mess.";
					}

					array_push($response, array( 'status' => $status, 'msg' => $msg ));
					echo json_encode(array( 'response' => $response ));
				} elseif (strcmp($type, 'add') == 0) {
					mysqli_select_db( $con, 'admin' );
					$name = trim(htmlentities( $var->values->name ));
					$detail = trim(htmlentities( $var->values->detail ));
					$password = trim(htmlentities( $var->values->password ));
					$status = 3;
					$msg = "";
					$serial = -1;
					$response = array();
					if ( ( strlen($name) > 0 ) && ( strlen($name) < 51 ) && ( strlen($detail) > 0 ) && ( strlen($detail) < 201 ) && ( strlen($password) > 0 ) && ( strlen($password) < 21 ) ) {
						$str = "select max(serial) as maxcount from messdetails";
						$query = mysqli_query( $con, $str );
						$row = mysqli_fetch_array( $query );
						$max = $row['maxcount'];
						$serial = $max + 1;
						$password = encryptPassword( $serial, $password );
						$str = "insert into messdetails (serial, name, password, detail, mess, image, status) values (".$serial.", '".$name."', '".$password."', '".$detail."', 'mess".$serial."', 'hostel".$serial.".jpg', 2)";
						$query = mysqli_query( $con, $str );
						$str = "create database mess".$serial;
						$query = mysqli_query( $con, $str );
						mysqli_select_db( $con, 'mess'.$serial );
						$str = "create table users (id int(6) NOT NULL UNIQUE PRIMARY KEY, password varchar(60) NOT NULL, name varchar(50) NOT NULL, image varchar(50), email varchar(100) NOT NULL, fpactive varchar(1), gender varchar(1), dob date,roll varchar(20), room int(5), mobile varchar(12), remember varchar(1), upto date)";
						$query = mysqli_query( $con, $str );
						$str = "create table members (id int(6) NOT NULL UNIQUE PRIMARY KEY, opening date, closing date, status varchar(1), subsidized varchar(1), current decimal(7,2), total decimal(7,2))";
						$query = mysqli_query( $con, $str );
						$str = "create table meals (id int(3) NOT NULL UNIQUE PRIMARY KEY AUTO_INCREMENT, name varchar(20) NOT NULL, start time, end time, status varchar(1), points int(2), cost decimal(5,2))";
						$query = mysqli_query( $con, $str );
						$str = "create table extras (id int(3) NOT NULL UNIQUE PRIMARY KEY AUTO_INCREMENT, name varchar(20) NOT NULL, status varchar(1), cost decimal(5,2))";
						$query = mysqli_query( $con, $str );
						$str = "create table expense (id int(3) NOT NULL AUTO_INCREMENT UNIQUE PRIMARY KEY, start date, end date, status varchar(1), initial int(10), last int(10), current int(10), cost decimal(10,2))";
						$query = mysqli_query( $con, $str );
						$str = "create table subExpense (id int(3) NOT NULL, type varchar(1), cost decimal(10,2), entries int(6), PRIMARY KEY (id,type))";
						$query = mysqli_query( $con, $str );
						$str = "create table employee ( id int(3) AUTO_INCREMENT NOT NULL UNIQUE PRIMARY KEY, name varchar(50) NOT NULL, post varchar(30), salary int(6), status varchar(1) )";
						$query = mysqli_query( $con, $str );
						$str = "create table coupons ( id int(3) NOT NULL UNIQUE PRIMARY KEY, name varchar(30), cost int(3), description varchar(50), status varchar(1) )";
						$query = mysqli_query( $con, $str );
						$str = "create table couponSale ( id int(6) NOT NULL UNIQUE PRIMARY KEY, type int(3), issued varchar(1), status varchar(1) )";
						$query = mysqli_query( $con, $str );
						$str = "create table weeklyOff ( meal int(3), day int(1), start date, end date, status varchar(1), FOREIGN KEY(meal) REFERENCES meals(id) )";
						$query = mysqli_query( $con, $str );
						$str = "create table scheduledOff ( start_meal int(3), start_day date, end_meal int(3), end_day date, FOREIGN KEY(start_meal) REFERENCES meals(id), FOREIGN KEY(end_meal) REFERENCES meals(id) )";
						$query = mysqli_query( $con, $str );
						$str = "create table menu ( meal int(3), day1 varchar(70), day2 varchar(70), day3 varchar(70), day4 varchar(70), day5 varchar(70), day6 varchar(70), day7 varchar(70) )";
						$query = mysqli_query( $con, $str );
						$str = "create table notice ( id int(5) NOT NULL UNIQUE PRIMARY KEY, date date, heading varchar(50), text varchar(5000), severity varchar(1), image varchar(30) )";
						$query = mysqli_query( $con, $str );
						$str = "create table bills ( id int(3) NOT NULL UNIQUE PRIMARY KEY, date date, amount decimal(9,2), exp_id int(3), members int(4), base_h decimal(7,2), base_all decimal(7,2) )";
						$query = mysqli_query( $con, $str );
						$str = "create table offs ( id int(6), date date, meal int(3), status varchar(1) )";
						$query = mysqli_query( $con, $str );
						$dir = "..".DIRECTORY_SEPARATOR."hostels".DIRECTORY_SEPARATOR."hostel".$serial;
						mkdir($dir, 0755, true);
						$status = 0;
						$msg = "Mess added successfully.";
					} else {
						$status = 1;
						$msg = "Inappropriate input provided.";
					}
					array_push($response, array( 'status' => $status, 'msg' => $msg ));
					if ( $status == 0 ) {
						array_push($response, array( 'serial' => $serial ));
					}
					echo json_encode(array( 'response' => $response ));
				}
			}
		} else {
			header('Location: mess.php');
		}
	} else{
		header('Location: mess.php');
	}
	mysqli_close( $con );
	function rrmdir($dir) { 
		if (is_dir($dir)) { 
			$objects = scandir($dir); 
			foreach ($objects as $object) { 
				if ($object != "." && $object != "..") { 
					if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object); 
				} 
			} 
			reset($objects); 
			rmdir($dir); 
		} 
	}
	function encryptPassword($serial, $password) {
		$password = md5($password);
		$temp_serial = sha1($serial);
		$options = [
		    'cost' => 11,
		    'salt' => hash('sha256', $temp_serial),
		];
		$password = password_hash( $password, PASSWORD_BCRYPT, $options );
		return $password;
	}
?>