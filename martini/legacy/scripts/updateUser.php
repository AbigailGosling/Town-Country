<?php
	require(__DIR__.'/../functions.php');
	
	$name = request()->input('name');
	$email = request()->input('email');
	$password = sha1(request()->input('password'));
	
	$pages = implode(',', request()->input('pages'));
	$view_intake_prices = request()->input('view_intake_prices');
	$allow_override_salesman = request()->input('allow_override_salesman');
	
	$user_type = request()->input('user_type');

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
