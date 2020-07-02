<?php
	require('../functions.php');
	
	$name = mysqli_real_escape_string($conn, $_POST['name']);
	$email = mysqli_real_escape_string($conn, $_POST['email']);
	$type = mysqli_real_escape_string($conn, $_POST['type']);
	$password = sha1(mysqli_real_escape_string($conn, $_POST['password']));
	
	$x = "INSERT into `users` (name,email,type,password) VALUES ('$name','$email','$type','$password')";
	$y = mysqli_query($conn, $x);
?>
<script>
	window.location = '../editUsers.php';
</script>
