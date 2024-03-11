<?php
	require(__DIR__.'/../functions.php');

    $addressid = request()->input('addressid');
    $customerid = request()->input('customerid');
    $picksheetid = request()->input('picksheetid');
    $picksheet_note = request()->input('picksheet_note');
    $user_from_id = request()->input('user_from_id');
        
    $orderReferenceNumber = request()->input('orderReferenceNumber');

    
    $estimated_delivery_date = request()->input('estimated_delivery_date'); #picksheet
 
    $y = prepareExecuteQuery("UPDATE `pickerSheets` SET user_from_id=?, estimated_delivery_date=?, orderReferenceNumber=?, addressid=?,picksheet_note=? WHERE id=? LIMIT 1",
'isssss',[$user_from_id,$estimated_delivery_date,$orderReferenceNumber,$addressid,$picksheet_note,$picksheetid]);

    loggedDataChange("picksheet_note",$picksheetid,$picksheet_note);
    loggedDataChange("picksheet_orderReferenceNumber",$picksheetid,$orderReferenceNumber);
?>

<script type="text/javascript">
	window.location.href = "<?php echo $domain; ?>/viewSalesconfirmation.php?id=<?php echo $picksheetid; ?>";
</script>