<?php
	require(__DIR__.'/../functions.php');
    $picksheetid = request()->input('picksheetid');      
    $orderReferenceNumber = request()->input('orderReferenceNumber');
 
    $y = prepareExecuteQuery("UPDATE `pickerSheets` SET orderReferenceNumber=? WHERE id=? LIMIT 1",'si',[$orderReferenceNumber,$picksheetid]);
    loggedDataChange("picksheet_orderReferenceNumber",$picksheetid,$orderReferenceNumber);
?>

<script type="text/javascript">
	window.location.href = "<?php echo $domain; ?>/viewSalesconfirmation.php?id=<?php echo $picksheetid; ?>";
</script>