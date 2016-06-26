<?php
	require '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'connection.php';
	require '..'.DIRECTORY_SEPARATOR.'utility.php';
	require 'utility.php';
	session_start();
	session_regenerate_id(true);
	if ( checkAuthToken() && checkMode( $con ) ) {
		mysqli_select_db( $con, 'admin' );
		$str = "select name from messdetails where mess='".$_SESSION['database']."'";
		$query = mysqli_query( $con, $str );
		$row = mysqli_fetch_array( $query );
		$mess = $row[0];
		echo "
		<!DOCTYPE html>
		<html>
		<head>
			<title>Tuning | ".$mess."</title>
			<link rel='stylesheet' type='text/css' href='..".DIRECTORY_SEPARATOR."root.css' />
			<link rel='stylesheet' type='text/css' href='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR."dateAndAuto".DIRECTORY_SEPARATOR."jquery-ui.min.css' />
			<link rel='stylesheet' type='text/css' href='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR."dateAndAuto".DIRECTORY_SEPARATOR."jquery-ui.theme.min.css' />
			<link rel='stylesheet' type='text/css' href='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR."dateAndAuto".DIRECTORY_SEPARATOR."jquery-ui.structure.min.css' />
			<script type='text/javascript' src='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."jquery.js' defer></script>
			<script type='text/javascript' src='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR."dateAndAuto".DIRECTORY_SEPARATOR."jquery-ui.min.js' defer></script>
			<link rel='stylesheet' type='text/css' href='style.css' />
			<script type='text/javascript' src='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."utility.js' defer></script>
			<script type='text/javascript' src='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."cookie.js' defer></script>
			<script type='text/javascript' src='..".DIRECTORY_SEPARATOR."utility.js' defer></script>
			<script type='text/javascript' src='script.js' defer></script>
		</head>
		<body>
			<div id='menu_wrap'>
				<h2>Design Menu</h2>
				<div id='close'>+</div>
				<div id='menu'></div>
			</div>
			<div id='meal_feed'></div>
			<div id='extra_feed'></div>
			<div id='extra_edit'>
				<div id='extra_edit_name'></div>
				<input type='number' id='extra_edit_cost' placeholder='Cost' min='0'  />
				<button class='button' id='extra_button'>Save</button>
			</div>
			<div id='off_edit'>
				<div value = '1' class='days'>Sunday</div>
				<div value = '2' class='days'>Monday</div>
				<div value = '3' class='days'>Tuesday</div>
				<div value = '4' class='days'>Wednesday</div>
				<div value = '5' class='days'>Thursday</div>
				<div value = '6' class='days'>Friday</div>
				<div value = '7' class='days'>Saturday</div>
				<button class='button' id='off_button'>Save</button>
			</div>
			<div id='meal_edit'>
				<div id='meal_edit_name'></div>
				<input type='text' id='meal_edit_start' placeholder='Start time' /><span id='meal_edit_start_feed' class='time'>AM</span>
				<input type='text' id='meal_edit_end' placeholder='End time' /><span id='meal_edit_end_feed' class='time'>AM</span>
				<input type='number' id='meal_edit_points' placeholder='Points' max='99' min='0' />
				<input type='number' id='meal_edit_cost' placeholder='Cost' min='0' />
				<button class='button' id='meal_button'>Save</button>
			</div>
			<div id='scroll'>&#10162</div>
			<div id='load-window'><div id='floating-bar'></div></div>
			<div id='dialog-overlay'>
				<div id='scheduled_off_display'><span id='load'>Loading...</span></div>
				<div id='transaction-box'>
					<div id='transaction-head'>Enter Transaction Password</div>
					<div id='transaction-content'><input class='prompt-input' id='password' type='password' maxlength='20' /></div>
					<div id='transaction-foot'><p><input type='checkbox' id='check' />Remember password for this session.</p><p><button class='submit' id='submit'>Submit</button></p></div>
				</div>
				<div id='dialog-box'>
					<div id='dialog-head'>Alert!</div>
					<div id='dialog-content'></div>
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
				<a href='' class='slide'>Tuning</a>
				<a href='..".DIRECTORY_SEPARATOR."notice' class='slide'>Notice</a>
				<a href='..".DIRECTORY_SEPARATOR."balance' class='slide'>Balance</a>
			</nav>
			<div id='middle'>
				<div id='container'>
					".getContent($con)."
				</div>
			</div>
		</body>
		</html>";
	} else {
		header('Location: ..'.DIRECTORY_SEPARATOR);
	}
	mysqli_close ( $con );
?>