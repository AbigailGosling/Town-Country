<?php
	require('../functions.php');

	
    $addressid = mysqli_real_escape_string($conn, $_POST['addressid']);
    $customerid = mysqli_real_escape_string($conn, $_POST['customerid']);
    $picksheetid = mysqli_real_escape_string($conn, $_POST['picksheetid']);
    $picksheet_note = mysqli_real_escape_string($conn, $_POST['picksheet_note']);

    $addressline1 = mysqli_real_escape_string($conn, $_POST['addressline1']);
    $addressline2 = mysqli_real_escape_string($conn, $_POST['addressline2']);
    $addressline3 = mysqli_real_escape_string($conn, $_POST['addressline3']);
    $addressline4 = mysqli_real_escape_string($conn, $_POST['addressline4']);
    $addresspostcode = mysqli_real_escape_string($conn, $_POST['addresspostcode']);
    $deliverynumber = mysqli_real_escape_string($conn, $_POST['deliverynumber']);
    
    
    $estimated_delivery_date = mysqli_real_escape_string($conn, $_POST['estimated_delivery_date']); #picksheet
    

   echo 'Updating..';

    // update delivery date && addressid
    $y = mysqli_query($conn, "UPDATE `pickerSheets` SET estimated_delivery_date='$estimated_delivery_date', addressid='$addressid',picksheet_note='$picksheet_note' WHERE id='$picksheetid' LIMIT 1");


    if($addressid == 1){
        $y = mysqli_query($conn, "UPDATE `customers` SET
        address1_1='$addressline1',
        address1_2='$addressline2',
        address1_3='$addressline3',
        address1_4='$addressline4',
        postcode_1='$addresspostcode',
        address1_number='$deliverynumber'
        WHERE id = $customerid LIMIT 1");
    }else if($addressid == 2){
        $y = mysqli_query($conn, "UPDATE `customers` SET
        address2_1='$addressline1',
        address2_2='$addressline2',
        address2_3='$addressline3',
        address2_4='$addressline4',
        postcode_2='$addresspostcode',
        address2_number='$deliverynumber'

        WHERE id = $customerid LIMIT 1");
    }else if($addressid == 3){
        $y = mysqli_query($conn, "UPDATE `customers` SET
        address3_1='$addressline1',
        address3_2='$addressline2',
        address3_3='$addressline3',
        address3_4='$addressline4',
        postcode_3='$addresspostcode',
        address3_number='$deliverynumber'

        WHERE id = $customerid LIMIT 1");
    }



?>

<script type="text/javascript">
	window.location.href = "<?php echo $domain; ?>/viewSalesconfirmation.php?id=<?php echo $picksheetid; ?>";
</script>