<?php
	require(__DIR__.'/../functions.php');
	
	$id = $mysqli->real_escape_string( request('id'));
	$name = $mysqli->real_escape_string( request('name'));
	$postcode = $mysqli->real_escape_string( request('postcode'));
	$contact_name = $mysqli->real_escape_string( request('contact_name'));
	$contact_number = $mysqli->real_escape_string( request('contact_number'));
	$user_id = $mysqli->real_escape_string( request('user_id'));
	$internal_number = $mysqli->real_escape_string( request('internal_number'));
	
	$x = "UPDATE `supplier` SET `name`= ?, 
		`postcode`= ?, 
		`contact_name`= ?, 
		`contact_number`= ?,
		`user_id`=?, 
		`internal_number`= ? 
		WHERE id = ?";
	$y = prepareExecuteQuery($x,'ssssisi',[$name,$postcode,$contact_name,$contact_number,$user_id,$internal_number,$id]);
?>
<script>
	window.location = '../manageSuppliers.php';
</script>