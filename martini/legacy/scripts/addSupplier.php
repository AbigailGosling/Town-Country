<?php
	require(__DIR__.'/../functions.php');
	
	$name = $mysqli->real_escape_string( request('name'));
	$postcode = $mysqli->real_escape_string( request('postcode'));
	$contact_name = $mysqli->real_escape_string( request('contact_name'));
	$contact_number = $mysqli->real_escape_string( request('contact_number'));
	$user_id = $mysqli->real_escape_string( request('user_id'));
	$internal_number = $mysqli->real_escape_string( request('internal_number'));
	
	$x = "INSERT into `supplier` (`name`,`postcode`,`contact_name`,`contact_number`,`user_id`,`internal_number`) VALUES (?,?,?,?,?,?)";
	$y = prepareExecuteQuery($x,'ssssss',[$name,$postcode,$contact_name,$contact_number,$user_id,$internal_number]);
?>
<script>
	window.location = '../manageSuppliers.php';
</script>
