<?php
	function getContent($con) {
		mysqli_select_db( $con, $_SESSION['database'] );
		$days = array( 
			1 => 'Sunday',
			2 => 'Monday',
			3 => 'Tuesday',
			4 => 'Wednesday',
			5 => 'Thursday',
			6 => 'Friday',
			7 => 'Saturday'
		 );
		$result = "<div id='meals'><h2>Meals:</h2>";
		$str = "select id, meal, name, day from weeklyoff, meals where weeklyoff.status='a' and meal=id";
		$query = mysqli_query( $con, $str );
		$offs = "";
		while ( $row = mysqli_fetch_array( $query ) ) {
			$offs .= "
			<div class='item weekly_off' day='".$row['day']."' meal='".$row['meal']."'>
				<div class='remove rm_weekly_off'>Remove</div>
				".$row['name'].", ".$days[$row['day']]."
			</div>";
		}
		$str = "select * from meals where status='a'";
		$query = mysqli_query( $con, $str );
		$meals_beg = "
			<select id=";
		$meals_end = "";
		while ( $row = mysqli_fetch_array( $query ) ) {
			$result .= "<div class='item meal' start=".$row['start']." end=".$row['end']." cost=".$row['cost']." pts=".$row['points']." serial='".$row['id']."'><div class='remove rm_meal'>Remove</div>".$row['name']."</div>";
			$meals_end .= "<option value='".$row['id']."'>".$row['name']."</option>";
		}
		$meals_end .= "</select>";
		$offs .= $meals_beg."'select_offs'>".$meals_end;
		$result .= "<div class='add' id='add_meal_input'>&#x271a;</div></div>";
		$result .= "<div id='extras'><h2>Extras:</h2>"; 
		$str = "select * from extras where status='a'";
		$query = mysqli_query( $con, $str );
		while ( $row = mysqli_fetch_array( $query ) ) {
			$result .= "<div class='item extra' cost='".$row['cost']."' serial='".$row['id']."'><div class='remove rm_extra'>Remove</div>".$row['name']."</div>";
		}
		$response = getExpiration( $con );
		$result .= "<div class='add' id='add_extra_input'>&#x271a;</div></div>
		<div id='offs'>
			<h2>Weekly off:</h2>
			".$offs."
			<div class='add' id='add_weekly_off_input'>&#x271a;</div>
		</div>
		<div id='scheduled_off'>
			<h2>Scheduled off:</h2>
			<a href='#' id='view_scheduled_off'>View all</a>
			<div id='pull'>
			<div id='scheduled_off_edit'>
				<table cellpadding='5px' cellspacing='2px'>
					<tr>
						<td></td>
						<th>From</th>
						<th>To</th>
					</tr>
					<tr>
						<td>Meal:</td>
						<td>".$meals_beg."'select_scheduled_off_from'>".$meals_end."</td>
						<td>".$meals_beg."'select_scheduled_off_to'>".$meals_end."</td>
					</tr>
					<tr>
						<td>Date:</td>
						<td><input type='text' id='scheduled_off_date_from' /></td>
						<td><input type='text' id='scheduled_off_date_to' /></td>
					</tr>
					<tr>
						<th colspan='3'><button id='scheduled_off_button' class='button'>Save</button></th>
					</tr>
				</table>
			</div>
			</div>
			<div class='add' id='add_scheduled_off_input'>&#x271a;</div>
		</div>
		<div id='remember_menu'>
			<div id='remember'>
				<h2>Remember me: &nbsp&nbsp</h2><input type='checkbox' id='remember_me' "; if( $response['remember'] == 'y' ) { $result .= "checked"; } $result .= "/><span id='remaining'>";if ( $response['remaining'] >= 0 ) {$result .= $response['remaining']." days remaining...";} else { $result .= "Expired..."; } $result .= "</span>&nbsp&nbsp<a href='#' id='refresh'>Refresh</a>
			</div>
			<a href='#' id='design_menu' >Design Menu</a>
		</div>
		<div id='allowed_off'>
			<h2>Maximum Allowed Off:&nbsp&nbsp</h2><input id='allowed_off_input' type='number' min='0' />
			<button id='allowed_off_button' class='button'>Save</button>
		</div>
		<div id='change_password'>
			<h2>Change Password:&nbsp&nbsp</h2><input type='password' id='old_password' placeholder='Old Password' /><input type='password' id='new_password_1' placeholder='New Password' /><input type='password' id='new_password_2' placeholder='Repeat New Password' /><button id='change_password_button' class='button'>Change</button>
		</div>
		";
		return $result;
	}
	function getExpiration( $con ) {
		mysqli_select_db( $con, 'admin' );
		$str = "select remember, upto from users where id=".$_SESSION['id'];
		$query = mysqli_query( $con, $str );
		$row = mysqli_fetch_array( $query );
		$remember = $row[0];
		$today = new DateTime('now');
		$upto = strtotime($row['upto']);
		$valid = new DateTime();
		$valid->setTimestamp($upto);
		$diff = date_diff($today, $valid);
		$remaining = (string)$diff->format("%R%a");
		return array( 'remaining' => intval($remaining), 'remember' => $remember );
	}
?>