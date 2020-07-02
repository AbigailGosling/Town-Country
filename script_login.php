<?php
	include_once('functions.php');
	
	
	$email = mysqli_real_escape_string($conn, $_POST['email']);
	$password = mysqli_real_escape_string($conn, $_POST['password']);
	
	
	$result = check_login($email, $password);
	
	if($result == 1){
	?>
	<script>
		window.location.href = '/menu.php';
	</script>
	<?php
	}else{
	?>
	<script>
		window.location.href = '/index.php';
	</script>
	<?php
	}
	// echo $password = sha1(mysqli_real_escape_string($conn, 'password'));
?>