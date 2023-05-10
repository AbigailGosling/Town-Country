<?php
	require(__DIR__.'/../functions.php');

    $addressid = $mysqli->real_escape_string( request()->input('addressid'));
    $customerid = $mysqli->real_escape_string( request()->input('customerid'));
    $picksheetid = $mysqli->real_escape_string( request()->input('picksheetid'));
    $picksheet_note = $mysqli->real_escape_string( request()->input('picksheet_note'));
    $user_from_id = $mysqli->real_escape_string( request()->input('user_from_id'));


    $addressline1 = $mysqli->real_escape_string( request()->input('addressline1'));
    $addressline2 = $mysqli->real_escape_string( request()->input('addressline2'));
    $addressline3 = $mysqli->real_escape_string( request()->input('addressline3'));
    $addressline4 = $mysqli->real_escape_string( request()->input('addressline4'));
    $addresspostcode = $mysqli->real_escape_string( request()->input('addresspostcode'));
    $deliverynumber = $mysqli->real_escape_string( request()->input('deliverynumber'));
    
    $orderReferenceNumber = $mysqli->real_escape_string( request('orderReferenceNumber'));

    
    $estimated_delivery_date = $mysqli->real_escape_string( request()->input('estimated_delivery_date')); #picksheet
 
    $y = prepareExecuteQuery("UPDATE `pickerSheets` SET user_from_id=?, estimated_delivery_date=?, orderReferenceNumber=?, addressid=?,picksheet_note=? WHERE id=? LIMIT 1",
'isssss',[$user_from_id,$estimated_delivery_date,$orderReferenceNumber,$addressid,$picksheet_note,$picksheetid]);

    $y = prepareExecuteQuery("UPDATE `customers` SET
        address{$addressid}_1='$addressline1',
        address{$addressid}_2='$addressline2',
        address{$addressid}_3='$addressline3',
        address{$addressid}_4='$addressline4',
        postcode_{$addressid}='$addresspostcode',
        address{$addressid}_number='$deliverynumber'
        WHERE id = $customerid LIMIT 1");

?>

<script type="text/javascript">
	window.location.href = "<?php echo $domain; ?>/viewSalesconfirmation.php?id=<?php echo $picksheetid; ?>";
</script>