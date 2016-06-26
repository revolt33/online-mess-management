<?php
	require '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'connection.php';
	require '..'.DIRECTORY_SEPARATOR.'utility.php';
	session_start();
	date_default_timezone_set("Asia/Calcutta");
	session_regenerate_id(true);
	if ( checkAuthToken() && checkMode( $con ) ) {
		mysqli_select_db( $con, 'admin' );
		$str = "select name, serial from messdetails where mess='".$_SESSION['database']."'";
		$query = mysqli_query( $con, $str );
		$row = mysqli_fetch_array( $query );
		$mess = $row[0];
		$serial = $row['serial'];
		mysqli_select_db( $con, $_SESSION['database'] );
		$str = "select * from notice order by date DESC";
		$query = mysqli_query( $con, $str );
		echo "
		<!DOCTYPE html>
		<html>
		<head>
			<title>Notice | ".$mess."</title>
			<link rel='stylesheet' type='text/css' href='..".DIRECTORY_SEPARATOR."root.css' />
			<link rel='stylesheet' type='text/css' href='style.css' />
			<script type='text/javascript' src='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."jquery.js' defer></script>
			<script type='text/javascript' src='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."utility.js' defer></script>
			<script type='text/javascript' src='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."cookie.js' defer></script>
			<script type='text/javascript' src='..".DIRECTORY_SEPARATOR."utility.js' defer></script>
			<script type='text/javascript' src='".DIRECTORY_SEPARATOR."ckeditor".DIRECTORY_SEPARATOR."ckeditor.js'></script>
			<script type='text/javascript' src='script.js' defer></script>
		</head>
		<body>
			<div id='scroll'>&#10162</div>
			<div id='preview'>
				<div id='close_preview'>+</div>
				<div id='preview_content'></div>
			</div>
			<div id='load-window'><div id='floating-bar'></div></div>
			<div id='dialog-overlay'>
				<div id='transaction-box'>
					<div id='transaction-head'>Enter Transaction Password</div>
					<div id='transaction-content'><input class='prompt-input' id='password' type='password' maxlength='20' /></div>
					<div id='transaction-foot'><p><input type='checkbox' id='check' />Remember password for this session.</p><p><button class='submit' id='submit'>Submit</button></p></div>
				</div>
				<div id='dialog-box'>
					<div id='dialog-head'>Alert!</div>
					<div id='dialog-content'>Do you want to add the notice?</div>
					<div id='dialog-foot'><button id='yes'>Yes</button>&nbsp&nbsp&nbsp<button id='no'>No</button></div>
				</div>
			</div>
			<div id='disable'></div>
			<div id='feedback'></div>
			<div id='header'>
				<h1>IET Mess Online</h1><span id='tagline'><i>one step towards transparency...</i></span><br /><span>".$mess."</span>
				<a id='logout' href='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."logout.php'>Logout</a>
			</div>
			<nav>
				<a href='..".DIRECTORY_SEPARATOR."accounts'>Accounts</a>
				<a href='..".DIRECTORY_SEPARATOR."expense' class='slide'>Expenses</a>
				<a href='..".DIRECTORY_SEPARATOR."servings' class='slide'>Servings</a>
				<a href='..".DIRECTORY_SEPARATOR."tuning' class='slide'>Tuning</a>
				<a href='' class='slide'>Notice</a>
				<a href='..".DIRECTORY_SEPARATOR."balance' class='slide'>Balance</a>
			</nav>
			<div id='middle'>
				<div id='container'>
					<div id='tab_container'>
						<div class='tab selected' serial='1'>Previous Notice</div><div class='tab border' serial='2'>Write a Notice</div>
					</div>
					<div id='content_container'>
						<div class='content' serial='1'>";
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
								<div class='notice_wrap' imgpath='".$image_name."'>
									<div class='notice_view'>View Notice</div>
									<div class='notice_head'>".$row['heading'].$urgency."</div>
									<div class='notice_date'>".date('l, F d Y', strtotime($row['date']))."</div>
									<div class='notice_content'>".$row['text']."</div>
								</div>";
							}
						}
						echo "
						</div>
						<div class='content' serial='2'>
							<div id='notice_top' >
								<h2>Heading: </h2><input type='text' id='heading' maxlength='50'/>
								<img title='Attach an image.' src='attach_image.jpg' width='50' height='50' />
								<input type='file' id='image' />
								<span id='image_feed' >(optional)</span><a href='#' id='get-preview'>preview</a>
								<p>Urgency: <input type='radio' name='urgency' value='l' checked/>Low &nbsp&nbsp&nbsp&nbsp <input type='radio' name='urgency' value='h' />high</p>
							</div>
							<div id='notice_middle'>
								<textarea id='notice' rows='10' cols='80'></textarea>
							</div>
							<div id='notice_bottom'>
								<button id='notice_button'>Submit</button>
							</div>
						</div>
					</div>
				</div>
			</div>
			<script type='text/javascript'>
				CKEDITOR.replace(\"notice\");
			</script>
		</body>
		";
	} else {
		header('Location: ..'.DIRECTORY_SEPARATOR);
	}
	mysqli_close( $con );
?>