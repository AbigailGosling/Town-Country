<?php
	require('../functions.php');
	
	$name = mysqli_real_escape_string($conn, $_POST['name']);
	$email = mysqli_real_escape_string($conn, $_POST['email']);
	$password = sha1(mysqli_real_escape_string($conn, $_POST['password']));
	

	$pages = implode(',', $_POST['pages']);
	$view_intake_prices = mysqli_real_escape_string($conn, $_POST['view_intake_prices']);
	$user_type = mysqli_real_escape_string($conn, $_POST['user_type']);

	$x = "INSERT into `users` (name,email,pages,password,view_intake_prices,user_type) VALUES ('$name','$email','$pages','$password',$view_intake_prices,'$user_type')";
	$y = mysqli_query($conn, $x);
?>
<script>
	window.location = '../editUsers.php';
</script>
