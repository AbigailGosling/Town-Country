<?php
    use App\Models\ClientAddress;
    use App\Models\ClientType;

	require(__DIR__.'/../functions.php');

	$name = request()->input('name');
	$postcode = request()->input('postcode');
	$contact_name = request()->input('contact_name');
	$contact_number = request()->input('contact_number');
	$user_id = request()->input('user_id');
	$internal_number = request()->input('internal_number');
    $address_1 = request()->input('address_1');
    $address_2 = request()->input('address_2');
    $address_3 = request()->input('address_3');
    $address_4 = request()->input('address_4');
    $email = request()->input('email');
	$enabled = (int)request()->input('disabled',0);

	$x = "INSERT into `supplier` (`name`,`postcode`,`contact_name`,`contact_number`,`user_id`,`internal_number`,`disabled`) VALUES (?,?,?,?,?,?,?)";
	$supplier_id = prepareExecuteQuery($x,'ssssssi',[$name,$postcode,$contact_name,$contact_number,$user_id,$internal_number,$enabled], true);

    if (
        ($address_1 !== null && $address_1 !== '') ||
        ($address_2 !== null && $address_2 !== '') ||
        ($address_3 !== null && $address_3 !== '') ||
        ($address_4 !== null && $address_4 !== '') ||
        ($postcode !== null && $postcode !== '')
    ) {
        $ca = new ClientAddress();
        $ca->client_id = $supplier_id;
        $ca->address_id = 1;
        $ca->client_type = ClientType::SUPPLIER->value;
        $ca->address_1 = $address_1;
        $ca->address_2 = $address_2;
        $ca->address_3 = $address_3;
        $ca->address_4 = $address_4;
        $ca->postcode = $postcode;
        $ca->address_number = $contact_number;
        $ca->site_id = 1;
        $ca->allowed_vehicle_types = '';
        $ca->require_tail_lift = false;
        $ca->geocoding_tried = 0;
        $ca->collection = false;
        $ca->lat = null;
        $ca->lon = null;
        $ca->save();
    }
?>
<script>
	window.location = '../manageSuppliers.php';
</script>
