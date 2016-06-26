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
		$count = getNotifsCount($con);
		$count = $count>0?"(".$count.")":"";
		$str = "select table_1.notifs, table_2.notice from (select count(*) as notifs from notifs_".$_SESSION['id']." where type=0 and status='n') as table_1, (select count(*) as notice from notifs_".$_SESSION['id']." where type>0 and status='n') as table_2";
		$notifs = 0;
		$notice = 0;
		$query = mysqli_query( $con, $str );
		if ( $query ) {
			$row = mysqli_fetch_array( $query );
			$notifs = $row[0];
			$notice = $row[1];
		}
		$notifs = $notifs>0?"(".$notifs.")":"";
		$notice = $notice>0?"(".$notice.")":"";
		echo "
		<!DOCTYPE html>
		<html>
		<head>
			<title>Notifications | ".$_SESSION['name']."</title>
			<link rel='stylesheet' type='text/css' href='..".DIRECTORY_SEPARATOR."root.css' />
			<link rel='stylesheet' type='text/css' href='style.css' />
			<script type='text/javascript' src='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."jquery.js' defer></script>
			<script type='text/javascript' src='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."utility.js' defer></script>
			<script type='text/javascript' src='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."cookie.js' defer></script>
			<script type='text/javascript' src='..".DIRECTORY_SEPARATOR."utility.js' defer></script>
			<script type='text/javascript' src='script.js' defer></script>
		</head>
		<body>
			<div id='preview'>
				<div id='close_preview'>+</div>
				<div id='preview_content'></div>
			</div>
			<div id='overlay'>
				<div id='preview_notifs'>
					<div id='preview_notifs_head'>
					</div>
					<div id='preview_notifs_content'>
					</div>
				</div>
			</div>
			<div id='scroll'>&#10162</div>
			<div id='header'>
				<h1>IET Mess Online</h1><span id='tagline'><i>one step towards transparency...</i></span><br /><span>".$mess."</span>
				<a id='logout' href='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."logout.php'>Logout</a>
			</div>
			<nav>
				<a href='..".DIRECTORY_SEPARATOR."accounts'>My Account</a>
				<a href='..".DIRECTORY_SEPARATOR."expense' class='slide'>Expenses</a>
				<a href='..".DIRECTORY_SEPARATOR."servings' class='slide'>Servings</a>
				<a href='..".DIRECTORY_SEPARATOR."tuning' class='slide'>Tuning</a>
				<a href='' id='notifications' class='slide'>Notifications".$count."</a>
				<a href='..".DIRECTORY_SEPARATOR."balance' class='slide'>Balance</a>
			</nav>
			<div id='middle'>
				<div id='container'>
					<div id='tab_container'>
						<div class='tab selected' id='notifs' serial='1'>Notifications".$notifs."</div><div class='tab border' id='notice' serial='2'>Notice".$notice."</div>
					</div>
					<div id='content_container'>
						<div class='content' serial='1'>
						";
						$str = "select * from notifs_".$_SESSION['id']." where type=0 order by date DESC";
						$query = mysqli_query( $con, $str );
						if ( $query ) {
							while ( $row = mysqli_fetch_array( $query ) ) {
								echo "
								<div class='notifs_wrap ".($row['status']=='y'?'old':'new')."' serial='".$row['serial']."'>
									<div class='notifs_view'>View Notification</div>
									<div class='notifs_head'>".$row['title']."</div>
									<div class='notifs_date'>".date('l, F d Y', strtotime($row['date']))."</div>
									<div class='notifs_content'>".$row['content']."</div>
								</div>";
							}
						}
						echo "
						</div>
						<div class='content' serial='2'>";
						$str = "select heading, severity, image, notice.date, text, serial, status from notice join notifs_".$_SESSION['id']." on type=id order by date DESC";
						$query = mysqli_query( $con, $str );
						if ( $query ) {
							while ( $row = mysqli_fetch_array( $query ) ) {
								$urgency = " &nbsp(urgency: ";
								if ( $row['severity'] == 'l' )
									$urgency .= "low)";
								elseif ( $row['severity'] == 'h' ) {
									$urgency .= "high)";
								}
								$image_name = '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'hostels'.DIRECTORY_SEPARATOR."hostel".$serial.DIRECTORY_SEPARATOR.$row['image'];
								echo "
								<div class='notice_wrap ".($row['status']=='y'?'old':'new')."' imgpath='".$image_name."' serial='".$row['serial']."'>
									<div class='notice_view'>View Notice</div>
									<div class='notice_head'>".$row['heading'].$urgency."</div>
									<div class='notice_date'>".date('l, F d Y', strtotime($row['date']))."</div>
									<div class='notice_content'>".$row['text']."</div>
								</div>";
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