<?php
	include_once('functions.php');
	
	
	$email = request()->input('email');
	$password = request()->input('password');
	
	
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