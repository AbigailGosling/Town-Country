<?php
	require(__DIR__.'/../functions.php');

    $addressid = request()->input('addressid');
    $customerid = request()->input('customerid');
    $picksheetid = request()->input('picksheetid');
    $picksheet_note = request()->input('picksheet_note');
    $user_from_id = request()->input('user_from_id');

    $orderReferenceNumber = request()->input('orderReferenceNumber');


    $y = prepareExecuteQuery("UPDATE `reservation` SET `user_id`=?, `order_reference_number`=?, `address_id`=?,`picksheet_note`=? WHERE id=? LIMIT 1",
'issss',[$user_from_id,$orderReferenceNumber,$addressid,$picksheet_note,$picksheetid]);

    loggedDataChange("reservation_note",$picksheetid,$picksheet_note);
    loggedDataChange("reservation_orderReferenceNumber",$picksheetid,$orderReferenceNumber);
?>

<script type="text/javascript">
	window.location.href = "<?php echo $domain; ?>/viewReservation.php?id=<?php echo $picksheetid; ?>";
</script>
