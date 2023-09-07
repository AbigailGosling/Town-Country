<?php
	require(__DIR__.'/../functions.php');
	
	$name = request()->input('name');
	$email = request()->input('email');
	$password = sha1(request()->input('password'));
	

	$pages = implode(',', request()->input('pages'));
	$view_intake_prices = request()->input('view_intake_prices');
	$allow_override_salesman = request()->input('allow_override_salesman');
	$user_type = request()->input('user_type');

	$x = "INSERT into `users` (`name`,`email`,`pages`,`password`,`allow_override_salesman`,`view_intake_prices`,`user_type`) VALUES (?,?,?,?,?,?,?)";
	$y = prepareExecuteQuery($x,'sssssss',[$name,$email,$pages,$password,$allow_override_salesman,$view_intake_prices,$user_type]);
?>
<script>
	window.location = '../editUsers.php';
</script>
