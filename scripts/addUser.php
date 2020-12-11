<?php
	require('../functions.php');
	
	$name = mysqli_real_escape_string($conn, $_POST['name']);
	$email = mysqli_real_escape_string($conn, $_POST['email']);
	$password = sha1(mysqli_real_escape_string($conn, $_POST['password']));
	

	$pages = implode(',', $_POST['pages']);


	$x = "INSERT into `users` (name,email,pages,password) VALUES ('$name','$email','$pages','$password')";
	$y = mysqli_query($conn, $x);
?>
<script>
	window.location = '../editUsers.php';
</script>
