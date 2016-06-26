<?php
	require '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'connection.php';
	require '..'.DIRECTORY_SEPARATOR.'utility.php';
	session_start();
	session_regenerate_id(true);
	date_default_timezone_set("Asia/Calcutta");
	$escape = "SET sql_mode='NO_BACKSLASH_ESCAPES'";
	mysqli_query($con, $escape);
	if ( checkAuthToken() && checkMode( $con ) ) {
		mysqli_select_db( $con, 'admin' );
		$str = "select name, serial from messdetails where mess='".$_SESSION['database']."'";
		$query = mysqli_query( $con, $str );
		$row = mysqli_fetch_array( $query );
		$mess = $row[0];
		$serial = $row['serial'];
		$id = 0;
		$date = "";
		$meal = "";
		mysqli_select_db( $con, $_SESSION['database'] );
		$totalMembers = 0;
		$str = "select count(*) from members where status='a'";
		$query = mysqli_query( $con, $str );
		if ( $query ) {
			$totalMembers = mysqli_fetch_array( $query )[0];
		}
		$insert = "<span id='error'>Session has not been started yet.</span>";
		if ( checkSession( $con ) ) {
			mysqli_select_db( $con, $_SESSION['database'] );
			$insert = "
			<div id ='container'>
				<div id='tab_container'>
					<div class='tab selected' serial='1'>Servings</div><div class='tab border' serial='2'>Add Extras</div>
				</div>
				<div id='content_container'>
					<div class='content' serial='1'>
						<form action='' method='GET'>
						<div class='form'>
							Meal: <select name='meal'>";
			$str = "select * from meals where status='a'";
			$query = mysqli_query( $con, $str );
			if ( $query ) {
				while ( $row = mysqli_fetch_array( $query ) ) {
					$insert .= "<option value='".$row['id']."'>".$row['name']."</option>";
				}
			}
			$insert .= "</select>
					Date: <input type='text' class='date' name='date' required/>
					<button>Go</button>
					</div></form>
			";
			if ( isset($_GET['date']) && isset($_GET['meal']) && validateDate($_GET['date']) ) {
				$id = intval($_GET['meal']);
				$date = (new DateTime($_GET['date']))->getTimestamp();
				$str = "select name from meals where id=".($id);
				$meal = mysqli_fetch_array( mysqli_query( $con, $str ) )[0];
			} else {
				$str = "select m.id, m.name from meals m where m.start order by (m.start > '".date('H:i:s')."') desc, start asc limit 1";
				$row = mysqli_fetch_array( mysqli_query( $con, $str ) );
				$id = $row[0];
				$meal = $row['name'];
				$str = "select case when max(start)<'".date('H:i:s')."' then 1 else 0 end 'advance' from meals where status='a'";
				$date = mktime(0,0,0, date('m'), date('d')+mysqli_fetch_array( mysqli_query( $con, $str ) )[0], date('Y'));
				
			}
			$insert .= "<div id='servings_count'>";
			$str = "select count(*) from offs where meal=".$id." and date='".date('Y-m-d', $date)."'";
			$query = mysqli_query( $con, $str );
			if ( $query ) {
				$offCount = mysqli_fetch_array( $query )[0];
				$insert .= $meal." (".date('d F Y, D', $date)."): ".( $totalMembers - $offCount )." Serving(s), ".$offCount." Off(s)";
			}
			$insert .= "</div>";
			$str = "select * from offs natural join users where offs.meal=".$id." and offs.date='".date('Y-m-d', $date)."'";
			$query = mysqli_query( $con, $str );
			if ( $query ) {
				while ( $row = mysqli_fetch_array( $query ) ) {
					$insert .= "<div class='users'><div><img src='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."hostels".DIRECTORY_SEPARATOR."hostel".$serial.DIRECTORY_SEPARATOR.$row['image']."' width='95px' height='115px' /></div><div><span>".$row['name']."</span><br /><span> Room: ".($row['room']>0?$row['room']:'non-hostler')."</span></div></div>";
				}
			}
			$insert .= "
					</div>
					<div class='content' serial='2'>
						<div class='form'>Select Extra: &nbsp<select id='meals'>
					";
			$str = "select * from extras where status='a'";
			$query = mysqli_query( $con, $str );
			if ( $query ) {
				while ( $row = mysqli_fetch_array( $query ) ) {
					$insert .= "<option value='".$row['id'].":e' >".$row['name']."</option>";
				}
			}
			$str = "select * from meals where status='a'";
			$query = mysqli_query( $con, $str );
			if ( $query ) {
				while ( $row = mysqli_fetch_array( $query ) ) {
					$insert .= "<option value='".$row['id'].":m' >".$row['name']."</option>";
				}
			}
					$insert .= "</select>&nbsp
						Date: <input type='text' class='date' id='extra_date' />
						<button id='add'>Add</button>
					</div>";
			$str = "select * from users natural join members where status='a'";
			$query = mysqli_query( $con, $str );
			if ( $query ) {
				while ( $row = mysqli_fetch_array( $query ) ) {
					$insert .= "<div class='users extra' onclick='processList(this)' serial='".$row['id']."' ><div><img src='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."hostels".DIRECTORY_SEPARATOR."hostel".$serial.DIRECTORY_SEPARATOR.$row['image']."' width='95px' height='115px' /></div><div class='tick'>&#10004</div><div><span>".$row['name']."</span><br /><span> Room: ".($row['room']>0?$row['room']:'non-hostler')."</span><br /><span>ID: ".$row['id']."</span><a href='#'>View Extras</a></div></div>";
				}
			}
				$insert .= "
				</div>
			</div>
			</div>
			";
		}
		echo "
		<!DOCTYPE html>
		<html>
		<head>
			<title>Servings | ".$mess."</title>
			<link rel='stylesheet' type='text/css' href='..".DIRECTORY_SEPARATOR."root.css' />
			<link rel='stylesheet' type='text/css' href='style.css' />
			<link rel='stylesheet' type='text/css' href='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR."dateAndAuto".DIRECTORY_SEPARATOR."jquery-ui.min.css' />
			<link rel='stylesheet' type='text/css' href='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR."dateAndAuto".DIRECTORY_SEPARATOR."jquery-ui.theme.min.css' />
			<link rel='stylesheet' type='text/css' href='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR."dateAndAuto".DIRECTORY_SEPARATOR."jquery-ui.structure.min.css' />
			<script type='text/javascript' src='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."jquery.js' defer></script>
			<script type='text/javascript' src='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR."dateAndAuto".DIRECTORY_SEPARATOR."jquery-ui.min.js' defer></script>
			<script type='text/javascript' src='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."utility.js' defer></script>
			<script type='text/javascript' src='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."cookie.js' defer></script>
			<script type='text/javascript' src='..".DIRECTORY_SEPARATOR."utility.js' defer></script>
			<script type='text/javascript' src='script.js' defer></script>
		</head>
		<body>
			<div id='scroll'>&#10162</div>
			<div id='load-window'><div id='floating-bar'></div></div>
			<div id='dialog-overlay'>
				<div id='details'><span id='load'>Loading...</span></div>
				<div id='dialog-box'>
					<div id='dialog-head'>Alert!</div>
					<div id='dialog-content'></div>
					<div id='dialog-foot'><input type='button' id='yes' value='Yes'/><input type='button' id='no' value='No'></div>
				</div>
				<div id='transaction-box'>
					<div id='transaction-head'>Enter Transaction Password</div>
					<div id='transaction-content'><input class='prompt-input' id='password' type='password' maxlength='20' /></div>
					<div id='transaction-foot'><p><input type='checkbox' id='check' />Remember password for this session.</p><p><button class='submit' id='submit'>Submit</button></p></div>
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
				<a href='' class='slide'>Servings</a>
				<a href='..".DIRECTORY_SEPARATOR."tuning' class='slide'>Tuning</a>
				<a href='..".DIRECTORY_SEPARATOR."notice' class='slide'>Notice</a>
				<a href='..".DIRECTORY_SEPARATOR."balance' class='slide'>Balance</a>
			</nav>
			<div id='middle'>".$insert."
			</div>
		</body>
		</html>
		";
	} else {
		header('Location: ..'.DIRECTORY_SEPARATOR);
	}
	mysqli_close( $con );
?>