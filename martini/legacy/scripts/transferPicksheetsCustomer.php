<?php
	require(__DIR__.'/../functions.php');
	
	$old_customer_id = $mysqli->real_escape_string( request()->input('old_customer_id'));
	$new_customer_id = $mysqli->real_escape_string( request()->input('new_customer_id'));
	
    

    if($new_customer_id != '' && $old_customer_id != ''){


        prepareExecuteQuery("UPDATE pickerSheets SET customer_id=? WHERE customer_id=?",'ii',[$new_customer_id,$old_customer_id]);
        
        prepareExecuteQuery("DELETE FROM customers WHERE id=? LIMIT 1",'i',[$old_customer_id]);




    }    
?>
<script> window.location.href = '<?php echo $domain; ?>manageCustomers.php?msg=Picksheets transfered!'; </script>