<?php
	require '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'connection.php';
	session_start();
	session_regenerate_id(true);
	$escape = "SET sql_mode='NO_BACKSLASH_ESCAPES'";
	mysqli_query($con, $escape);
	if ( !empty($_SESSION['id']) && !empty($_SESSION['type']) && !empty($_SESSION['name']) && !empty($_SESSION['database']) ) {
		if ( isset($_POST['serial']) ) {
			$serial = htmlentities($_POST['serial']);
			$serial = intval($serial);
			mysqli_select_db($con, "admin");
			$str = "select * from messdetails where serial=".$serial;
			$query = mysqli_query($con, $str) or die('operation failed.');
			if ( $query ) {
				if ( mysqli_num_rows( $query ) > 0 ) {
					$row = mysqli_fetch_array( $query );
					echo "
					<div id='form'>
						<form id='mess' action='processMess.php' method='POST'>
							<input name='serial' id='form_serial' type='hidden' value='".$row['serial']."' />
							<table cellspacing='5px' cellpadding='3px'>
								<tr>
									<td class='right'>Name:</td>
									<td><input id='name' class='feed' maxlength='50' len='50' rem='name' type='text' value='".$row['name']."'/>
									<p class='rem'><span id='rem_name' ></span></p></td>
								</tr>
								<tr>
									<td class='right'>Detail:</td>
									<td><textarea id='detail' maxlength='200' class='feed' len='200' rem='detail' rows='4' cols='40' >".$row['detail']."</textarea>
									<p class='rem'><span id='rem_detail' ></span></p></td>
								</tr>
								<tr>
									<td class='right'>Transaction Password:</td>
									<td><input id='pass' type='password' class='feed' len='20' rem='pass' maxlength='20' value='' />
									<p class='rem'><span id='rem_pass' ></span></p></td>
								</tr>
								<tr>
									<td class='right'>Members:</td>
									<td>".$row['members']."</td>
								</tr>
								<tr>
									<td class='right'>Enable:</td>
									<td><div id='status_parent'><input type='checkbox' name='status' id='status'"; if ( $row['mode'] === 'd' ) { echo "/><label for='status'> (Disabled)";}  else { echo "checked/><label for='status'> (Enabled)"; } echo "</label></div></td>
								</tr>
							</table>
							<p><button id='save' class='button1'>Save</button><button id='delete' class='button1'>Delete</button></p>
						</form>
					</div>
					<div id='image'>
						<form id='imageForm' action='saveImage.php' enctype='multipart/form-data' method='POST'>
							<div id='frame'><img src='..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."messImages".DIRECTORY_SEPARATOR.'thumbnails'.DIRECTORY_SEPARATOR.$row['image']."' /></div>
							<p><button class='button' id='change' title='Upload a new Image' onclick='document.getElementById(\"uploadImage\").click()'>Change Image</button><input id='uploadImage' type='file' /></p>
							<input name='serial' id='serial' type='hidden' value='".$row['serial']."' />
							<p id='upload'><button class='button'>Upload</button></p>
							<p id='progress'><progress value='0' max='100'></progress></p>
						</form>
					</div>
					";
				}
			}
		} else {
			header('Location: mess.php');
		}
	} else {
		header('Location: mess.php');
	}
	mysqli_close($con);
?>