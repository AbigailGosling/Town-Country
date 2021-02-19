<?php
	require('../functions.php');
	
	$old_customer_id = mysqli_real_escape_string($conn, $_POST['old_customer_id']);
	$new_customer_id = mysqli_real_escape_string($conn, $_POST['new_customer_id']);
	
    

    if($new_customer_id != '' && $old_customer_id != ''){


        mysqli_query($conn, "UPDATE pickerSheets SET customer_id='$new_customer_id' WHERE customer_id='$old_customer_id'");
        
        mysqli_query($conn, "DELETE FROM customers WHERE id='$old_customer_id' LIMIT 1");




    }    
?>
<script> window.location.href = '<?php echo $domain; ?>manageCustomers.php?msg=Picksheets transfered!'; </script>