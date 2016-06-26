<?php
	require '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'connection.php';
	require '..'.DIRECTORY_SEPARATOR.'utility.php';
	session_start();
	session_regenerate_id(true);
	date_default_timezone_set("Asia/Calcutta");
	$escape = "SET sql_mode='NO_BACKSLASH_ESCAPES'";
	mysqli_query($con, $escape);
	if ( checkAuthToken() && checkMode( $con ) ) {
		if ( isset($_POST['data']) ) {
			$var = json_decode( $_POST['data'] );
			switch ( $var->type->name ) {
				case 'get_data':
					mysqli_select_db( $con, 'admin' );
					$str = "select id, users.name from messdetails join users on users.serial=messdetails.serial where mess='".$_SESSION['database']."'";
					$names = array();
					$query = mysqli_query( $con, $str );
					if ( $query ) {
						while ( $row = mysqli_fetch_array( $query ) ) {
							$names[$row['id']] = $row['name'];
						}
					}
					mysqli_select_db( $con, $_SESSION['database'] );
					echo "
					<table cellspacing='5px' cellpadding='5px'>
						<tr>
							<th>Name of extra</th>
							<th>Date</th>
							<th>Cost/Points</th>
							<th>Added By</th>
						</tr>";
					$str = "select * from extras_".$var->values->id." order by date";
					$query = mysqli_query( $con, $str );
					if ( $query ) {
						while ( $row = mysqli_fetch_array( $query ) ) {
						$href = $row['status']=='n'?"<a href='#' onclick='preRemove(this)' serial='".$row['serial'].":".$var->values->id."'>Remove</a>":"";
							echo "
						<tr>
							<td>".$row['name']."</td>
							<td>".((new DateTime($row['date']))->format('d F Y'))."</td>
							<td>".$row['value']."</td>
							<td>".$names[$row['addedBy']]."</td>
							<td>".$href."</td>
						</tr>
							";
						}
					}
					echo "</table>
					";
					break;
				case 'add_extra':
					$status = 0;
					$msg = "";
					$response = array();
					if ( checkPassword( $con, $var ) ) {
						$total = intval(trim($var->type->total));
						$meal = intval(trim( $var->values->meal ));
						$type = trim( $var->values->type );
						$date = trim( $var->values->date );
						if ( $total > 0 && validateDate( $date ) ) {
							mysqli_select_db( $con, $_SESSION['database'] );
							$str = "select * from ".($type=='m'?'meals':($type=='e'?'extras':'none'))." where id=".$meal;
							$query = mysqli_query( $con, $str );
							if ( $query && mysqli_num_rows( $query ) > 0 ) {
								$row = mysqli_fetch_array( $query );
								$name = $row['name'];
								$value = $type=='e'?$row['cost']:$row['points'];
								$status = 1;
								$msg = "Extras added successfully";
								$proceed = true;
								for ($i=0; $i < $total && $proceed ; $i++) {
									$str = "select case when status='a' then 1 else 0 end 'new' from members where id=".$var->values->users->$i;
									if ( mysqli_fetch_array ( mysqli_query( $con, $str ) )[0] == 1 ) {
										$str = "insert into extras_".$var->values->users->$i." (type, name, value, addedBy, date, status) values('".$type."', '".$name."', ".$value.", '".$_SESSION['id']."', '".date('Y-m-d')."', 'n')";
										if ( !mysqli_query( $con, $str )) {
											$proceed = false;
											$status = 2;
											$msg = "Some of the extras could not be added";
										}
									}
								}
							}	
						} else {
							$status = 2;
							$msg = "Incorrect data format!";
						}
					} else {
						$status = 3;
						$msg = "Transaction password incorrect!";
					}
					array_push( $response , array( 'status' => $status, 'msg' => $msg, 'error' => mysqli_error( $con ) ) );
					if ( $status != 3 )
						array_push( $response , array( 'rememberPassword' => $var->type->rememberPassword ) );
					echo json_encode( $response );
					break;
				case 'remove_extra':
					$status = 0;
					$msg = "";
					$response = array();
					if ( checkPassword( $con, $var ) ) {
						$serial = $var->values->serial;
						$id = $var->values->id;
						mysqli_select_db( $con, $_SESSION['database'] );
						$str = "delete from extras_".$id." where serial=".$serial;
						if ( mysqli_query( $con, $str ) ) {
							$status = 1;
							$msg = "Extra removed successfully!";
						} else {
							$status = 2;
							$msg = "Extra could not be removed!";
						}
					} else {
						$status = 3;
						$msg = "Transaction password incorrect!";
					}
					array_push( $response , array( 'status' => $status, 'msg' => $msg ) );
					if ( $status != 3 )
						array_push( $response , array( 'rememberPassword' => $var->type->rememberPassword ) );
					echo json_encode( $response );
					break;
			}
		}
	} else {
		header('Location: ..'.DIRECTORY_SEPARATOR.'servings');
	}
	mysqli_close( $con );
?>