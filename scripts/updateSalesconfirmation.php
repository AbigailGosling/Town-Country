<?php
	require('../functions.php');

    $addressid = mysqli_real_escape_string($conn, $_POST['addressid']);
    $customerid = mysqli_real_escape_string($conn, $_POST['customerid']);
    $picksheetid = mysqli_real_escape_string($conn, $_POST['picksheetid']);
    $picksheet_note = mysqli_real_escape_string($conn, $_POST['picksheet_note']);
    $user_from_id = mysqli_real_escape_string($conn, $_POST['user_from_id']);


    $addressline1 = mysqli_real_escape_string($conn, $_POST['addressline1']);
    $addressline2 = mysqli_real_escape_string($conn, $_POST['addressline2']);
    $addressline3 = mysqli_real_escape_string($conn, $_POST['addressline3']);
    $addressline4 = mysqli_real_escape_string($conn, $_POST['addressline4']);
    $addresspostcode = mysqli_real_escape_string($conn, $_POST['addresspostcode']);
    $deliverynumber = mysqli_real_escape_string($conn, $_POST['deliverynumber']);
    
    $orderReferenceNumber = mysqli_real_escape_string($conn, $_POST['orderReferenceNumber']);

    
    $estimated_delivery_date = mysqli_real_escape_string($conn, $_POST['estimated_delivery_date']); #picksheet
 
    $y = mysqli_query($conn, "UPDATE `pickerSheets` SET user_from_id='$user_from_id', estimated_delivery_date='$estimated_delivery_date', orderReferenceNumber='$orderReferenceNumber', addressid='$addressid',picksheet_note='$picksheet_note' WHERE id='$picksheetid' LIMIT 1");

    $y = mysqli_query($conn, "UPDATE `customers` SET
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