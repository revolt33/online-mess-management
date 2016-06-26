<?php
	require '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'connection.php';
	require '..'.DIRECTORY_SEPARATOR.'utility.php';
	session_start();
	session_regenerate_id(true);
	date_default_timezone_set("Asia/Calcutta");
	$escape = "SET sql_mode='NO_BACKSLASH_ESCAPES'";
	mysqli_query($con, $escape);
	if ( checkAuthToken() && checkMode( $con ) ) {
		$str = "select case status when 'a' then true else false end as 'status' from members where id=".$_SESSION['id'];
		mysqli_select_db( $con, $_SESSION['database'] );
		$query = mysqli_query( $con, $str );
		if ( isset($_POST['data']) && mysqli_fetch_array( $query )[0] ) {
			$var = json_decode($_POST['data']);
			if ( $var->head->length > 0 ) {
				$status = 0;
				$msg = "";
				$str = "select offLimit from messdetails where mess='".$_SESSION['database']."'";
				mysqli_select_db( $con, 'admin' );
				$offLimit = 0;
				$query = mysqli_query( $con, $str );
				$proceed = true;
				$totalPoints = 0;
				if ( $query && mysqli_num_rows( $query ) == 1 ) {
					$offLimit = mysqli_fetch_array( $query )[0];
				} else {
					$proceed = false;
					$status = 1;
					$msg = "Database Connection Error!";
				}
				mysqli_select_db( $con, $_SESSION['database'] );
				$str = "select * from meals";
				$meals = array();
				$query = mysqli_query( $con, $str );
				if ( $query && $proceed ) {
					while ( $row = mysqli_fetch_array( $query ) ) {
						$meals[$row['id']] = array('start' => $row['start'], 'status' => $row['status'], 'points' => $row['points'] );
					}
					$str = "select points from meals join offs on offs.meal=meals.id where date between cast('".date('Y-m-01')."' as date) and cast('".date('Y-m-t')."' as date) and offs.id=".$_SESSION['id'];
					$query = mysqli_query( $con, $str );
					if ( $query ) {
						while ( $row = mysqli_fetch_array( $query ) ) {
							$totalPoints += $row[0];
						}
					}
					for ($i=0; $i < $var->head->length; $i++) {
						$points = $meals[$var->values[$i]->meal]['points'];
						$totalPoints += $var->values[$i]->operation=='add_off'?$points:(-$points);
					}
					if ( $totalPoints > $offLimit ) {
						$proceed = false;
						$status = 2;
						$msg = "Off limit exceeded by ".($totalPoints-$offLimit)." points!";
					}
				} else {
					$proceed = false;
					$status = 1;
					$msg = "Database Connection Error!";
				}
				if ( $proceed )
				switch (checkValues( $con, $var, $meals )) {
					case 0:
						for ($i=0; $i < $var->head->length && $proceed; $i++) {
							$str = "select count(*) from meals where cast('".date('H:i:s')."' as time)<start and status='a'";
							$advance = mysqli_fetch_array( mysqli_query( $con, $str ) )[0]>0?0:1;
							if ( date('Y-m-d', mktime(0,0,0, date('m'), date('d')+$advance, date('Y'))) <= date($var->values[$i]->day) && date($var->values[$i]->day)<=date('Y-m-d', mktime(0,0,0, date('m'), date('d')+$advance+7, date('Y'))) ) {
								if ( $var->values[$i]->operation == 'add_off' ) {
									$str = "select count(*) from offs where date=cast('".$var->values[$i]->day."' as date) and meal=".$var->values[$i]->meal." and id=".$_SESSION['id'];
									$query = mysqli_query( $con, $str );
									if ( $query ) {
										$proceed = mysqli_fetch_array( $query )[0]==0?true:false;
										if ( $proceed ) {
											$str = "insert into offs (meal, date, status, id) values(".$var->values[$i]->meal.", '".$var->values[$i]->day."', 'n', ".$_SESSION['id'].")";
											$query = mysqli_query( $con, $str );
											if ( !$query ) {
												$proceed = false;
												$status = 0;
												$msg = "Some of the offs could not be Scheduled!";
											}
										} else {
											$proceed = false;
											$status = 0;
											$msg = "Some of the offs are conflicting.";
										}
									} else {
										$proceed = false;
										$status = 0;
										$msg = "Database connection Error!";
									}
								} elseif ( $var->values[$i]->operation == 'remove_off' ) {
									$str = "delete from offs where meal=".$var->values[$i]->meal." and date=cast('".date($var->values[$i]->day)."' as date) and id=".$_SESSION['id'];
									if ( !mysqli_query( $con, $str ) ) {
										$proceed = false;
										$status = 0;
										$msg = "Some of the offs could not be removed!";
									}
								}
							} else {
								$status = 0;
								$msg = "Dates are out of bound!";
							}
						}
						if ( $proceed ) {
							$status = 0;
							$msg = "Off(s) scheduled successfully!";
						}
						break;
					case 1:
						$status = 1;
						$msg = "Connection Error.";
						break;
					case 2:
						$status = 1;
						$msg = "Weekly Off Conflict.";
						break;
					case 3:
						$status = 1;
						$msg = "Scheduled off Conflict.";
						break;
				}
				echo json_encode(array('status' => $status, 'msg' => $msg ));
			}
		}
	} else {
		header('Location: ..'.DIRECTORY_SEPARATOR);
	}
	mysqli_close( $con );
	function checkValues($con, $var, $meals) {
		$proceed = false;
		$str = "select count(*) as count from weeklyOff where ";
		for ($i=0; $i < $var->head->length; $i++) {
			$str .= "(status='a' and meal=".$var->values[$i]->meal." and day=".(date( 'w', (new DateTime($var->values[$i]->day))->getTimestamp()) + 1);
			$str .= $i<($var->head->length-1)?") or ":")";
		}
		$query = mysqli_query($con, $str);
		if ( $query ) {
			$proceed =  mysqli_fetch_array( $query )['count']>0?false:true;
		} else {
			return 1; //Connection Error
		}
		if ( $proceed ) {
			for ($i=0; $i < $var->head->length; $i++) {
				$str = "select * from scheduledOff where cast('".$var->values[$i]->day."' as date) between start_day and end_day";
				$query = mysqli_query( $con, $str );
				if ( $query ) {
					while ( $row = mysqli_fetch_array( $query ) ) {
						$diff = ((new DateTime(date($row['start_day'])))->diff(new DateTime(date($row['end_day'])))->d);
						if ( $diff > 1 ) {
							return 3;
						} elseif ( $diff == 1 ) {
							if ( $row['start_day'] == $var->values[$i]->day ) {
								if ( strtotime($meals[$row['start_meal']]['start']) <= strtotime($meals[$var->values[$i]->meal]['start']) )
									return 3; // Scheduled Off Conflict
							} else {
								if ( strtotime($meals[$row['end_meal']]['start']) >= strtotime($meals[$var->values[$i]->meal]['start']) )
									return 3;
							}
						} else {
							if ( strtotime($meals[$row['start_meal']]['start']) <= strtotime($meals[$var->values[$i]->meal]['start']) && strtotime($meals[$var->values[$i]->meal]['start']) <= strtotime($meals[$row['end_meal']]['start']) )
								return 3;
						}
					}
				} else {
					return 1; //Connection Error

				}
			}
		} else {
			return 2; //Weekly Off Conflict
		}
		return 0;
	}
	
?>