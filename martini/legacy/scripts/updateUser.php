<?php
	require(__DIR__.'/../functions.php');
	
	$name = $mysqli->real_escape_string( request()->input('name'));
	$email = $mysqli->real_escape_string( request()->input('email'));
	$password = sha1($mysqli->real_escape_string( request()->input('password')));
	
	$pages = implode(',', request()->input('pages'));
	$view_intake_prices = $mysqli->real_escape_string( request()->input('view_intake_prices'));
	$allow_override_salesman = $mysqli->real_escape_string( request()->input('allow_override_salesman'));
	
	$user_type = $mysqli->real_escape_string( request()->input('user_type'));

	$id = request()->input('id');
	
	if(request()->input('password') != ''){
		prepareExecuteQuery("UPDATE `users` SET `name` = ?, `email` = ?, `pages` = ?, `allow_override_salesman` = ?, `view_intake_prices` = ?, `user_type` = ?, `password` = ? WHERE `id` = ? LIMIT 1",'sssssssi',[$name,$email,$pages,$allow_override_salesman,$view_intake_prices,$user_type,$password,$id]);
	}else{
		prepareExecuteQuery("UPDATE `users` SET `name` = ?, `email` = ?, `pages` = ?, `allow_override_salesman` = ?, `view_intake_prices` = ?, `user_type` = ? WHERE `id`  = ? LIMIT 1",'ssssssi',[$name,$email,$pages,$allow_override_salesman,$view_intake_prices,$user_type,$id]);
	}

?>
<script>
	window.location = '../editUsers.php?id=<?php echo $id; ?>';
</script>
