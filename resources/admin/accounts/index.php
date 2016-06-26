<?php
	require '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'connection.php';
	require '..'.DIRECTORY_SEPARATOR.'utility.php';
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
				<title>Accounts | ".$mess."</title>
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
					<div id='money-box'>
						<div id='money-head'>Enter amount of money</div>
						<div id='money-content'><input class='prompt-input' id='amount' type='number' min='0' /></div>
						<div id='money-foot'><p><button class='submit' id='add'>Submit</button></p></div>
					</div>
				</div>
				<div id='disable'></div>
				<div id='feedback'></div>
				<div id='header'>
					<h1>IET Mess Online</h1><span id='tagline'><i>one step towards transparency...</i></span><br /><span>".$mess."</span>
					<a id='logout' href='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."logout.php'>Logout</a>
				</div>
				<nav>
					<a href=''>Accounts</a>
					<a href='..".DIRECTORY_SEPARATOR."expense' class='slide'>Expenses</a>
					<a href='..".DIRECTORY_SEPARATOR."servings' class='slide'>Servings</a>
					<a href='..".DIRECTORY_SEPARATOR."tuning' class='slide'>Tuning</a>
					<a href='..".DIRECTORY_SEPARATOR."notice' class='slide'>Notice</a>
					<a href='..".DIRECTORY_SEPARATOR."balance' class='slide'>Balance</a>
				</nav>
				<div id='middle'>
					<div id='container'>
						<div id='tab_container'>
							<div class='tab selected' serial='1'>View Accounts</div><div class='tab border' serial='2'>Add account</div><div class='tab border' serial='3'>Add Employee</div><div class='tab border' serial='4'>View Employees</div>
						</div>
						<div id='content_container'>
							<div class='content' id='userList' serial='1'>
							</div>
							<div class='content' id='account_form' serial='2'>
								<input type='hidden' value='' id='serial' />
								<table cellpadding='5px'>
									<tr>
										<td><span class='right'>User ID:</span></td>
										<td><input id='username' type='number' min='100' max='100000' /></td>
										<td rowspan='8'><div id='image_div'><form id='image_form' >
											<div id='frame'><img /></div>
											<span><button id='change' class='button'>Select Image</button></span><input type='file' id='image' /></form>
										</div></td>
									</tr>
									<tr>
										<td><span class='right'>Name:</span></td>
										<td><input type='text' maxlength='50' id='name' /></td>
									</tr>
									<tr>
										<td><span  class='right'>Email:</span></td>
										<td><input type='email' maxlength='50' id='email' />
									</tr>
									<tr>
										<td><span class='right'>Gender:</span></td>
										<td><select id='gender'><option value='m'>Male</option><option value='f'>Female</option></select></td>
									</tr>
									<tr>
										<td><span class='right'>DOB:</span></td>
										<td><input type='text' id='dob' /></td>
									</tr>
									<tr>
										<td><span class='right'>Roll number:</span></td>
										<td><input min='0' type='number' id='roll' /></td>
									</tr>
									<tr>
										<td><span class='right'>Room number:</span></td>
										<td><input min='0' type='number' id='room' /></td>
									</tr>
									<tr>
										<td><span class='right'>Mobile number:</span></td>
										<td><input min='0' type='number' id='mobile' /></td>
									</tr>
									<tr>
										<td><span class='right'>Initial Balance:</span></td>
										<td><input min='0' type='number' id='balance' /></td>
									</tr>
									<tr>
										<td><span class='right'>Subsidized:</span></td>
										<td><span><input type='radio' value='y' name='subsidized' />Yes <input type='radio' name='subsidized' value='n' checked/>No</span></td>
									</tr>
									<tr>
										<td></td>
										<th><button id='add_user' class='button' />Add User</button></th>
										<td></td>
									</tr>
								</table>
							</div>
							<div class='content' serial='3'>
								<table cellpadding='5px'>
									<tr>
										<td><span class='right'>Name:</span></td>
										<td><input id='emp_name' type='text' maxlength='50' /></td>
									</tr>
									<tr>
										<td><span class='right'>Post:</span></td>
										<td><input id='emp_post' type='text' maxlength='30' /></td>
									</tr>
									<tr>
										<td><span class='right'>Salary:</span></td>
										<td><input id='emp_salary' type='number' min='0' /></td>
									</tr>
									<tr>
										<td></td>
										<td colspan='2'><button class='button' id='add_emp'>Add Employee</button></td>
									</tr>
								</table>
							</div>
							<div class='content' serial='4' id='empList'>
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