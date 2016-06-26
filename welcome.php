<?php
	$con= mysqli_connect('localhost','root','220757');
	mysqli_select_db($con, 'admin') OR die('Could not connect to database');
	echo "
	<div id='disable'></div>
	<div id='dialog-overlay'>
		<div id='dialog-box'>
			<form action='login.php' method='POST'>
				<p><input help='Username is required.' placeholder='Username' type='text' id='username' name='username' /></p>
				<p><input help='Password is required.' placeholder='Password' type='password' id='password' name='password' /></p>
				<input type='hidden' name='type' id='type' value='' />
				<button type='submit' id='submit' name='submit'>Login</button>
				<p><a id='fp' href='#'>forgot password?</a></p>
			</form>
		</div>
		<div id='feedback'></div>
	</div>
	<div id='container'>
		<div id='header'>
			<h1>IET Mess Online</h1><span><i>one step towards transparency...</i></span>
			<a class='login' type='0' href='#'>Admin Login</a>
		</div>
		<div id='middle'>
			<div id='sidebar' class='scrollbar'>";
			$str = "select * from messDetails where mode='e'";
			$query=mysqli_query($con,$str) or die('Error');
			$src = '';
			if ( mysqli_num_rows($query) > 0 ) {
				echo "<div id='cssmenu'><ul>";
				$first = true;
				while ( $row = mysqli_fetch_array($query)) {
					echo "<li class='";if($first){ echo"active "; $src = 'messImages/'.$row['image']; $first = false; } echo "has-sub'><a image='messImages/".$row['image']."' href='#'>".$row['name']."</a>
					<ul><li>".$row['detail']."<a href='#' class='login' type='".$row['serial']."'>Login</a></li></ul>
					</li>";
				}
				echo"</ul></div>";
			}
			echo"</div>
			<div id='showcase'>
			<img src='".$src."' width='820px' height='425px' />
			<span></span>
			</div>
		</div>
	</div>
	";
	mysqli_close($con);
?>