<?php
	session_start();
	session_regenerate_id(true);
	if ( !empty($_SESSION['id']) && !empty($_SESSION['type']) && !empty($_SESSION['name']) && !empty($_SESSION['database']) ) {
		if ( $_POST ) {
			$mess = $_POST['request'];
			echo "
			<div id='form'>
				<form id='mess' action='processMess.php' method='POST'>
					<table cellspacing='5px' cellpadding='3px'>
						<tr>
							<td class='right'>Name:</td>
							<td><input id='name' class='feed' maxlength='50' len='50' rem='name' type='text' value=''/>
							<p class='rem'><span id='rem_name' ></span></p></td>
						</tr>
						<tr>
							<td class='right'>Detail:</td>
							<td><textarea id='detail' maxlength='200' class='feed' len='200' rem='detail' rows='4' cols='40' ></textarea>
							<p class='rem'><span id='rem_detail' ></span></p></td>
						</tr>
						<tr>
							<td class='right'>Transaction Password:</td>
							<td><input id='pass' type='password' class='feed' len='20' rem='pass' maxlength='20' value='' />
							<p class='rem'><span id='rem_pass' ></span></p></td>
						</tr>
					</table>
					<p><button id='add' class='button1'>Add Mess</button></p>
				</form>
			</div>
			<div id='image'>
				<form id='imageForm' action='saveImage.php' enctype='multipart/form-data' method='POST'>
					<div id='frame'><img /></div>
					<p><button class='button' id='change' title='Upload a new Image' onclick='document.getElementById(\"uploadImage\").click()'>Change Image</button><input id='uploadImage' type='file' /></p>
					<input name='serial' id='serial' type='hidden' value='' />
				</form>
			</div>
			";
		} else {
			header('Location: mess.php');
		}
	} else {
		header('Location: mess.php');
	}
?>