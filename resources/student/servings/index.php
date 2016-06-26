<?php
	require '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'connection.php';
	require '..'.DIRECTORY_SEPARATOR.'utility.php';
	session_start();
	session_regenerate_id(true);
	date_default_timezone_set("Asia/Calcutta");
	$days = array( 
		1 => 'Sunday',
		2 => 'Monday',
		3 => 'Tuesday',
		4 => 'Wednesday',
		5 => 'Thursday',
		6 => 'Friday',
		7 => 'Saturday'
	);
	$dates = array();
	if ( checkAuthToken() && checkMode( $con ) ) {
		mysqli_select_db( $con, 'admin' );
		$str = "select name from messdetails where mess='".$_SESSION['database']."'";
		$query = mysqli_query( $con, $str );
		$row = mysqli_fetch_array( $query );
		$mess = $row[0];
		$count = getNotifsCount($con);
		$count = $count>0?"(".$count.")":"";
		$str = "select case status when 'a' then true else false end as 'status' from members where id=".$_SESSION['id'];
		$insert = "<span id='error'>Sorry, this account is deactivated.</span>";
		$query = mysqli_query( $con, $str );
		if ( $query && mysqli_num_rows( $query ) == 1 ) {
			if ( mysqli_fetch_array( $query )[0] ) {
				$proceed = false;
				$str = "select * from meals, menu where status='a' and meal=id order by start";
				$query = mysqli_query( $con, $str );
				$row_count = mysqli_num_rows( $query );
				$schedule = array();
				$meals = array();
				$backtrack = array();
				$all_meals = array();
				$time = date('H:i:s');
				for ($i=0 ; $i < $row_count ; $i++) { 
					array_push( $schedule, array(0,0,0,0,0,0,0) );
				}
				$menu = array();
				$start_date = date('Y-m-d');
				if ( $query && $row_count > 0 ) {
					$proceed = true;
					$counter = 0;
					$schedule[0][0] = 2;
					while ( $row = mysqli_fetch_array( $query ) ) {
						$meals[$counter] = array( 'id' => $row['id'], 'name' => $row['name'], 'points' => $row['points']);
						$backtrack[$row['id']] = $counter;
						$menu[$row['id']] = array( $row['day1'], $row['day2'], $row['day3'], $row['day4'], $row['day5'], $row['day6'], $row['day7'] );
						if ( $time > date($row['end']) ) {
							if ( $counter == ($row_count - 2) ) {
								$start_date = date('Y-m-d', mktime(0,0,0, date('m'), date('d')+1, date('Y')));
								for( $i = 0; $i < $row_count; $i++ )
									$schedule[$i][0] = 0;
							} elseif ( $counter == ( $row_count -1 ) ) {
								$start_date = date('Y-m-d', mktime(0,0,0, date('m'), date('d')+1, date('Y')));
								$schedule[0][0] = 2;
							} else {
								$schedule[$counter+1][0] = 2;
							}
						}
						$counter++;
					}
				}
				$str = "select * from meals";
				$query = mysqli_query( $con, $str );
				if ( $query ) {
					while ( $row = mysqli_fetch_array( $query ) ) {
						$all_meals[$row['id']] = array('start' => $row['start'], 'status' => $row['status'] );
					}
				}
				$begin = date('w', (new DateTime($start_date))->getTimestamp());
				$str = "select * from weeklyOff where status='a'";
				$query = mysqli_query( $con, $str );
				if ( $query ) {
					while ( $row = mysqli_fetch_array( $query ) ) {
						$col = $row['day'] - 1;
						$schedule[$backtrack[$row['meal']]][$col>=$begin?$col-$begin:((6-$begin)+$col)] = 3;
					}
				}
				$diff = ((new DateTime('today'))->diff(new DateTime($start_date))->d);
				for ($i=0; $i < 7; $i++) { 
					array_push( $dates , mktime(0,0,0, date('m'), date('d')+$diff+$i, date('Y')));
				}
				$str = "select * from scheduledOff where (start_day < cast('".date('Y-m-d', $dates[0])."' as date ) and (end_day between cast('".date('Y-m-d', $dates[0])."' as date) and cast('".date('Y-m-d', $dates[6])."' as date))) or ((start_day between cast('".date('Y-m-d', $dates[0])."' as date) and cast('".date('Y-m-d', $dates[6])."' as date)) and (end_day between cast('".date('Y-m-d', $dates[0])."' as date) and cast('".date('Y-m-d', $dates[6])."' as date))) or ((end_day > cast( '".date('Y-m-d', $dates[6])."' as date)) and (start_day between cast('".date('Y-m-d', $dates[0])."' as date) and cast('".date('Y-m-d', $dates[6])."' as date))) order by start_day";
				$query = mysqli_query( $con, $str );
				if ( $query ) {
					while ( $row = mysqli_fetch_array( $query ) ) {
						$start = date('Y-m-d', $dates[0])>date($row['start_day'])?false:true;
						$end = date('Y-m-d', $dates[6])<date($row['end_day'])?false:true;
						if ( !$start && $end ) {
							$diff = ((new DateTime(date('Y-m-d', $dates[0])))->diff(new DateTime(date($row['end_day'])))->d);
							for ($i=0; $i < $diff; $i++) { 
								for ($j=0; $j < $row_count; $j++) { 
									$schedule[$j][$i] = 4;
								}
							}
							$index = 0;
							$validMeal = getValidMeal( $con, $all_meals, $row['end_meal'], 'end' );
							if  ( $validMeal != -1 )
								while (true) {
									$schedule[$index][$diff] = 4;
									if ( $meals[$index]['id'] == $validMeal )
										break;
									$index++;
								}
						} elseif ( $start && $end ) {
							$diff = ((new DateTime(date($row['start_day'])))->diff(new DateTime(date($row['end_day'])))->d);
							$base = ((new DateTime(date('Y-m-d',$dates[0])))->diff(new DateTime(date($row['start_day'])))->d);
							if ( $diff > 0 ) {
								$validMeal = getValidMeal( $con, $all_meals, $row['start_meal'], 'beg' );
								if ( $validMeal != -1 )
									for ($i= $backtrack[$validMeal]; $i < $row_count; $i++) { 
										$schedule[$i][$base] = 4;
									}
								for ($i=($base+1); $i < ($base+$diff); $i++) { 
									for ($j=0; $j < $row_count; $j++) { 
										$schedule[$j][$i] = 4;
									}
								}
								$index = 0;
								$validMeal = getValidMeal( $con, $all_meals, $row['end_meal'], 'end' );
								if  ( $validMeal != -1 )
									while (true) {
										$schedule[$index][$base+$diff] = 4;
										if ( $meals[$index]['id'] == $validMeal )
											break;
										$index++;
									}
							} else {
								$startValidMeal = getValidMeal( $con, $all_meals, $row['start_meal'], 'beg' );
								$endValidMeal = getValidMeal( $con, $all_meals, $row['end_meal'], 'end' );
								if ( $startValidMeal != -1 && $endValidMeal != -1 )
									for ($i=$backtrack[$startValidMeal]; $i <= $backtrack[$endValidMeal]; $i++) { 
										$schedule[$i][$base] = 4;
									}
							}	
						} elseif ( $start && !$end ) {
							$diff = ((new DateTime(date($row['start_day'])))->diff(new DateTime(date('Y-m-d', $dates[6])))->d);
							$base = ((new DateTime(date('Y-m-d',$dates[0])))->diff(new DateTime(date($row['start_day'])))->d);
							$validMeal = getValidMeal( $con, $all_meals, $row['start_meal'], 'beg' );
							$index = $backtrack[$validMeal];
							if ( $validMeal != -1 )
								while (true) {
									$schedule[$index][$base] = 4;
									if ( $index == $row_count )
										break;
									$index++;
								}
							for ($i=$base+1; $i < 7; $i++) { 
								for ($j=0; $j < $row_count; $j++) { 
									$schedule[$j][$i] = 4;
								}
							}
						}
					}
				}
				$str = "select * from offs where date between cast('".date('Y-m-d', $dates[0])."' as date) and cast('".date('Y-m-d', $dates[6])."' as date) and id=".$_SESSION['id'];
				$query = mysqli_query( $con, $str );
				if ( $query ) {
					while ( $row = mysqli_fetch_array( $query ) ) {
						$base = ((new DateTime(date('Y-m-d',$dates[0])))->diff(new DateTime(date($row['date'])))->d);
						$schedule[$backtrack[$row['meal']]][$base] = $schedule[$backtrack[$row['meal']]][$base]==2?5:1;
					}
				}
				$insert = "<div class='row'><div class='column'></div>";
				for ($i=0; $i < 7; $i++) { 
					$insert .= "<div class='column bold'>".date('d F Y (D)', $dates[$i])."</div>";
				}
				$insert .= "</div>";
				for ($i=0; $i < $row_count; $i++) { 
					$insert .= "<div class='row'><div class='column bold'>".$meals[$i]['name']."<br />Points: ".$meals[$i]['points']."</div>";
					for ($j=$begin; $j < 7; $j++) { 
						$insert .= "<div class='column'>".getMenuItem( $menu, $meals, $i, $j, $schedule, $begin, $dates)."</div>";
					}
					if ( $begin != 0 ) {
						for ($k=0; $k < $begin; $k++) { 
							$insert .= "<div class='column'>".getMenuItem( $menu, $meals, $i, $k, $schedule, $begin, $dates)."</div>";
						}
					}
					$insert .= "</div>";
				}
				$insert .= "<div class='row'><div class='column'><button id='button'>Save Changes</button></div></div>";
			}
		}
		
		echo "
		<!DOCTYPE html>
		<html>
		<head>
			<title>Servings | ".$_SESSION['name']."</title>
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
			<div id='feedback'></div>
			<div id='disable'></div>
			<div id='load-window'><div id='floating-bar'></div></div>
			<div id='dialog-overlay'>
				<div id='dialog-box'>
					<div id='dialog-head'>Alert!</div>
					<div id='dialog-content'>Do you want to save changes?</div>
					<div id='dialog-foot'><input type='button' id='yes' value='Yes'/><input type='button' id='no' value='No'></div>
				</div>
			</div>
			<div id='header'>
				<h1>IET Mess Online</h1><span id='tagline'><i>one step towards transparency...</i></span><br /><span>".$mess."</span>
				<a id='logout' href='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."logout.php'>Logout</a>
			</div>
			<nav>
				<a href='..".DIRECTORY_SEPARATOR."accounts'>My Account</a>
				<a href='..".DIRECTORY_SEPARATOR."expense' class='slide'>Expenses</a>
				<a href='' class='slide'>Servings</a>
				<a href='..".DIRECTORY_SEPARATOR."tuning' class='slide'>Tuning</a>
				<a href='..".DIRECTORY_SEPARATOR."notifications' class='slide'>Notifications".$count."</a>
				<a href='..".DIRECTORY_SEPARATOR."balance' class='slide'>Balance</a>
			</nav>
			<div id='middle'>".$insert."</div>
		</body>
		</html>
		";
	} else {
		header('Location: ..'.DIRECTORY_SEPARATOR);
	}
	function getMenuItem( $menu, $meals, $row, $col, $schedule, $begin, $dates ) {
		switch ($schedule[$row][$col>=$begin?$col-$begin:((6-($begin-1))+$col)]) {
			case 0:
				return "<div class='item open' day='".date('Y-m-d', $dates[$col>=$begin?$col-$begin:((6-($begin-1))+$col)])."' meal='".$meals[$row]['id']."'><div class='off'>Turn Off</div>".($menu[$meals[$row]['id']][$col]==''?'No Item':$menu[$meals[$row]['id']][$col])."</div>";
			case 1:
				return "<div class='item open selected' day='".date('Y-m-d', $dates[$col>=$begin?$col-$begin:((6-($begin-1))+$col)])."' meal='".$meals[$row]['id']."'><div class='off select'>Remove Off</div>".($menu[$meals[$row]['id']][$col]==''?'No Item':$menu[$meals[$row]['id']][$col])."</div>";
			case 2:
				return "<div class='pass'></div><div class='item'>".($menu[$meals[$row]['id']][$col]==''?'No Item':$menu[$meals[$row]['id']][$col])."</div>";
			case 3:
				return "<div class='weekly_off'>Weekly Off</div><div class='item'>".($menu[$meals[$row]['id']][$col]==''?'No Item':$menu[$meals[$row]['id']][$col])."</div>";;
			case 4:
				return "<div class='scheduled_off'>Scheduled Off</div><div class='item'>".($menu[$meals[$row]['id']][$col]==''?'No Item':$menu[$meals[$row]['id']][$col])."</div>";
			case 5:
				return "<div class='pass'></div><div class='item green'>".($menu[$meals[$row]['id']][$col]==''?'No Item':$menu[$meals[$row]['id']][$col])."</div>";
		}
		return "Error";
	}
	function getValidMeal( $con, $all_meals, $id, $mode ) {
		switch ($mode) {
			case 'beg':
				if ( $all_meals[$id]['status'] == 'a' ) {
					return $id;
				} else if ( $all_meals[$id]['status'] == 'r' ) {
					$str = "select id from meals where id not in (select distinct big.id from meals as small join meals as big on big.start>small.start where big.start>cast('".$all_meals[$id]['start']."' as time) and small.start>cast('".$all_meals[$id]['start']."' as time)) and start>cast('".$all_meals[$id]['start']."' as time) and status='a'";
					$query = mysqli_query( $con, $str );
					if ( $query && mysqli_num_rows( $query ) == 1 ) {
						return mysqli_fetch_array( $query )['id'];
					} else {
						return -1;
					}
				}
				break;
			case 'end':
				if ( $all_meals[$id]['status'] == 'a' ) {
					return $id;
				} else if ( $all_meals[$id]['status'] == 'r' ) {
					$str = "select id from meals where id not in (select distinct small.id from meals as small join meals as big on big.start>small.start where small.start<cast('".$all_meals[$id]['start']."' as time) and big.start<cast('".$all_meals[$id]['start']."' as time)) and start<cast('".$all_meals[$id]['start']."' as time) and status='a'";
					$query = mysqli_query( $con, $str );
					if ( $query && mysqli_num_rows( $query ) == 1 ) {
						return mysqli_fetch_array( $query )['id'];
					} else {
						return -1;
					}
				}
				break;
		}
	}
	mysqli_close( $con );
?>