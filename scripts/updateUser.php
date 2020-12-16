<?php
	require('../functions.php');
	
	$name = mysqli_real_escape_string($conn, $_POST['name']);
	$email = mysqli_real_escape_string($conn, $_POST['email']);
	$password = sha1(mysqli_real_escape_string($conn, $_POST['password']));
	
	$pages = implode(',', $_POST['pages']);
	$view_intake_prices = mysqli_real_escape_string($conn, $_POST['view_intake_prices']);
	
	$id = $_POST['id'];
	
	if($_POST['password'] != ''){
		$x = "UPDATE `users` SET name='$name', email='$email', pages='$pages', view_intake_prices=$view_intake_prices, password='$password' WHERE id='$id' LIMIT 1";
	}else{
		$x = "UPDATE `users` SET name='$name', email='$email', pages='$pages', view_intake_prices=$view_intake_prices WHERE id='$id' LIMIT 1";
	}
	
	$y = mysqli_query($conn, $x);
?>
<script>
	window.location = '../editUsers.php?id=<?php echo $id; ?>';
</script>
