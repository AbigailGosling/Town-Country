<?php
	require(__DIR__.'/../functions.php');
	
	$id = request()->input('id');
	$name = request()->input('name');
	$postcode = request()->input('postcode');
	$contact_name = request()->input('contact_name');
	$contact_number = request()->input('contact_number');
	$user_id = request()->input('user_id');
	$internal_number = request()->input('internal_number');
	$enabled = (int)request()->input('disabled',0);
	
	$x = "UPDATE `supplier` SET `name`= ?, 
		`postcode`= ?, 
		`contact_name`= ?, 
		`contact_number`= ?,
		`user_id`=?, 
		`internal_number`= ?, 
		`disabled` = ?
		WHERE id = ?";
	$y = prepareExecuteQuery($x,'ssssisii',[$name,$postcode,$contact_name,$contact_number,$user_id,$internal_number,$enabled,$id]);
?>
<script>
	window.location = '../manageSuppliers.php';
</script>