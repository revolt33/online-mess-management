<?php
	require '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'connection.php';
	require '..'.DIRECTORY_SEPARATOR.'utility.php';
	session_start();
	session_regenerate_id(true);
	if ( checkAuthToken() && checkMode( $con ) ) {
		mysqli_select_db( $con, 'admin' );
		$str = "select name, serial from messdetails where mess='".$_SESSION['database']."'";
		$query = mysqli_query( $con, $str );
		$row = mysqli_fetch_array( $query );
		$mess = $row[0];
		$serial = $row[1];
		$str = "select id, name from users where serial=".$serial;
		$query = mysqli_query( $con, $str );
		$users = array();
		while ( $row = mysqli_fetch_array( $query ) )
			$users[$row['id']] = $row['name'];
		echo "
		<!DOCTYPE html>
		<html>
		<head>
			<title>Expenses | ".$mess."</title>
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
			<div id='load-window'><div id='floating-bar'></div></div>
			<div id='dialog-overlay'>
				<div id='transaction-box'>
					<div id='transaction-head'>Enter Transaction Password</div>
					<div id='transaction-content'><input class='prompt-input' id='password' type='password' maxlength='20' /></div>
					<div id='transaction-foot'><p><input type='checkbox' id='check' />Remember password for this session.</p><p><button class='submit' id='submit'>Submit</button></p></div>
				</div>
				<div id='dialog-box'>
					<div id='dialog-head'></div>
					<div id='dialog-content'></div>
					<div id='dialog-foot'><button id='yes'>Yes</button>&nbsp&nbsp&nbsp<button id='no'>No</button></div>
				</div>
			</div>
			<div id='image-viewer'><img /><div id='close-button'>+</div></div>
			<div id='hovertext'></div>
			<div id='disable'></div>
			<div id='feedback'></div>
			<div id='header'>
				<h1>IET Mess Online</h1><span id='tagline'><i>one step towards transparency...</i></span><br /><span>".$mess."</span>
				<a id='logout' href='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."logout.php'>Logout</a>
			</div>
			<nav>
				<a href='..".DIRECTORY_SEPARATOR."accounts'>Accounts</a>
				<a href='' class='slide'>Expenses</a>
				<a href='..".DIRECTORY_SEPARATOR."servings' class='slide'>Servings</a>
				<a href='..".DIRECTORY_SEPARATOR."tuning' class='slide'>Tuning</a>
				<a href='..".DIRECTORY_SEPARATOR."notice' class='slide'>Notice</a>
				<a href='..".DIRECTORY_SEPARATOR."balance' class='slide'>Balance</a>
			</nav>
			<div id='middle'>
				<div id='root_container'>
					<div class='tab_container'>
						<div class='tab selected' serial='11'>Add</div><div class='tab border' serial='12'>View</div>
					</div>
					<div class='content_container'>
						<div class='content' serial='11'>
							<div class='branch_container'>
								<div class='tab_container'>
									<div class='tab selected' serial='21'>Wage</div>
									<div class='tab border' serial='22'>Expenses</div>
									<div class='tab border' serial='23'>Miscellaneous</div>
								</div>
								<div class='content_container'>
									<div class='content' serial='21'>
										<table cellpadding='5px'>
											<tr>
												<td>Employee Name:</td>
												<td>
												<select id='name'>";
													mysqli_select_db($con, $_SESSION['database']);
													$str = "select * from employee where status='w'";
													$query = mysqli_query($con, $str);
													$state = true;
													$salary = 0;
													$post = "";
													while ($row = mysqli_fetch_array($query)) {
														if ( $state ) {
															$salary = $row['salary'];
															$post = $row['post'];
															$state = false;
														}
														echo "<option value='".$row['id']."'>".$row['name']."</option>";
													}
												echo "</select>
												</td>
											</tr>
											<tr>
												<td><span class='right'>Salary:</span></td>
												<td><span id='salary'>".$salary."</span></td>
											</tr>
											<tr>
												<td><span class='right'>Post:</span></td>
												<td><span id='post'>".$post."</span></td>
											</tr>
											<tr>
												<td><span class='right'>Comment:</span></td>
												<td><textarea class='notify' id='wage_comment' rows='4' cols='25' maxlength='100' placeholder='Optional...'></textarea></td>
											</tr>
											<tr>
												<td></td>
												<td><p class='feed'>&nbsp<span id='wage_comment_feed'></span></p></td>
											</tr>
											<tr>
												<td><span class='right'>Apply to All:</span></td>
												<td><span id='wage_hostler_div'><input type='checkbox' class='hostler' id='wage_hostler' /><label for='wage_hostler'>(No)</label></span></td>
											</tr>
											<tr>
												<td></td>
												<td><button class='button' id='add_wage'>Add</button></td>
											</tr>
										</table>
									</div>
									<div class='content' serial='22'>
										<table cellpadding='5px'>
											<tr>
												<td><span class='right'>Title:</span></td>
												<td><input class='notify' type='text' id='exp_title' maxlength='50' /></td>
											</tr>
											<tr>
												<td></td>
												<td><p class='feed'>&nbsp<span id='exp_title_feed'></span></p></td>
											</tr>
											<tr>
												<td><span class='right'>Amount:</span></td>
												<td><input type='number' min='0' id='exp_amount' /></td>
											</tr>
											<tr>
												<td><span class='right'>Apply to All:</span></td>
												<td><span id='exp_hostler_div'><input type='checkbox' class='hostler' id='exp_hostler' /><label for='exp_hostler'>(No)</label></span></td>
											</tr>
											<tr>
											<form id='exp_image_form' action='saveImage.php' enctype='multipart/from-data' method='POST'>
												<input type='hidden' id='exp_transaction' value='' />
												<td><span class='right'>Image:</span></td>
												<td><input type='file' id='exp_image' /><button class='button' id='exp_image_select'>Select Image</button></td>
											</form>
											</tr>
											<tr>
												<td></td>
												<td><button id='add_exp' class='button'>Add</button></td>
											</tr>
										</table>
										<div class='image_feed' id='exp_image_feed'><img width='220' height='260' /></div>
									</div>
									<div class='content' serial='23'>
										<table cellpadding='5px'>
											<tr>
												<td><span class='right'>Title:</span></td>
												<td><input class='notify' type='text' id='misc_title' maxlength='50' /></td>
											</tr>
											<tr>
												<td></td>
												<td><p class='feed'>&nbsp<span id='misc_title_feed'></span></p></td>
											</tr>
											<tr>
												<td><span class='right'>Amount:</span></td>
												<td><input type='number' min='0' id='misc_amount' /></td>
											</tr>
											<tr>
												<td><span class='right'>Comment:</span></td>
												<td><textarea class='notify' id='misc_comment' rows='4' cols='25' maxlength='100' placeholder='Optional...'></textarea></td>
											</tr>
											<tr>
												<td></td>
												<td><p class='feed'>&nbsp<span id='misc_comment_feed'></span></p></td>
											</tr>
											<tr>
												<td><span class='right'>Apply to All:</span></td>
												<td><span id='misc_hostler_div'><input type='checkbox' class='hostler' id='misc_hostler' /><label for='misc_hostler'>(No)</label></span></td>
											</tr>
											<tr>
												<td></td>
												<td><button id='add_misc' class='button'>Add</button></td>
											</tr>
										</table>
									</div>
								</div>
							</div>
						</div>
						<div class='content' serial='12'>
							<div class='branch_container'>
								<div class='tab_container'>
									<div class='tab selected' serial='31'>Wage</div>
									<div class='tab border' serial='32'>Expenses</div>
									<div class='tab border' serial='33'>Miscellaneous</div>
								</div>
								<div class='content_container' id='data'>
									<div class='content' serial='31'>
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
												echo "
										<div class='row'>
											<div class='hover' hovertext='".$row['comment']."'></div>
											<div class='column'>".$row['name']."</div>
											<div class='column'>".$row['amount']."</div>
											<div class='column'>".date('d F Y', strtotime($row['date']))."</div>
											<div class='column'>".$users[$row['uid']]."</div>
											<div class='column'>"; if ($row['all_users'] == 'y') echo "Everyone"; else echo "Hostlers"; echo "</div>
										</div>
										";
											}
										}
									echo "
									</div>
									<div class='content' serial='32'>
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
										echo "
										<div class='row'>
											<div class='hover image' location='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."hostels".DIRECTORY_SEPARATOR."hostel".$serial.DIRECTORY_SEPARATOR.$row['image']."' hovertext='Click to see image.'></div>
											<div class='column'>".$row['title']."</div>
											<div class='column'>".$row['amount']."</div>
											<div class='column'>".date('d F Y', strtotime($row['date']))."</div>
											<div class='column'>".$users[$row['uid']]."</div>
											<div class='column'>"; if ($row['all_users'] == 'y') echo "Everyone"; else echo "Hostlers"; echo "</div>
										</div>
										";
									}
								}
									echo "
									</div>
									<div class='content' serial='33'>
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
										echo "
										<div class='row'>
											<div class='hover' hovertext='".$row['comment']."'></div>
											<div class='column'>".$row['title']."</div>
											<div class='column'>".$row['amount']."</div>
											<div class='column'>".date('d F Y', strtotime($row['date']))."</div>
											<div class='column'>".$users[$row['uid']]."</div>
											<div class='column'>"; if ($row['all_users'] == 'y') echo "Everyone"; else echo "Hostlers"; echo "</div>
										</div>
										";
									}
								}
									echo "
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</body>
		</html>
		";
	} else {
		header('Location: ..'.DIRECTORY_SEPARATOR);
	}
	mysqli_close( $con );
?>