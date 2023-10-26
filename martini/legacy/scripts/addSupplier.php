<?php
	require(__DIR__.'/../functions.php');
	
	$name = request()->input('name');
	$postcode = request()->input('postcode');
	$contact_name = request()->input('contact_name');
	$contact_number = request()->input('contact_number');
	$user_id = request()->input('user_id');
	$internal_number = request()->input('internal_number');
	$enabled = (int)request()->input('disabled',0);
	
	$x = "INSERT into `supplier` (`name`,`postcode`,`contact_name`,`contact_number`,`user_id`,`internal_number`,`disabled`) VALUES (?,?,?,?,?,?,?)";
	$y = prepareExecuteQuery($x,'ssssssi',[$name,$postcode,$contact_name,$contact_number,$user_id,$internal_number,$enabled]);
?>
<script>
	window.location = '../manageSuppliers.php';
</script>
