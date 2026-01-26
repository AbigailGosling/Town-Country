<?php

use Carbon\Carbon;

	require(__DIR__.'/../functions.php');

    $addressid = request()->input('address_id');
    $customerid = request()->input('customer_id');
    $picksheetid = request()->input('picksheetid');
    $picksheet_note = request()->input('picksheet_note');
    $user_from_id = request()->input('user_id');
    $eta = Carbon::createFromFormat('d/m/Y', request()->input('eta'));

    $orderReferenceNumber = request()->input('orderReferenceNumber');

    $y = prepareExecuteQuery("UPDATE `reservation` SET `user_id`=?, `order_reference_number`=?, `address_id`=?,`picksheet_note`=?,`eta`=? WHERE id=? LIMIT 1",
'isssss',[$user_from_id,$orderReferenceNumber,$addressid,$picksheet_note,$eta->format("Y-m-d"),$picksheetid]);

    loggedDataChange("reservation_note",$picksheetid,$picksheet_note);
    loggedDataChange("reservation_orderReferenceNumber",$picksheetid,$orderReferenceNumber);
?>

<script type="text/javascript">
	window.location.href = "<?php echo $domain; ?>/viewReservation.php?id=<?php echo $picksheetid; ?>";
</script>
