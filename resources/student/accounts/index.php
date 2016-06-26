<?php
	require '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'connection.php';
	require '..'.DIRECTORY_SEPARATOR.'utility.php';
	session_start();
	session_regenerate_id(true);
	if ( checkAuthToken() && checkMode( $con ) ) {
		mysqli_select_db( $con, 'admin' );
		$str = "select name, serial from messdetails where mess='".$_SESSION['database']."'";
		$query = mysqli_query( $con, $str );
		$row = mysqli_fetch_array( $query );
		$mess = $row[0];
		$serial = $row[1];
		mysqli_select_db( $con, $_SESSION['database'] );
		$str = "select * from users natural join members where id=".$_SESSION['id'];
		$query = mysqli_query( $con, $str );
		$data = array( 'avail' => false );
		if ( $query && mysqli_num_rows( $query ) == 1 ) {
			$row = mysqli_fetch_array( $query );
			$data['avail'] = true;
			$data['name'] = $row['name'];
			$data['age'] = (new DateTime('today'))->diff(new DateTime($row['dob']))->y;
			if ( $row['gender'] == 'm' )
				$data['gender'] = "Male";
			elseif ( $row['gender'] == 'f' )
				$data['gender'] = "Female";
			$data['email'] = $row['email'];
			if ( $row['room'] == 0 )
				$data['room'] = "Non-Hostler";
			else
				$data['room'] = $row['room'];
			$data['id'] = $row['id'];
			$data['roll'] = $row['roll'];
			if ( $row['status'] == 'a' )
				$data['status'] = "Active";
			else if ( $row['status'] == 'd' )
				$data['status'] = "Deactive";
			elseif ( $row['status'] == 'c' )
				$data['status'] = "Closed";
			$data['image'] = $row['image'];

		}
		$insert = "";
		if ( $data['avail'] ) {
			$insert = "
			<div id='container'>
				<div id='left_container'>
					<table cellspacing='10px' cellpadding='8px'>
						<tr>
							<td>Name:</td>
							<td>".$data['name']."</td>
						</tr>
						<tr>
							<td>Age:</td>
							<td>".$data['age']." Years</td>
						</tr>
						<tr>
							<td>Gender:</td>
							<td>".$data['gender']."</td>
						</tr>
						<tr>
							<td>Email:</td>
							<td>".$data['email']."</td>
						</tr>
						<tr>
							<td>Room:</td>
							<td>".$data['room']."</td>
						</tr>
						<tr>
							<td>Roll No:</td>
							<td>".$data['roll']."</td>
						</tr>
						<tr>
							<td>ID:</td>
							<td>".$data['id']."</td>
						</tr>
						<tr>
							<td>Status:</td>
							<td>".$data['status']."</td>
						</tr>
					</table>
				</div>
				<div id='right_container'>
					<img src='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."hostels".DIRECTORY_SEPARATOR."hostel".$serial.DIRECTORY_SEPARATOR.$data['image']."' width='200' />
				</div>
			</div>";
		} else
			$insert = "No data found!";
		$data['count'] = getNotifsCount($con);
		$data['count'] = $data['count']>0?"(".$data['count'].")":"";
		echo "
		<!DOCTYPE html>
		<html>
		<head>
			<title>Account | ".$_SESSION['name']."</title>
			<link rel='stylesheet' type='text/css' href='..".DIRECTORY_SEPARATOR."root.css' />
			<link rel='stylesheet' type='text/css' href='style.css' />
		</head>
		<body>
			<div id='scroll'>&#10162</div>
			<div id='header'>
				<h1>IET Mess Online</h1><span id='tagline'><i>one step towards transparency...</i></span><br /><span>".$mess."</span>
				<a id='logout' href='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."logout.php'>Logout</a>
			</div>
			<nav>
				<a href=''>My Account</a>
				<a href='..".DIRECTORY_SEPARATOR."expense' class='slide'>Expenses</a>
				<a href='..".DIRECTORY_SEPARATOR."servings' class='slide'>Servings</a>
				<a href='..".DIRECTORY_SEPARATOR."tuning' class='slide'>Tuning</a>
				<a href='..".DIRECTORY_SEPARATOR."notifications' class='slide'>Notifications".$data['count']."</a>
				<a href='..".DIRECTORY_SEPARATOR."balance' class='slide'>Balance</a>
			</nav>
			<div id='middle'>".$insert."</div>
		";
	} else {
		header('Location: ..'.DIRECTORY_SEPARATOR);
	}
	mysqli_close( $con );
?>