<?php
	require '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'connection.php';
	require '..'.DIRECTORY_SEPARATOR.'utility.php';
	require 'utility.php';
	session_start();
	session_regenerate_id(true);
	date_default_timezone_set("Asia/Calcutta");
	$escape = "SET sql_mode='NO_BACKSLASH_ESCAPES'";
	mysqli_query($con, $escape);
	$days = array( 
		1 => 'Sunday',
		2 => 'Monday',
		3 => 'Tuesday',
		4 => 'Wednesday',
		5 => 'Thursday',
		6 => 'Friday',
		7 => 'Saturday'
	 );
	if ( checkAuthToken() && checkMode( $con ) ) {
		if ( $_POST ) {
			if( isset($_POST['data']) ) {
				$var = json_decode($_POST['data']);
				switch( $var->type->name ) {
					case 'add_meal':
						$response = array();
						$status = 5;
						$msg = "";
						if ( checkPassword($con, $var) ) {
							$name = trim(htmlentities( $var->values->name ));
							$start = trim(htmlentities( $var->values->start ));
							$end = trim(htmlentities( $var->values->end ));
							$pts = trim(htmlentities( $var->values->pts ));
							$cost = trim(htmlentities( $var->values->cost ));
							mysqli_select_db( $con, $_SESSION['database'] );
							if ( strlen($name) > 0 && preg_match( '/^[\d]{1,2}:[\d]{1,2}/' , $start ) && preg_match('/^[\d]{1,2}:[\d]{1,2}/', $end ) && $pts >= 0 && $cost >= 0 && isTime($start, true, false) && isTime( $end, true, false ) && strtotime($start) < strtotime($end) ) {
								$str = "select * from meals where ((CAST('".$start."' AS TIME) BETWEEN start and end) OR (CAST('".$end."' AS TIME) BETWEEN start and end) OR (CAST('".$start."' AS TIME) < start and CAST('".$end."' AS TIME) > end)) and status='a'";
								if ( mysqli_num_rows( mysqli_query( $con, $str ) ) == 0 ) {
									$str = "insert into meals (name, start, end, status, points, cost) values ('".$name."', '".$start."', '".$end."', 'a', ".$pts.", ".$cost.")";
									$query = mysqli_query( $con, $str );
									if ( $query ) {
										$id = mysqli_insert_id( $con );
										$str = "insert into menu (meal) values (".$id.")";
										$query = mysqli_query( $con, $str );
										if( $query ) {
											$status = 0;
											$msg = "Meal added successfully.";
										} else {
											$str = "delete from meals where id=".$id;
											mysqli_query( $con, $str );
											$status = 1;
											$msg = "Meal could not be added.";
										}
									} else {
										$status = 1;
										$msg = "Meal could not be added.";
									}
								} else {
									$status = 1;
									$msg = "Time of meals are conflicting!";
								}
							} else {
								$status = 2;
								$msg = "Invalid input format";
							}
						} else {
							$status = 3;
							$msg = "Transaction passsword incorrect.";
						}
						array_push( $response , array( 'status' => $status, 'msg' => $msg ) );
						if ( $status != 3 )
							array_push( $response , array( 'rememberPassword' => $var->type->rememberPassword ) );
						echo json_encode(array( 'response' => $response ));
						break;
					case 'add_extra':
						$response = array();
						$status = 5;
						$msg = "";
						if ( checkPassword( $con, $var ) ) {
							$name = trim(htmlentities( $var->values->name ));
							$cost = trim(htmlentities( $var->values->cost ));
							mysqli_select_db( $con, $_SESSION['database'] );
							if ( strlen($name) > 0 && $cost > 0 ) {
								$str = "insert into extras (name, status, cost) values ('".$name."', 'a', ".$cost.")";
								$query = mysqli_query( $con, $str );
								if ( $query ) {
									$status = 0;
									$msg = "Extra added successfully.";
								} else {
									$status = 1;
									$msg = "Extra could not be added!";
								}
							} else {
								$status = 2;
								$msg = "Invalid input format";
							}
						} else {
							$status = 3;
							$msg = "Transaction passsword incorrect.";
						}
						array_push( $response , array( 'status' => $status, 'msg' => $msg ) );
						if ( $status != 3 )
							array_push( $response , array( 'rememberPassword' => $var->type->rememberPassword ) );
						echo json_encode(array( 'response' => $response ));
						break;
					case 'get_data':
						echo getContent($con);
						break;
					case 'get_scheduled_off':
						mysqli_select_db( $con, $_SESSION['database'] );
						$str = "select * from meals";
						$query = mysqli_query( $con, $str );
						$meals = array();
						while ( $row = mysqli_fetch_array( $query ) ) {
							$meals[$row['id']] = $row['name'];
						}
						$str = "select * from scheduledOff order by start_day";
						$query = mysqli_query( $con, $str );
						$response = "
							<table cellspacing='5px' cellpadding='10px'>
								<tr>
									<th colspan='2'>From</th>
									<th colspan='2'>To</th>
								</tr>
								<tr>
									<th>Meal</th>
									<th>Date</th>
									<th>Meal</th>
									<th>Date</th>
								</tr>";
								while ( $row = mysqli_fetch_array( $query ) ) {
									$response .= "
									<tr>
										<td>".$meals[$row['start_meal']]."</td>
										<td>".date('d F Y' , ((new DateTime($row['start_day']))->getTimestamp()))."</td>
										<td>".$meals[$row['end_meal']]."</td>
										<td>".date('d F Y' , ((new DateTime($row['end_day']))->getTimestamp()))."</td>
										<td><a onclick='removeScheduledOffPrompt(this)' href='#' meal='".$row['start_meal']."' date='".$row['start_day']."'>Remove</a></td>
									</tr>
									";
								}
							$response .= "
							</table>
						";
						echo $response;
						break;
					case 'add_scheduled_off':
						$response = array();
						$status = 3;
						$msg = "";
						$meals = array();
						if ( checkPassword( $con, $var ) ) {
							$start_meal = trim(htmlentities( $var->values->start_meal ));
							$start_date = trim(htmlentities( $var->values->start_date ));
							$end_meal = trim(htmlentities( $var->values->end_meal ));
							$end_date = trim(htmlentities( $var->values->end_date ));
							mysqli_select_db( $con, $_SESSION['database'] );
							$str = "select * from meals where status='a'";
							$query = mysqli_query( $con, $str );
							
							$proceed = false;
							if ( mysqli_num_rows($query) > 0 )
								$proceed = true;
							while ( $row = mysqli_fetch_array( $query ) ) {
								$meals[$row['id']] = $row['start'];
							}
							if ( $proceed ) {
								if ( !validateDate($start_date) || !validateDate($end_date) )
									$proceed = false;
								if ( $proceed && (date( $end_date ) >= date( $start_date )) ) {
									if ( date( $end_date ) == date( $start_date ) )
										if ( strtotime($meals[$start_meal]) > strtotime($meals[$end_meal]) )
											$proceed = false;
									$str = "select * from scheduledOff where (start_day<CAST('".$start_date."' AS DATE) and end_day>CAST('".$start_date."' AS DATE)) or (start_day<CAST('".$end_date."' AS DATE) and end_day>CAST('".$end_date."' AS DATE)) or (start_day>CAST('".$start_date."' AS DATE) and end_day<CAST('".$end_date."' AS DATE))";
									$query = mysqli_query( $con, $str );
									if ( mysqli_num_rows( $query ) > 0 )
										$proceed = false;
									$str = "select * from scheduledOff where start_day=CAST('".$end_date."' AS DATE) or end_day=CAST('".$start_date."' AS DATE)";
									$query = mysqli_query( $con, $str );
									while ( $proceed && ($row = mysqli_fetch_array( $query )) ) {
										if ( $row['start_day'] == $end_date ) {
											if ( strtotime($meals[$end_meal]) >= strtotime($meals[$row['start_meal']]) )
												$proceed = false;
										} else {
											if ( strtotime($meals[$row['end_meal']]) >= strtotime($meals[$start_meal]) )
												$proceed = false;
										}
									}
									if ( $proceed ) {
										$str = "insert into scheduledOff values(".$start_meal.", '".$start_date."', ".$end_meal.", '".$end_date."')";
										$query = mysqli_query( $con, $str );
										if ( $query ) {
											$status = 0;
											$msg = "Off scheduled successfully!";
										} else {
											$status = 1;
											$msg = "Off could not be scheduled!";
										}
									} else {
										$status = 1;
										$msg = "Dates are conflicting!";
									}
								} else {
									$proceed = false;
									$status = 1;
									$msg = "Incorrect dates provided!";
								}
							} else{
								$status = 1;
								$msg = "Dates not provided!";
							}
						} else {
							$status = 2;
							$msg = "Transaction passsword is incorrect!";
						}
						array_push( $response , array( 'status' => $status, 'msg' => $msg ) );
						if ( $status != 3 )
							array_push( $response , array( 'rememberPassword' => $var->type->rememberPassword ) );
						echo json_encode(array( 'response' => $response ));
						break;
					case 'add_weekly_off':
						$response = array();
						$status = 3;
						$msg = "";
						if ( checkPassword( $con, $var ) ) {
							$meal = trim(htmlentities( $var->values->meal ));
							$day = trim(htmlentities( $var->values->day ));
							mysqli_select_db( $con, $_SESSION['database'] );
							$str = "select * from weeklyoff where meal='".$meal."' and day=".$day." and status='a'";
							$query = mysqli_query( $con, $str );
							if ( mysqli_num_rows($query) == 0 ) {
								$str = "insert into weeklyoff ( meal, day, start, status) values ( ".$meal.", ".$day.", '".date('Y-m-d')."', 'a' )";
								$query = mysqli_query( $con, $str );
								if ( $query ) {
									$status = 0;
									$msg = "Weekly Off added successfully!";
								} else {
									$status = 1;
									$msg = "Weekly Off could not be added!";
								}
							} else {
								$status = 1;
								$msg = "Duplicate values are not allowed.";
							}
						} else {
							$status = 2;
							$msg = "Transaction passsword is incorrect!";
						}
						array_push( $response , array( 'status' => $status, 'msg' => $msg ) );
						if ( $status != 3 )
							array_push( $response , array( 'rememberPassword' => $var->type->rememberPassword ) );
						echo json_encode(array( 'response' => $response ));
						break;
					case 'menu_input':
						$response = array();
						$status = 3;
						$msg = "";
						if ( checkPassword( $con, $var ) ) {
							$input = trim(htmlentities( $var->values->menu_input ));
							$meal = intval(trim(htmlentities( $var->values->meal )));
							$day = intval(trim(htmlentities( $var->values->day )));
							mysqli_select_db( $con, $_SESSION['database'] );
							$str = "update menu set day".$day."='".$input."' where meal=".$meal;
							if ( mysqli_query( $con, $str ) ) {
								$status = 0;
								$msg = "Menu changed successfully!";
							} else {
								$status = 1;
								$msg = "Update could not be performed!";
							}
						} else {
							$status = 2;
							$msg = "Transaction passsword is incorrect!";
						}
						array_push( $response , array( 'status' => $status, 'msg' => $msg ) );
						if ( $status != 3 )
							array_push( $response , array( 'rememberPassword' => $var->type->rememberPassword ) );
						echo json_encode(array( 'response' => $response ));
						break;
					case 'allowed_off':
						$response = array();
						$status = 3;
						$msg = "";
						if ( checkPassword( $con, $var ) ) {
							$points = intval(trim(htmlentities( $var->values->points )));
							mysqli_select_db( $con, 'admin' );
							$str = "update messdetails set offLimit=".$points." where mess='".$_SESSION['database']."'";
							if ( mysqli_query( $con, $str ) ) {
								$status = 0;
								$msg = "Off Limit updated successfully!";
							} else {
								$status = 1;
								$msg = "Off Limit could not be updated!";
							}
						} else {
							$status = 2;
							$msg = "Transaction passsword is incorrect!";
						}
						array_push( $response , array( 'status' => $status, 'msg' => $msg ) );
						if ( $status != 3 )
							array_push( $response , array( 'rememberPassword' => $var->type->rememberPassword ) );
						echo json_encode(array( 'response' => $response ));
						break;
					case 'remove_weekly_off':
						$response = array();
						$status = 3;
						$msg = "";
						if ( checkPassword( $con, $var ) ) {
							$meal = trim(htmlentities( $var->values->meal ));
							$day = trim(htmlentities( $var->values->day ));
							mysqli_select_db( $con, $_SESSION['database'] );
							$str = "update weeklyoff set status='r', end='".date('Y-m-d')."' where meal=".$meal." and day=".$day;
							$query = mysqli_query( $con, $str );
							if ( $query ) {
								$status = 0;
								$msg = "Weekly Off removed successfully!";
							} else {
								$status = 1;
								$msg = "Weekly Off could not be removed!";
							}
						} else {
							$status = 2;
							$msg = "Transaction passsword is incorrect!";
						}
						array_push( $response , array( 'status' => $status, 'msg' => $msg ) );
						if ( $status != 3 )
							array_push( $response , array( 'rememberPassword' => $var->type->rememberPassword ) );
						echo json_encode(array( 'response' => $response ));
						break;
					case 'remove_meal':
						$response = array();
						$status = 3;
						$msg = "";
						if ( checkPassword( $con, $var ) ) {
							mysqli_select_db( $con, $_SESSION['database'] );
							$serial = trim(htmlentities( $var->values->serial ));
							$str = "update meals set status='r' where id=".$serial;
							$query = mysqli_query( $con, $str );
							$str = "update weeklyoff set status='r', end='".date('Y-m-d')."' where meal=".$serial;
							$query1 = mysqli_query( $con, $str );
							if ( $query && $query1 ) {
								$status = 0;
								$msg = "Meal removed successfully.";
							} else {
								$status = 1;
								$msg = "Meal could not be removed.";
							}
						} else {
							$status = 2;
							$msg = "Transaction passsword incorrect.";
						}
						array_push( $response , array( 'status' => $status, 'msg' => $msg ) );
						if ( $status != 2 )
							array_push( $response , array( 'rememberPassword' => $var->type->rememberPassword ) );
						echo json_encode(array( 'response' => $response ));
						break;
					case 'remove_extra':
						$response = array();
						$status = 3;
						$msg = "";
						if ( checkPassword( $con, $var ) ) {
							mysqli_select_db( $con, $_SESSION['database'] );
							$serial = trim(htmlentities( $var->values->serial ));
							$str = "update extras set status='r' where id=".$serial;
							$query = mysqli_query( $con, $str );
							if ( $query ) {
								$status = 0;
								$msg = "Extra removed successfully.";
							} else {
								$status = 1;
								$msg = "Extra could not be removed.";
							}
						} else {
							$status = 2;
							$msg = "Transaction passsword incorrect.";
						}
						array_push( $response , array( 'status' => $status, 'msg' => $msg ) );
						if ( $status != 2 )
							array_push( $response , array( 'rememberPassword' => $var->type->rememberPassword ) );
						echo json_encode(array( 'response' => $response ));
						break;
					case 'remove_scheduled_off':
						$response = array();
						$status = 3;
						$msg = "";
						if ( checkPassword( $con, $var ) ) {
							mysqli_select_db(  $con, $_SESSION['database'] );
							$start_date = trim(htmlentities( $var->values->date ));
							$start_meal = trim(htmlentities( $var->values->meal ));
							if ( validateDate($start_date) ) {
								$str = "delete from scheduledOff where start_meal=".$start_meal." and start_day='".$start_date."'";
								if ( mysqli_query( $con, $str ) ) {
									$status = 0;
									$msg = "Scheduled Off removed successfully!";
								} else {
									$status = 1;
									$msg = "Scheduled off could not be removed!";
								}
							} else {
								$status = 1;
								$msg = "Invalid date provided!";
							}
						} else {
							$status = 2;
							$msg = "Transaction passsword incorrect.";
						}
						array_push( $response , array( 'status' => $status, 'msg' => $msg, 'error' => mysqli_error($con) ) );
						if ( $status != 2 )
							array_push( $response , array( 'rememberPassword' => $var->type->rememberPassword ) );
						echo json_encode(array( 'response' => $response ));
						break;
					case 'refresh':
						$check = htmlentities( $var->values->check );
						$next = "0000-00-00";
						$remember = "n";
						if ( $check ) {
							$date = strtotime("+7 days");
							$next = date('Y-m-d', $date);
							$remember = 'y';
						} else {
							$date = strtotime("-1 days");
							$next = date('Y-m-d', $date);
							$remember = 'n';
						}
						$id = $_SESSION['id'];
						mysqli_select_db( $con, 'admin' );
						$str = "update users set upto='".$next."', remember='".$remember."' where id=".$id;
						$query = mysqli_query( $con, $str );
						$status = -1;
						$msg = "";
						$response = array();
						$expiration = getExpiration( $con );
						if ( $query ) {
							if ( $remember == 'y' ) {
								$status = 0;
								$msg = $expiration['remaining']." days remaining...";
							} elseif ( $remember == 'n' ) {
								$status = 1;
								$msg = "Expired...";
							}
						} else {
							$status = 2;
							$msg = "Refresh failed.";
						}
						$response = array();
						array_push($response, array( 'status' => $status, 'msg' => $msg ));
						if ( ( $status == 0 ) || ( $status == 1 ) ) {
							array_push($response, array( 'username' => $id, 'type' => 0, 'remember' => $expiration['remember'], 'validity' => $expiration['remaining'] ));
						}
						echo json_encode(array( 'response' => $response ));
						break;
					case 'get_menu':
						echo "
						<div class='row'>
							<div class='column menu_heading'></div>";
							for ($i=1; $i < 8; $i++) { 
								echo "<div class='column menu_heading'>".$days[$i]."</div>";
							}
							echo "
						</div>
							";
						mysqli_select_db( $con, $_SESSION['database'] );
						$str = "select * from menu join meals where status='a' and meal=id";
						$query = mysqli_query( $con, $str );
						while ( $row = mysqli_fetch_array( $query ) ) {
							echo "
							<div class='row'>
								<div class='column menu_heading'>".$row['name']."</div>
							";
							for ( $i=1; $i < 8; $i++ ) { 
								echo "<div class='column'><div class='menu_input'><div class='ask_save'>Save:<div class='accept'>&#x2714</div><div class='decline'>X</div></div><textarea day='".$i."' meal='".$row['meal']."' maxlength='70' rows='2' cols='15'>".$row['day'.$i]."</textarea></div><div class='menu_item'><div class='ask_edit'>Edit</div>";if( strlen($row['day'.$i]) == 0 ) { echo "No Item"; } else { echo $row['day'.$i]; } echo "</div></div>";
							}
							echo "
							</div>";
						}
						break;
					case 'change_password':
						$response = array();
						$msg = "";
						$old_password = trim(htmlentities( $var->values->old_password ));
						$new_password_1 = trim(htmlentities( $var->values->new_password_1 ));
						$new_password_2 = trim(htmlentities( $var->values->new_password_2 ));
						mysqli_select_db( $con, 'admin' );
						$str = "select password from users where id=".$_SESSION['id'];
						$query = mysqli_query( $con, $str );
						if ( $query && mysqli_num_rows( $query ) > 0 ) {
							$row = mysqli_fetch_array( $query );
							if ( encryptPassword( $_SESSION['id'], $old_password ) == $row[0] ) {
								if ( strlen($new_password_1) > 7 ) {
									if ( $new_password_1 == $new_password_2 ) {
										$str = "update users set password='".encryptPassword( $_SESSION['id'], $new_password_1 )."' where id=".$_SESSION['id'];
										if ( mysqli_query( $con, $str ) ) {
											$msg = "Password changed successfully!";
										} else
											$msg = "Password could not be changed.";
									} else
										$msg = "Passwords do not match!";
								} else
									$msg = "Password must be atleast 8 characters long.";
							} else
								$msg = "Old password is incorrect!";
						} else
							$msg = "An error Occured.";
						array_push($response, array( 'msg' => $msg ));
						echo json_encode($response);
						break;
				}
			}
		}
	} else {
		header('Location: ..'.DIRECTORY_SEPARATOR.'tuning');
	}
	mysqli_close( $con );
	function isTime($time,$is24Hours,$seconds) {
	    $pattern = "/^".($is24Hours ? "([1-2][0-3]|[01]?[1-9])" : "(1[0-2]|0?[1-9])").":([0-5]?[0-9])".($seconds ? ":([0-5]?[0-9])" : "")."$/";
	    if (preg_match($pattern, $time)) {
	        return true;
	    }
	    return false;
	}
?>