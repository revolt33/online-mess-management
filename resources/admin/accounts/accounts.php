<?php
	require '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'connection.php';
	require '..'.DIRECTORY_SEPARATOR.'utility.php';
	session_start();
	session_regenerate_id(true);
	date_default_timezone_set("Asia/Calcutta");
	$escape = "SET sql_mode='NO_BACKSLASH_ESCAPES'";
	mysqli_query($con, $escape);
	if ( checkAuthToken() && checkMode( $con ) ) {
		if ( $_POST ) {
			if ( isset( $_POST['data'] ) ) {
				$var = json_decode( $_POST['data'] );
				$type = trim( htmlentities( $var->type->name ) );
				if ( strcmp( $type , 'avail' ) == 0 ) {
					$userid = trim( htmlentities( $var->values->id ) );
					$userid = intval( $userid );
					mysqli_select_db( $con, $_SESSION['database'] );
					$avail = checkAvailability( $con, $userid);
					if ( $avail ) {
						echo "This username is available.";
					} else {
						echo "This username is not available.";
					}
				} elseif (strcmp( $type , 'add_user') == 0) {
					$status = -1;
					$msg = "Some error Occured!";
					$response = array();
					if ( checkPassword( $con, $var ) ) {
						$username = trim(htmlentities( $var->values->username ));
						$name = trim(htmlentities( $var->values->name ));
						$email = trim(htmlentities( $var->values->email ));
						$dob = trim(htmlentities( $var->values->dob ));
						$roll = trim(htmlentities( $var->values->roll ));
						$room = trim(htmlentities( $var->values->room ));
						$mobile = trim(htmlentities( $var->values->mobile ));
						$gender = trim(htmlentities( $var->values->gender ));
						$subsidized = trim(htmlentities( $var->values->subsidized ));
						$balance = trim(htmlentities( $var->values->balance ));
						mysqli_select_db( $con, $_SESSION['database'] );
						$avail = checkAvailability( $con, $username);
						if ( $avail ) {
							if ( ( strlen($name) > 0 ) && ( $username > 0 ) && validateEmail($email) && validateDate($dob) && ( $roll > 0 ) && ( $room >= 0 ) && ( $mobile > 0 ) && ( strlen($gender) == 1 ) && ( strlen($subsidized) == 1 ) ) {
								$str = "insert into users (id, name, email, dob, roll, room, mobile, gender, image, password, fpactive) values (".$username.", '".$name."', '".$email."', '".$dob."', ".$roll.", ".$room.", ".$mobile.", '".$gender."', 'user_".$username.".jpg', '".encryptPassword($username, 'password')."', 'y')";
								$query = mysqli_query( $con, $str );
								if ( $query ) {
									$str = "insert into members (id, subsidized, status, total, current) values (".$username.", '".$subsidized."', 'd', ".$balance.", ".$balance.")";
									$query = mysqli_query( $con, $str );
									if ( $query ) {
										if ( createUser( $con, $username ) ) {
											$str = "insert into notifs_".$username." (title, content, status, date, time, type) values('Account Created', 'Hello ".$name.", your account has been created in mess: ".getMessName($con).".', 'n', '".date('Y-m-d')."', '".date('G:i A')."', 0)";
											mysqli_select_db( $con, $_SESSION['database'] );
											mysqli_query( $con, $str );
											mysqli_select_db( $con, 'admin' );
											$str = "update messdetails set members = members + 1, total = total + ".$balance." where mess='".$_SESSION['database']."'";
											$query = mysqli_query( $con, $str );
											if ( $query ) {
												$status = 0;
												$msg = "User added successfuly.";
											}
										}
									}
								}
							} else {
								$status = 2;
								$msg = "Inappropriate input provided.";
							}
						} else {
							$status = 1;
							$msg = "Username not available.";
						}
					} else {
						$status = 3;
						$msg = "Incorrect transaction password.";
					}
					array_push($response, array( 'status' => $status, 'msg' => $msg ));
					if ( $status == 0 ) {
						array_push($response, array( 'username' => $username ));
					}
					if ( $status != 3 ) {
						array_push( $response, array( 'rememberPassword' => $var->type->rememberPassword ));
					}
					echo json_encode( array( 'response' => $response ) );
				} elseif ( strcmp($type, 'userList' ) == 0 ) {
					mysqli_select_db( $con, 'admin' );
					$str = "select serial from messdetails where mess='".$_SESSION['database']."'";
					$query = mysqli_query( $con, $str );
					$row = mysqli_fetch_array( $query );
					$serial = $row[0];
					mysqli_select_db( $con, $_SESSION['database'] );
					$str = "select * from users natural join members where status='d' OR status='a' OR status='c'";
					$query = mysqli_query( $con, $str );
					$imagePatd = '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'hostels'.DIRECTORY_SEPARATOR."hostel".$serial.DIRECTORY_SEPARATOR;
					if ( $query ) {
						if ( mysqli_num_rows( $query ) > 0 ) {
							echo "
							<div class='table'>
								<div class='row header'>
									<div class='column'>ID</div>
									<div class='column'>Room</div>
									<div class='column'>Name</div>
									<div class='column'>Photo</div>
									<div class='column'>Deposit Money</div>
									<div class='column'>Balance</div>
								</div>
							";
							while ($row = mysqli_fetch_array( $query ) ) {
								echo "
								<div class='row contents'>
								<div class='hover' serial='".$row['id']."'>"; if( $row['status'] == 'a' ) {echo "<div mode='close' class='leftoptions close'>Close</div>"; } elseif( $row['status'] == 'd' ) { echo "<div mode='activate' class='leftoptions close'>Activate</div>"; } if( $row['status'] != 'r' ) { echo "<div class='leftoptions remove'>Remove</div>"; } echo "<div class='rightoptions money'>Add Money</div><div class='rightoptions fine'>Add Fine</div></div>
									<div class='column'>".$row['id']."</div>
									<div class='column'>".$row['room']."</div>
									<div class='column'>".$row['name']."</div>
									<div class='column'><div class='frame'><img src='".$imagePatd.$row['image']."' widtd='80' height='80'></div></div>
									<div class='column'>".$row['total']."</div>
									<div class='column'>".$row['current']."</div>
								</div>
								";
							}
							echo "</div>";
						}
					}
				} elseif ( $type == 'cr' ) {
					$status = -1;
					$msg = "Some error Occured.";
					$response = array();
					if ( checkPassword( $con, $var ) ) {
						$mode = trim(htmlentities( $var->values->mode ));
						$str = "";
						$id = trim(htmlentities( $var->values->account ));
						$notice = "insert into notifs_".$id." (title, content, status, date, time, type) values(";
						$ch = "";
						mysqli_select_db( $con, $_SESSION['database'] );
						if ( $mode == 'close' ) {
							$date = date('Y-m-d');
							$ch = "c";
							$str = "update members set status='".$ch."', closing='".$date."' where id=".$id;
							$notice .= "'Account Closed', 'Your account with id: ".$id." has been closed by the administrator, therefore you will not be able to perform transactions anymore from this account.',";
						} elseif ( $mode == 'remove' ) {
							$date = date('Y-m-d');
							$ch = "r";
							$str = "select closing from members where id=".$id;
							$query = mysqli_query( $con, $str );
							if ( $query ) {
								$row = mysqli_fetch_array($query);
								if ( $row[0] == NULL ) {
									$str = "update members set status='".$ch."', closing='".$date."' where id=".$id;
								} else {
									$str = "update members set status='".$ch."' where id=".$id;
								}
							}
							$notice .= "'Account Removed', 'Your account with id: ".$id." has been removed by the administrator, therefore you will not be able to perform transactions anymore from this account.',";
						} elseif ( $mode == 'activate' ) {
							$date = date('Y-m-d');
							$ch = 'a';
							$str = "update members set status='".$ch."', opening='".$date."' where id=".$id;
							$notice .= "'Account Activation', 'Your account with id: ".$id." has been activated by the administrator, therefore you will be able to perform transactions from this account.',";
						}
						$notice .= " 'n', '".date('Y-m-d')."', '".date('G:i A')."', 0)";
						$query = mysqli_query( $con, $str );
						if ( $query ) {
							mysqli_query( $con, $notice );
							$status = 0;
							if ( $mode == 'close' ) {
								$msg = "Account ".$id." is closed";
							} elseif ( $mode == 'remove' ) {
								$msg = "Account ".$id." is removed";
							} elseif ($mode == 'activate') {
								$msg = "Account ".$id." is activated.";
							}
						} else {
							$status = 1;
							$msg = "Request could not be completed.";
						}

					} else {
						$status = 2;
						$msg = "Incorrect transaction password.";
					}
					array_push($response, array('status' => $status, 'msg' => $msg ));
					if ( $status != 2 ) {
						array_push( $response, array( 'rememberPassword' => $var->type->rememberPassword ));
					}
					echo json_encode( array( 'response' => $response ) );
				} elseif ( $type == 'money' ) {
					$status = -1;
					$msg = "Some error Occured.";
					$response = array();
					if ( checkPassword( $con, $var ) ) {
						$amount = trim(htmlentities( $var->values->amount ));
						$amount = intval($amount);
						$id = trim(htmlentities( $var->values->account ));
						mysqli_select_db( $con, 'admin' );
						$str = "update messdetails set total = total + ".$amount." where mess='".$_SESSION['database']."'";
						$query = mysqli_query( $con, $str );
						if ( $query ) {
							mysqli_select_db( $con, $_SESSION['database'] );
							$str = "update members set total = total + ".$amount.", current = current + ".$amount." where id = ".$id;
							$query = mysqli_query( $con, $str );
							if ( $query ) {
								$status = 0;
								$msg = "Money added successfuly.";
								$str = "insert into notifs_".$id." ( title, content, status, date, time, type ) values ('Money Added', 'Your account has been credited by amount ".$amount." INR, Admin:".$_SESSION['name']."', 'n', '".date('Y-m-d')."', '".date('G:i A')."', 0)";
								mysqli_query( $con, $str );
							} else {
								$status = 0;
								$msg = "Money could not be added.";
							}
						}
					} else {
						$status = 2;
						$msg = "Incorrect transaction password.";
					}
					array_push($response, array('status' => $status, 'msg' => $msg ));
					if ( $status != 2 ) {
						array_push( $response, array( 'rememberPassword' => $var->type->rememberPassword ));
					}
					echo json_encode( array( 'response' => $response ) );
				} elseif ( $type == 'fine' ) {
					$status = -1;
					$msg = "Some error Occured.";
					$response = array();
					if ( checkPassword( $con, $var ) ) {
						$amount = trim(htmlentities( $var->values->amount ));
						$amount = intval($amount);
						$id = trim(htmlentities( $var->values->account ));
						mysqli_select_db( $con, $_SESSION['database'] );
						$str = "update members set current = current - ".$amount." where id=".$id;
						$query = mysqli_query( $con, $str );
						if ( $query ) {
							$status = 0;
							$msg = "Fine charged to userid:".$id;
							$str = "insert into notifs_".$id." ( title, content, status, date, time, type ) values ('Fine Charged', 'Your have been charged with a fine of amount ".$amount." INR by ".$_SESSION['name'].", please contact the administrator if reason is not known to you.', 'n', '".date('Y-m-d')."', '".date('G:i A')."', 0)";
							mysqli_query( $con, $str );
						} else {
							$status = 1;
							$msg = "Fine could not be charged.";
						}
					} else {
						$status = 2;
						$msg = "Incorrect transaction password.";
					}
					array_push($response, array('status' => $status, 'msg' => $msg ));
					if ( $status != 2 ) {
						array_push( $response, array( 'rememberPassword' => $var->type->rememberPassword ));
					}
					echo json_encode( array( 'response' => $response ) );
				} elseif ( $type == 'details' ) {
					$id = intval(trim(htmlentities( $var->values->account )));
					mysqli_select_db( $con, 'admin' );
					$str = "select serial from messdetails where mess='".$_SESSION['database']."'";
					$query = mysqli_query( $con, $str );
					$row = mysqli_fetch_array( $query );
					$serial = $row[0];
					mysqli_select_db( $con, $_SESSION['database'] );
					$str = "select * from users natural join members where id=".$id;
					$imagePatd = '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'hostels'.DIRECTORY_SEPARATOR."hostel".$serial.DIRECTORY_SEPARATOR;
					$query = mysqli_query( $con, $str );
					if ( $query ) {
						$row = mysqli_fetch_array( $query );
						echo "
						<table cellspacing='30px'>
							<tr>
								<td><span class='right'>USER ID:</span></td>
								<td>".$row['id']."</td>
								<td rowspan='8'><img src='".$imagePatd.$row['image']."' widtd='140px' height='170px' /></td>
							</tr>
							<tr>
								<td><span class='right'>Name:</span></td>
								<td>".$row['name']."</td>
							</tr>
							<tr>
								<td><span class='right'>Email:</span></td>
								<td>".$row['email']."</td>
							</tr>
							<tr>
								<td><span class='right'>Gender:</span></td>
								<td>"; if( $row['gender'] == 'm' ) { echo "Male"; } elseif ( $row['gender'] == 'f' ) { echo "Female"; } echo "</td>
							</tr>
							<tr>
								<td><span class='right'>Age:</span></td>
								<td>"; $from = new DateTime($row['dob']); $to   = new DateTime('today'); echo $from->diff($to)->y; echo " years</td>
							</tr>
							<tr>
								<td><span class='right'>Roll No:</span></td>
								<td>".$row['roll']."</td>
							</tr>
							<tr>
								<td><span class='right'>Room No:</span></td>
								<td>".($row['room']>0?$row['room']:'non-hostler')."</td>
							</tr>
							<tr>
								<td><span class='right'>Mobile:</span></td>
								<td>".$row['mobile']."</td>
							</tr>
						</table>
						";
					}
				} elseif ( $type == 'add_emp' ) {
					$status = 3;
					$msg = 'Some error Occured';
					$response = array();
					if ( checkPassword( $con, $var ) ) {
						mysqli_select_db( $con, $_SESSION['database'] );
						$name = trim(htmlentities( $var->values->name ));
						$post = trim(htmlentities( $var->values->post ));
						$salary = intval(trim(htmlentities( $var->values->salary )));
						$str = "insert into employee (name, post, salary, status) values ('".$name."', '".$post."', ".$salary.", 'w')";
						$query = mysqli_query( $con, $str );
						if ( $query ) {
							$status = 0;
							$msg = "Employee added successfuly.";
						} else {
							$status = 1;
							$msg = "Employee could not be added.";
						}
					} else {
						$status = 2;
						$msg = "Incorrect Transaction Password.";
					}
					array_push($response, array('status' => $status, 'msg' => $msg ));
					if ( $status != 2 ) {
						array_push( $response, array( 'rememberPassword' => $var->type->rememberPassword ));
					}
					echo json_encode( array( 'response' => $response ) );
				} elseif ( $type == 'emp_list' ) {
					mysqli_select_db( $con, $_SESSION['database'] );
					$str = "select * from employee where status='w'";
					$query = mysqli_query( $con, $str );
					if ( $query ) {
						echo "
						<div class='row'>
							<div class='column'>Serial</div>
							<div class='column'>Name</div>
							<div class='column'>Post</div>
							<div class='column'>Salary</div>
							<div class='column'></div>

						</div>
						";
						$serial = 1;
						while ($row = mysqli_fetch_array ( $query )) {
							echo "
							<div class='row'>
								<div class='column'>".$serial."</div>
								<div class='column'>".$row['name']."</div>
								<div class='column'>".$row['post']."</div>
								<div class='column'>".$row['salary']."</div>
								<div class='column'><a href='#' class='remove_emp' code='".$row['id']."'>Remove</a></div>
							</div>
							";
							$serial++;
						}
					}
				} elseif ( $type == 'remove_emp' ) {
					$status = 3;
					$msg = 'Some error Occured';
					$response = array();
					if ( checkPassword( $con, $var ) ) {
						$id = intval(trim(htmlentities( $var->values->id )));
						$str = "update employee set status='r' where id=".$id;
						mysqli_select_db( $con, $_SESSION['database'] );
						$query = mysqli_query( $con, $str );
						if ( $query ) {
							$status = 0;
							$msg = "Employee removed successfuly.";
						} else {
							$status = 1;
							$msg = "Employee could not be removed.";
						}
					} else {
						$status = 2;
						$msg = "Incorrect Transaction Password.";
					}
					array_push($response, array('status' => $status, 'msg' => $msg ));
					if ( $status != 2 ) {
						array_push( $response, array( 'rememberPassword' => $var->type->rememberPassword ));
					}
					echo json_encode( array( 'response' => $response ) );
				}
			}
		}
	} else {
		header('Location: index.php');
	}
	function checkAvailability( $con, $username ) {
		$str = "select count(id) as total from users where id=".$username;
		$query = mysqli_query( $con, $str );
		if ( $query ) {
			$row = mysqli_fetch_array( $query );
			if ( $row[0] > 0 ) {
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}
	function createUser( $con, $username ) {
		$str = "create  table notifs_".$username." (serial int(6) AUTO_INCREMENT NOT NULL UNIQUE PRIMARY KEY, title varchar(100), content varchar(200), status varchar(1), date date,time time, type int(5))";
		$query = mysqli_query( $con, $str );
		if ( $query ) {
			$str = "create table extras_".$username." (serial int(6) AUTO_INCREMENT NOT NULL UNIQUE PRIMARY KEY, type varchar(1), name varchar(20), value decimal(5,2), addedBy int(5), date date, status varchar(1))";
			$query = mysqli_query( $con, $str );
			if ( $query ) {
				return true;
			} else {
				return false;
			}
		}  else {
			return false;
		}
	}
	function getMessName( $con ) {
		$str = "select name from messdetails where mess='".$_SESSION['database']."'";
		mysqli_select_db( $con, 'admin' );
		$query = mysqli_query( $con, $str );
		if ( $query && mysqli_num_rows( $query ) == 1 ) {
			return mysqli_fetch_array( $query )[0];
		} else
			return "";
	}
	mysqli_close( $con );
?>