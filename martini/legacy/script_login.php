<?php
	include_once('functions.php');
	
	
	$email = $mysqli->real_escape_string( request('email'));
	$password = $mysqli->real_escape_string( request('password'));
	
	
	$result = check_login($email, $password);
	
	if($result == 1){
	?>
	<script>
		window.location.href = '/legacy/menu.php';
	</script>
	<?php
	}else{
	?>
	<script>
		window.location.href = '/index.php';
	</script>
	<?php
	}
	// echo $password = sha1($mysqli->real_escape_string( 'password'));
?>