<?php
	require(__DIR__.'/../functions.php');

	$id = request()->input('id');
	$name = request()->input('name');
	$postcode = request()->input('postcode');
    $address_1 = request()->input('address_1');
    $address_2 = request()->input('address_2');
    $address_3 = request()->input('address_3');
    $address_4 = request()->input('address_4');
    $email = request()->input('email');
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
		`disabled` = ?,
        address_1 = ?,
        address_2 = ?,
        address_3 = ?,
        address_4 = ?,
        email = ?
		WHERE id = ?";
	$y = prepareExecuteQuery($x,'ssssisisssssi',[$name,$postcode,$contact_name,$contact_number,$user_id,$internal_number,$enabled,$address_1,$address_2,$address_3,$address_4,$email,$id]);
?>
<script>
	window.location = '../manageSuppliers.php';
</script>
