<?php
	require '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'connection.php';
	session_start();
	session_regenerate_id(true);
	$escape = "SET sql_mode='NO_BACKSLASH_ESCAPES'";
	mysqli_query($con, $escape);
	if ( !empty($_SESSION['id']) && !empty($_SESSION['type']) && !empty($_SESSION['name']) && !empty($_SESSION['database']) ) {
		if ( $_POST ){
			if ( isset($_POST['data']) ) {
				$var = json_decode($_POST['data']);
				$type = htmlentities( $var->type->name );
				mysqli_select_db( $con, 'admin' );
				if ( strcmp( $type , 'list' ) == 0 ) {
					$serial = htmlentities( $var->values->serial );
					$serial = intval( $serial );
					$str = "select * from users where serial=".$serial;
					$query = mysqli_query( $con, $str );
					if ( $query ) {
						echo "
						<div id='adminList' class='scroll'>";
						if ( mysqli_num_rows( $query ) > 0 ) {
							echo "<table cellpadding='5px'>
									<thead>
										<tr>
											<th>User Id</th>
											<th>Name</th>
											<th>Email</th>
											<th></th>
										</tr>
									</thead>
									<tbody>
							";
							while ( $row = mysqli_fetch_array( $query ) ) {
								echo "
									<tr>
										<td>".$row['id']."</td>
										<td>".$row['name']."</td>
										<td>".$row['email']."</td>
										<td><a class='del' href='#' serial='".$row['id']."' >Delete</a></td>
									</tr>
								";
							}
							echo "</tbody></table>";
						}
						echo "</div>
						<div id='bottom'>
							<div id='add'><button class='button' id='addButton'>Add Admin</button></div>
							<div id='form'>
								<form action='processAdmin.php' method='POST'>
									<table><tr>
									<td><input type='number' placeholder='User Id' min='1' max='99999' id='username' /></td>
									<td><input type='text' placeholder='Name' id='name' maxlength='50' /></td>
									<td><input type='email' placeholder='Email' id='email' maxlength='100' /></td>
									</tr></table>
									<input type='hidden' id='serial' value='".$serial."' />
									<button id='submit' class='button'>Add Admin</button>
								</form>
							</div>
							<div id='bottomFeed'>Processing...</div>
						</div>
						";
					}
				} elseif (strcmp( $type, 'add' ) == 0 ) {
					$serial = htmlentities( $var->values->serial );
					$username = htmlentities( $var->values->username );
					$name = htmlentities( $var->values->name );
					$email = htmlentities( $var->values->email );
					$status = -1;
					$msg = "";
					if ( ($serial > 0 ) && is_numeric($serial) && is_numeric($username) && ( strlen($name) > 0 ) && ( strlen($name) < 51 ) && ( strlen($email) > 0 ) && ( strlen($email) < 101 ) ) {
						if ( validateEmail($email) ) {
							$str = "select * from users where id=".$username;
							$query = mysqli_query( $con, $str );
							if ( mysqli_num_rows( $query ) == 0 ) {
								if ( $username < 100000 ) {
									$password = rand_string(8);
									$password = encryptPassword( $username, 'password' );
									$str = "insert into users (id, name, email, serial, password, fpactive) values (".$username.", '".$name."', '".$email."', '".$serial."', '".$password."', 'y' )";
									#send email... a link which contains md5 hash of rand_string()
									$query = mysqli_query( $con, $str );
									if ( $query ) {
										$status = 0;
										$msg = "User added successfully!";
									} else {
										$status = 5;
										$msg = "User could not be added!";
									}
								} else {
									$msg = "Username must be less than 5 integers";
									$status = 4;
								}
							} else {
								$msg = "This username is taken.";
								$status = 3;
							}
						} else {
							$msg = "Invalid email.";
							$status = 2;
						}
					} else {
						$msg = "Inappropriate input provided.";
						$status = 1;
					}
					$response = array();
					array_push($response, array( 'status' => $status, 'msg' => $msg ));
					echo json_encode(array( 'response' => $response ));
				} elseif ( strcmp($type, 'delete') == 0 ) {
					$id = htmlentities( $var->values->id );
					$status = -1;
					$msg = "";
					if ( is_numeric($id) ) {
						$str = "delete from users where id=".$id;
						$query = mysqli_query( $con, $str );
						if ( $query ) {
							$status = 0;
							$msg = "Admin removed successfully.";
						} else {
							$status = 1;
							$msg = "Error removing admin.";
						}
					} else {
						$status = 2;
						$msg = "Inappropriate input provided.";
					}
					$response = array();
					array_push($response, array( 'status' => $status, 'msg' => $msg ));
					echo json_encode(array( 'response' => $response ));
				} elseif ( strcmp($type, 'avail' ) == 0 ) {
					$id = htmlentities( $var->values->id );
					$str = "select * from users where id=".$id;
					$query = mysqli_query( $con, $str );
					$status = -1;
					$msg = "";
					if ( $id < 100000 ) {
						if ( $query ) {
							if ( mysqli_num_rows( $query ) > 0 ) {
								$status = 1;
								$msg = "This username is taken.";
							} else {
								$status = 0;
								$msg = "This username is available.";
							}
						} else {
							$status = 2;
						}
					} else {
						$status = 3;
						$msg = "Maximum 5 numerics are allowed.";
					}
					$response = array();
					array_push($response, array( 'status' => $status, 'msg' => $msg ));
					echo json_encode(array( 'response' => $response ));
				}
			} else {
				header('Location: admin.php');
			}
		} else {
			header('Location: admin.php');
		}
	} else {
		header('Location: admin.php');
	}
	function validateEmail($email) {
		$regex = '/^[a-zA-z0-9][a-zA-z0-9\._\-&!?=#]*@[\w]+(\.\w{2,3})+$/';
		if ( preg_match( $regex, $email ) ) {
			return true;
		} else {
			return false;
		}
	}
	function rand_string( $length ) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$size = strlen( $chars );
		$str = "";
		for( $i = 0; $i < $length; $i++ ) {
			$str .= $chars[ rand( 0, $size - 1 ) ];
		}
		return $str;
	}
	function encryptPassword($serial, $password) {
		$password = md5($password);
		$temp_serial = sha1($serial);
		$options = [
		    'cost' => 11,
		    'salt' => hash('sha256', $temp_serial),
		];
		$password = password_hash( $password, PASSWORD_BCRYPT, $options );
		return $password;
	}
	mysqli_close( $con );
?>