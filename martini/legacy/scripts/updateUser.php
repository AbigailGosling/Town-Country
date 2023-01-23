<?php
	require(__DIR__.'/../functions.php');
	
	$name = $mysqli->real_escape_string( request('name'));
	$email = $mysqli->real_escape_string( request('email'));
	$password = sha1($mysqli->real_escape_string( request('password')));
	
	$pages = implode(',', request('pages'));
	$view_intake_prices = $mysqli->real_escape_string( request('view_intake_prices'));
	$allow_override_salesman = $mysqli->real_escape_string( request('allow_override_salesman'));
	
	$user_type = $mysqli->real_escape_string( request('user_type'));

	$id = request('id');
	
	if(request('password') != ''){
		prepareExecuteQuery("UPDATE `users` SET `name` = ?, `email` = ?, `pages` = ?, `allow_override_salesman` = ?, `view_intake_prices` = ?, `user_type` = ?, `password` = ? WHERE `id` = ? LIMIT 1",'sssssssi',[$name,$email,$pages,$allow_override_salesman,$view_intake_prices,$user_type,$password,$id]);
	}else{
		prepareExecuteQuery("UPDATE `users` SET `name` = ?, `email` = ?, `pages` = ?, `allow_override_salesman` = ?, `view_intake_prices` = ?, `user_type` = ? WHERE `id`  = ? LIMIT 1",'ssssssi',[$name,$email,$pages,$allow_override_salesman,$view_intake_prices,$user_type,$id]);
	}

?>
<script>
	window.location = '../editUsers.php?id=<?php echo $id; ?>';
</script>
