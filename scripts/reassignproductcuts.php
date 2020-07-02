<?php
	require('../functions.php');
    
    
    
    $before_cutid = $_POST['before_cutid'];
    $after_cutid = $_POST['after_cutid'];
    
     if($before_cutid != '' && $after_cutid != ''){
        $y = mysqli_query($conn, "UPDATE `product` SET cut_id ='$after_cutid' WHERE cut_id='$before_cutid'");
        
        $y = mysqli_query($conn, "DELETE FROM `cuts` WHERE id='$before_cutid'");

        ?><script> window.location.href = '/manageCuts.php'; </script><?php
    }else{
        echo 'failed';
    }
?>