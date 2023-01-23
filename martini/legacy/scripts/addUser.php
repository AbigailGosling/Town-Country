<?php
	require(__DIR__.'/../functions.php');
	
	$name = $mysqli->real_escape_string( request('name'));
	$email = $mysqli->real_escape_string( request('email'));
	$password = sha1($mysqli->real_escape_string( request('password')));
	

	$pages = implode(',', request('pages'));
	$view_intake_prices = $mysqli->real_escape_string( request('view_intake_prices'));
	$allow_override_salesman = $mysqli->real_escape_string( request('allow_override_salesman'));
	$user_type = $mysqli->real_escape_string( request('user_type'));

	$x = "INSERT into `users` (`name`,`email`,`pages`,`password`,`allow_override_salesman`,`view_intake_prices`,`user_type`) VALUES (?,?,?,?,?,?,?)";
	$y = prepareExecuteQuery($x,'sssssss',[$name,$email,$pages,$password,$allow_override_salesman,$view_intake_prices,$user_type]);
?>
<script>
	window.location = '../editUsers.php';
</script>
