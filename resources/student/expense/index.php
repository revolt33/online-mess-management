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
		$str = "select id, name from users where serial=".$serial;
		$query = mysqli_query( $con, $str );
		$users = array();
		while ( $row = mysqli_fetch_array( $query ) )
			$users[$row['id']] = $row['name'];
		$count = getNotifsCount($con);
		$count = $count>0?"(".$count.")":"";
		echo "
		<!DOCTYPE html>
		<html>
		<head>
			<title>Expenses | ".$_SESSION['name']."</title>
			<link rel='stylesheet' type='text/css' href='..".DIRECTORY_SEPARATOR."root.css' />
			<link rel='stylesheet' type='text/css' href='style.css' />
			<script type='text/javascript' src='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."jquery.js' defer></script>
			<script type='text/javascript' src='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."utility.js' defer></script>
			<script type='text/javascript' src='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."cookie.js' defer></script>
			<script type='text/javascript' src='..".DIRECTORY_SEPARATOR."utility.js' defer></script>
			<script type='text/javascript' src='script.js' defer></script>
		</head>
		<body>
			<div id='scroll'>&#10162</div>
			<div id='load-window'><div id='floating-bar'></div></div>
			<div id='image-viewer'><img /><div id='close-button'>+</div></div>
			<div id='hovertext'></div>
			<div id='feedback'></div>
			<div id='header'>
				<h1>IET Mess Online</h1><span id='tagline'><i>one step towards transparency...</i></span><br /><span>".$mess."</span>
				<a id='logout' href='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."logout.php'>Logout</a>
			</div>
			<nav>
				<a href='..".DIRECTORY_SEPARATOR."accounts'>My Account</a>
				<a href='' class='slide'>Expenses</a>
				<a href='..".DIRECTORY_SEPARATOR."servings' class='slide'>Servings</a>
				<a href='..".DIRECTORY_SEPARATOR."tuning' class='slide'>Tuning</a>
				<a href='..".DIRECTORY_SEPARATOR."notifications' class='slide'>Notifications".$count."</a>
				<a href='..".DIRECTORY_SEPARATOR."balance' class='slide'>Balance</a>
			</nav>
			<div id='middle'>
				<div class='root_container'>
					<div class='tab_container'>
						<div class='tab selected' serial='11'>Expenses</div>
						<div class='tab border' serial='12'>Extras</div>
					</div>
					<div class='content_container'>
						<div class='content' serial='11'>
							<div class='expense_container'>
								<div class='tab_container'>
									<div class='tab selected' serial='21'>Wage</div>
									<div class='tab border' serial='22'>Expenses</div>
									<div class='tab border' serial='23'>Miscellaneous</div>
								</div>
								<div class='content_container'>
									<div class='content' serial='21'>
										<div class='row bold'>
											<div class='column'>Employee</div>
											<div class='column'>Amount</div>
											<div class='column'>Date</div>
											<div class='column'>Added By</div>
											<div class='column'>Applied To</div>
										</div>";
										$str = "select id from expense where status='a'";
										$query = mysqli_query( $con, $str );
										$status = false;
										$current = 0;
										if ( $query && mysqli_num_rows( $query ) > 0 ) {
											$row = mysqli_fetch_array( $query );
											$current = $row[0];
											$status = true;
										}
										if ( $status ) {
											$str = "select name, comment, date, amount, uid, all_users from wage_".$current.", employee where eid=id order by date DESC";
											$query = mysqli_query( $con, $str );
											while ( $row = mysqli_fetch_array( $query ) ) {
												echo "
										<div class='row'>
											<div class='hover' hovertext='".$row['comment']."'></div>
											<div class='column'>".$row['name']."</div>
											<div class='column'>".$row['amount']."</div>
											<div class='column'>".date('d F Y', strtotime($row['date']))."</div>
											<div class='column'>".$users[$row['uid']]."</div>
											<div class='column'>"; if ($row['all_users'] == 'y') echo "Everyone"; else echo "Hostlers"; echo "</div>
										</div>
										";
											}
										}
									echo "
									</div>
									<div class='content' serial='22'>
										<div class='row bold'>
											<div class='column'>Title</div>
											<div class='column'>Amount</div>
											<div class='column'>Date</div>
											<div class='column'>Added By</div>
											<div class='column'>Applied To</div>
										</div>";
										if ( $status ) {
											$str = "select title, image, amount, date, uid, all_users from exp_".$current." order by date DESC";
											$query = mysqli_query( $con, $str );
											while ( $row = mysqli_fetch_array( $query ) ) {
												echo "
										<div class='row'>
											<div class='hover image' location='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."hostels".DIRECTORY_SEPARATOR."hostel".$serial.DIRECTORY_SEPARATOR.$row['image']."' hovertext='Click to see image.'></div>
											<div class='column'>".$row['title']."</div>
											<div class='column'>".$row['amount']."</div>
											<div class='column'>".date('d F Y', strtotime($row['date']))."</div>
											<div class='column'>".$users[$row['uid']]."</div>
											<div class='column'>"; if ($row['all_users'] == 'y') echo "Everyone"; else echo "Hostlers"; echo "</div>
										</div>
										";
											}
										}
									echo "
									</div>
									<div class='content' serial='23'>
										<div class='row bold'>
											<div class='column'>Title</div>
											<div class='column'>Amount</div>
											<div class='column'>Date</div>
											<div class='column'>Added By</div>
											<div class='column'>Applied To</div>
										</div>";
										if ( $status ) {
											$str = "select title, comment, amount, date, uid, all_users from misc_".$current." order by date DESC";
											$query = mysqli_query( $con, $str );
											while ( $row = mysqli_fetch_array( $query ) ) {
												echo "
										<div class='row'>
											<div class='hover' hovertext='".$row['comment']."'></div>
											<div class='column'>".$row['title']."</div>
											<div class='column'>".$row['amount']."</div>
											<div class='column'>".date('d F Y', strtotime($row['date']))."</div>
											<div class='column'>".$users[$row['uid']]."</div>
											<div class='column'>"; if ($row['all_users'] == 'y') echo "Everyone"; else echo "Hostlers"; echo "</div>
										</div>
										";
											}
										}
										echo "
									</div>
								</div>
							</div>
						</div>
						<div class='content' serial='12'>
							<div class='row bold'>
								<div class='column'>Name of extra</div>
								<div class='column'>Date</div>
								<div class='column'>Cost/Points</div>
								<div class='column'>Added By</div>
							</div>";
						$str = "select * from extras_".$_SESSION['id'];
						$query = mysqli_query( $con, $str );
						if ( $query ) {
							while ( $row = mysqli_fetch_array( $query ) ) {
								echo "
							<div class='row'>
								<div class='column'>".$row['name']."</div>
								<div class='column'>".((new DateTime($row['date']))->format('d F, Y'))."</div>
								<div class='column'>".$row['value']."</div>
								<div class='column'>".$users[$row['addedBy']]."</div>
							</div>
								";
							}
						}
							echo "
						</div>
					</div>
				</div>
			</div>
		";
	} else {
		header('Location: ..'.DIRECTORY_SEPARATOR);
	}
	mysqli_close( $con );
?>