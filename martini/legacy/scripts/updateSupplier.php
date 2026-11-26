<?php
    use App\Models\ClientAddress;
    use App\Models\ClientType;

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

    $ca = ClientAddress::where('client_id', $id)
        ->where('address_id', 1)
        ->where('client_type', ClientType::SUPPLIER->value)
        ->first();
    if (!$ca) {
        $ca = new ClientAddress();
        $ca->client_id = $id;
        $ca->address_id = 1;
        $ca->client_type = ClientType::SUPPLIER->value;
        $ca->site_id = 1;
        $ca->allowed_vehicle_types = '';
        $ca->require_tail_lift = false;
        $ca->geocoding_tried = 0;
        $ca->collection = false;
        $ca->lat = null;
        $ca->lon = null;
    }
    $ca->address_1 = $address_1;
    $ca->address_2 = $address_2;
    $ca->address_3 = $address_3;
    $ca->address_4 = $address_4;
    $ca->postcode = $postcode;
    $ca->address_number = $contact_number;
    $ca->save();
?>
<script>
	window.location = '../manageSuppliers.php';
</script>
