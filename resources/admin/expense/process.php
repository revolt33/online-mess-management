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
			if ( $_POST['data'] ) {
				$var = json_decode($_POST['data']);
				$type = trim(htmlentities( $var->type->name ));
				switch ($type) {
					case "emp_details" :
						$id = trim(htmlentities( $var->values->id ));
						mysqli_select_db( $con, $_SESSION['database'] );
						$str = "select post, salary from employee where id=".$id;
						$query = mysqli_query( $con, $str );
						$response = array();
						if ( $query ) {
							$row = mysqli_fetch_array( $query );
							array_push( $response , array( 'status' => 1 ));
							array_push( $response , array( 'post' => $row['post'], 'salary' => $row['salary'] ));
						} else {
							array_push( $response , array( 'status' => 0 ) );
						}
						echo json_encode(array( 'response' => $response ));
						break;
					case "add_wage" :
						$response = array();
						if ( checkPassword($con, $var) ) {
							if ( checkSession( $con ) ) {
								$id = trim(htmlentities(( $var->values->id )));
								$comment = trim(htmlentities(( $var->values->comment )));
								$hostler = trim(htmlentities(( $var->values->hostler )));
								mysqli_select_db( $con, $_SESSION['database'] );
								$str = "select salary from employee where id=".$id;
								$query = mysqli_query( $con, $str );
								if ( $query && mysqli_num_rows($query) > 0 && strlen($comment) > 0 && strlen($hostler) > 0 && ( $hostler =='y' || $hostler == 'n' ) ) {
									$row = mysqli_fetch_array( $query );
									$salary = $row['salary'];
									$str = "select id, current from expense where status='a'";
									$query = mysqli_query( $con, $str );
									$status = false;
									if ( $query )
										if ( mysqli_num_rows($query) > 0 ) {
											$row = mysqli_fetch_array( $query );
											$serial = $row['id'];
											$current = $row['current'] + 1;
											$str = "update expense set current=".$current.", cost=cost+".$salary." where status='a'";
											$query = mysqli_query( $con, $str );
											if ( $query ) {
												$str = "update subExpense set cost=cost+".$salary.", entries=entries+1 where id=".$serial." and type='w'";
												$query = mysqli_query( $con, $str );
												if ( $query ) {
													$str = "insert into wage_".$serial." values(".$id.", ".$salary.", '".$comment."', '".date('Y-m-d')."', ".$current.", ".$_SESSION['id'].", '".$hostler."')";
													$query = mysqli_query( $con, $str );
													if ( $query )
														$status = true;
													else {
														$str = "update subExpense set cost=cost-".$salary.", entries=entries-1 where id=".$serial." and type='w'";
														$query = mysqli_query( $con, $str );
														$str = "update expense set current=current-1, cost=cost-".$salary." where status='a'";
														$query = mysqli_query( $con, $str );
													}
												} else {
													$str = "update expense set current=current-1, cost=cost-".$salary." where status='a'";
													$query = mysqli_query( $con, $str );
												}
											}
										}
									if ( $status )
										array_push($response, array( 'status' => 0, 'msg' => "Wage added Successfully." ));
									else
										array_push($response, array( 'status' => 1, 'msg' => 'Wage could not be added.' ));
									array_push( $response , array( 'rememberPassword' => $var->type->rememberPassword ) );
								} else {
									array_push( $response ,  array( 'status' => 4, 'msg' => 'Employee details do not match') );
								}
							} else {
								array_push( $response , array( 'status' => 3, 'msg' => 'Please start the session.' ) );
							}
						} else {
							array_push( $response, array( 'status' => 2, 'msg' => 'Incorrect transaction password!' ) );
						}
						echo json_encode(array( 'response' => $response ));
						break;
					case 'add_exp':
						$response = array();
						if ( checkPassword($con, $var) ) {
							if ( checkSession( $con ) ) {
								$title = trim(htmlentities( $var->values->title ));
								$amount = trim(htmlentities( $var->values->amount ));
								$file = trim(htmlentities( $var->values->file ));
								$hostler = trim(htmlentities(( $var->values->hostler )));
								$image = '';
								if ( strlen($file) > 0 && strlen($title) > 0 && strlen($amount) > 0 && $amount > 0 && ( $file == 'image/jpeg' || $file == 'image/jpg' || $file == 'image/png' ) && ( $hostler =='y' || $hostler == 'n' ) ) {
									mysqli_select_db( $con, $_SESSION['database'] );
									$str = "select id, current from expense where status='a'";
									$query = mysqli_query( $con, $str );
									$status = false;
									if ( $query && mysqli_num_rows( $query ) > 0 ) {
										$row = mysqli_fetch_array( $query );
										$serial = $row['id'];
										$current = $row['current'] + 1;
										$str = "update expense set current=".$current.", cost=cost+".$amount." where status='a'";
										$query = mysqli_query( $con, $str );
										if ( $query ) {
											$str = "update subExpense set cost=cost+".$amount.", entries=entries+1 where id=".$serial." and type='e'";
											
											if ( $file == 'image/jpeg' )
												$image = "".$current.".jpeg";
											else if ( $file == 'image/jpg' )
												$image = "".$current.".jpg";
											else if ( $file == 'image/png' )
												$image = "".$current.".png";
											$query = mysqli_query( $con, $str );
											if ( $query ) {
												$str = "insert into exp_".$serial." values(".$current.", 'transaction".$image."', '".$title."', '".date('Y-m-d')."', ".$_SESSION['id'].", ".$amount.", '".$hostler."')";
												$query = mysqli_query( $con, $str );
												if ( $query )
													$status = true;
												else {
													$str = "update subExpense set cost=cost-".$amount.", entries=entries-1 where id=".$serial." and type='e'";
													$query = mysqli_query( $con, $str );
													$str = "update expense set current=current-1, cost=cost-".$amount."where status='a'";
													$query = mysqli_query( $con, $str );
												}
											} else {
												$str = "update expense set current=current-1, cost=cost-".$amount." where status='a'";
												$query = mysqli_query( $con, $str );
											}
										}
									}
									if ( $status ) {
										array_push( $response , array( 'status' => 0, 'msg' => 'Expense added Successfully.' ));
										array_push( $response , array( 'image' => "transaction".$image ));
									} else
										array_push( $response , array( 'status' => 1, 'msg' => 'Expense could not be added.' ) );
									array_push( $response , array( 'rememberPassword' => $var->type->rememberPassword ) );
								} else
								array_push( $response , array( 'status' => 4, 'msg' => 'Inappropriate input type.' ) );
							} else
								array_push( $response , array( 'status' => 3, 'msg' => 'Please start the session.' ) );
						} else
							array_push($response , array( 'status' => 2, 'msg' => 'Incorrect transaction password.' ) );
						echo json_encode(array( 'response' => $response ));
						break;
					case 'add_misc':
						$response = array();
						if ( checkPassword( $con, $var ) ) {
							if ( checkSession( $con ) ) {
								$title = trim(htmlentities( $var->values->title ));
								$amount = trim(htmlentities( $var->values->amount ));
								$comment = trim(htmlentities( $var->values->comment ));
								$hostler = trim(htmlentities(( $var->values->hostler )));
								if ( strlen($title) > 0 && strlen($amount) > 0 && $amount > 0 && strlen($comment) > 0 && ( $hostler =='y' || $hostler == 'n' ) ) {
									mysqli_select_db( $con, $_SESSION['database'] );
									$str = "select id, current from expense where status='a'";
									$query = mysqli_query( $con, $str );
									$status = false;
									if ( $query && mysqli_num_rows( $query ) > 0 ) {
										$row = mysqli_fetch_array( $query );
										$serial = $row['id'];
										$current = $row['current'] + 1;
										$str = "update expense set current=".$current.", cost=cost+".$amount." where status='a'";
										$query = mysqli_query( $con, $str );
										if ( $query ) {
											$str = "update subExpense set cost=cost+".$amount.", entries=entries+1 where id=".$serial." and type='m'";
											$query = mysqli_query( $con, $str );
											if ( $query ) {
												$str = "insert into misc_".$serial." values(".$current.", '".$title."', '".$comment."', '".date('Y-m-d')."', ".$_SESSION['id'].", ".$amount.", '".$hostler."')";
												$query = mysqli_query( $con, $str );
												if ( $query )
													$status = true;
												else {
													$str = "update subExpense set cost=cost-".$amount.", entries=entries-1 where id=".$serial." and type='m'";
													$query = mysqli_query( $con, $str );
													$str = "update expense set current=current-1, cost=cost-".$amount."where status='a'";
													$query = mysqli_query( $con, $str );
												}
											} else {
												$str = "update expense set current=current-1, cost=cost-".$amount." where status='a'";
												$query = mysqli_query( $con, $str );
											}
										}
									}
									if ( $status )
										array_push( $response , array( 'status' => 0, 'msg' => 'Expense added Successfully.' ));
									else
										array_push( $response , array( 'status' => 1, 'msg' => 'Expense could not be added.' ) );
									array_push( $response , array( 'rememberPassword' => $var->type->rememberPassword ) );
								} else
								array_push( $response , array( 'status' => 4, 'msg' => 'Inappropriate input type.' ) );
							} else
								array_push( $response , array( 'status' => 3, 'msg' => 'Please start the session.' ) );
						} else
							array_push($response , array( 'status' => 2, 'msg' => 'Incorrect transaction password.' ) );
						echo json_encode(array( 'response' => $response ));
						break;
					case 'get_data':
						mysqli_select_db( $con, 'admin' );
						$str = "select serial from messdetails where mess='".$_SESSION['database']."'";
						$query = mysqli_query( $con, $str );
						$row = mysqli_fetch_array( $query );
						$serial = $row[0];
						$str = "select id, name from users where serial=".$serial;
						$query = mysqli_query( $con, $str );
						$users = array();
						while ( $row = mysqli_fetch_array( $query ) )
							$users[$row['id']] = $row['name'];
						mysqli_select_db( $con, $_SESSION['database'] );
						$wage = "
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
								$str = "select name, comment, date, amount, uid, all_users from wage_".$current.", employee where eid=id";
								$query = mysqli_query( $con, $str );
								while ( $row = mysqli_fetch_array( $query ) ) {
									$wage .= "
									<div class='row'>
										<div class='hover' hovertext='".$row['comment']."'></div>
										<div class='column'>".$row['name']."</div>
										<div class='column'>".$row['amount']."</div>
										<div class='column'>".$row['date']."</div>
										<div class='column'>".$users[$row['uid']]."</div>
										<div class='column'>"; if ($row['all_users'] == 'y') $wage .= "Everyone"; else $wage .= "Hostlers"; $wage .= "</div>
									</div>
							";
								}
							}
						$expense = "
							<div class='row bold'>
								<div class='column'>Title</div>
								<div class='column'>Amount</div>
								<div class='column'>Date</div>
								<div class='column'>Added By</div>
								<div class='column'>Applied To</div>
							</div>";
							if ( $status ) {
								$str = "select title, image, amount, date, uid, all_users from exp_".$current;
								$query = mysqli_query( $con, $str );
								while ( $row = mysqli_fetch_array( $query ) ) {
									$expense .= "
									<div class='row'>
										<div class='hover image' location='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."hostels".DIRECTORY_SEPARATOR."hostel".$serial.DIRECTORY_SEPARATOR.$row['image']."' hovertext='Click to see image.'></div>
										<div class='column'>".$row['title']."</div>
										<div class='column'>".$row['amount']."</div>
										<div class='column'>".$row['date']."</div>
										<div class='column'>".$users[$row['uid']]."</div>
										<div class='column'>"; if ($row['all_users'] == 'y') $expense .= "Everyone"; else $expense .= "Hostlers"; $expense .= "</div>
									</div>
							";
								}
							}
						$misc = "
							<div class='row bold'>
								<div class='column'>Title</div>
								<div class='column'>Amount</div>
								<div class='column'>Date</div>
								<div class='column'>Added By</div>
								<div class='column'>Applied To</div>
							</div>";
							if ( $status ) {
								$str = "select title, comment, amount, date, uid, all_users from misc_".$current;
								$query = mysqli_query( $con, $str );
								while ( $row = mysqli_fetch_array( $query ) ) {
									$misc .= "
									<div class='row'>
										<div class='hover' hovertext='".$row['comment']."'></div>
										<div class='column'>".$row['title']."</div>
										<div class='column'>".$row['amount']."</div>
										<div class='column'>".$row['date']."</div>
										<div class='column'>".$users[$row['uid']]."</div>
										<div class='column'>"; if ($row['all_users'] == 'y') $misc .= "Everyone"; else $misc .= "Hostlers"; $misc .= "</div>
									</div>
							";
								}
							}
						$response['indices'] = array( 31, 32, 33 );
						$response['values'] = array( 31 => $wage, 32 => $expense, 33 => $misc );
						echo json_encode($response);
						break;
				}
			}
		}
	} else {
		header('Location: index.php');
	}
	mysqli_close( $con );
?>