<?php
	require(__DIR__.'/../functions.php');
	
	$name = $mysqli->real_escape_string( request()->input('name'));
	$email = $mysqli->real_escape_string( request()->input('email'));
	$password = sha1($mysqli->real_escape_string( request()->input('password')));
	

	$pages = implode(',', request()->input('pages'));
	$view_intake_prices = $mysqli->real_escape_string( request()->input('view_intake_prices'));
	$allow_override_salesman = $mysqli->real_escape_string( request()->input('allow_override_salesman'));
	$user_type = $mysqli->real_escape_string( request()->input('user_type'));

	$x = "INSERT into `users` (`name`,`email`,`pages`,`password`,`allow_override_salesman`,`view_intake_prices`,`user_type`) VALUES (?,?,?,?,?,?,?)";
	$y = prepareExecuteQuery($x,'sssssss',[$name,$email,$pages,$password,$allow_override_salesman,$view_intake_prices,$user_type]);
?>
<script>
	window.location = '../editUsers.php';
</script>
