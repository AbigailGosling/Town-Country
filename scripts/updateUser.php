<?php
	require('../functions.php');
	
	$name = mysqli_real_escape_string($conn, $_POST['name']);
	$email = mysqli_real_escape_string($conn, $_POST['email']);
	$type = mysqli_real_escape_string($conn, $_POST['type']);
	$password = sha1(mysqli_real_escape_string($conn, $_POST['password']));
	
	
	$id = $_POST['id'];
	
	if($_POST['password'] != ''){
		$x = "UPDATE `users` SET name='$name', email='$email', type='$type', password='$password' WHERE id='$id' LIMIT 1";
	}else{
		$x = "UPDATE `users` SET name='$name', email='$email', type='$type' WHERE id='$id' LIMIT 1";
	}
	
	$y = mysqli_query($conn, $x);
?>
<script>
	window.location = '../editUsers.php?id=<?php echo $id; ?>';
</script>
