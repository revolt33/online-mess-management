<?php
	function checkMess( $con, $mess ) {
		mysqli_select_db( $con, "admin" );
		$str = "select mode from messdetails where mess='".$mess."'";
		$query = mysqli_query( $con, $str );
		if ( $query ) {
			$row = mysqli_fetch_array( $query );
			if ( $row[0] == 'e' )
				return true;
			else
				return false;
		} else
			return false;
	}
?>