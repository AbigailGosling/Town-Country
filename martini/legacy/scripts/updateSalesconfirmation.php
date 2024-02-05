<?php
	require(__DIR__.'/../functions.php');

    $addressid = request()->input('addressid');
    $customerid = request()->input('customerid');
    $picksheetid = request()->input('picksheetid');
    $picksheet_note = request()->input('picksheet_note');
    $user_from_id = request()->input('user_from_id');


    $addressline1 = request()->input('addressline1');
    $addressline2 = request()->input('addressline2');
    $addressline3 = request()->input('addressline3');
    $addressline4 = request()->input('addressline4');
    $addresspostcode = request()->input('addresspostcode');
    $deliverynumber = request()->input('deliverynumber');
    
    $orderReferenceNumber = request()->input('orderReferenceNumber');

    
    $estimated_delivery_date = request()->input('estimated_delivery_date'); #picksheet
 
    $y = prepareExecuteQuery("UPDATE `pickerSheets` SET user_from_id=?, estimated_delivery_date=?, orderReferenceNumber=?, addressid=?,picksheet_note=? WHERE id=? LIMIT 1",
'isssss',[$user_from_id,$estimated_delivery_date,$orderReferenceNumber,$addressid,$picksheet_note,$picksheetid]);

    /*$y = prepareExecuteQuery("UPDATE `customers` SET
        address{$addressid}_1='$addressline1',
        address{$addressid}_2='$addressline2',
        address{$addressid}_3='$addressline3',
        address{$addressid}_4='$addressline4',
        postcode_{$addressid}='$addresspostcode',
        address{$addressid}_number='$deliverynumber'
        WHERE id = $customerid LIMIT 1");*/
    loggedDataChange("picksheet_note",$picksheetid,$picksheet_note);
    loggedDataChange("picksheet_orderReferenceNumber",$picksheetid,$orderReferenceNumber);
?>

<script type="text/javascript">
	window.location.href = "<?php echo $domain; ?>/viewSalesconfirmation.php?id=<?php echo $picksheetid; ?>";
</script>