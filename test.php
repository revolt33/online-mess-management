<?php
	// $pass = "mynpassword";
	// $shapass = sha1($pass);
	// $shapass = md5($pass);
	// $shapass = hash('sha256', $pass);
	// echo $shapass." length=".strlen($shapass);
	/* Method to calculate encrypted passwords using bcrypt */
	
	// $options = [
	//     'cost' => 11,
	//     // 'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM),
	//     'salt' => hash('sha256', '100'),
	// ];
	// $password = password_hash($pass, PASSWORD_BCRYPT, $options);
	// echo $password." length=".strlen($password);
	
	/* A method to calculate what cost should one use. */
	/*$timeTarget = 0.05; // 50 milliseconds 

	$cost = 8;
	do {
	    $cost++;
	    $start = microtime(true);
	    $options = [
		    'cost' => $cost,
		    'salt' => hash('sha256', '1'),
		];
	    password_hash("test", PASSWORD_BCRYPT, $options);
	    $end = microtime(true);
	} while (($end - $start) < $timeTarget);

	echo "Appropriate Cost Found: " . $cost . "\n";*/
	echo md5("password");
?>