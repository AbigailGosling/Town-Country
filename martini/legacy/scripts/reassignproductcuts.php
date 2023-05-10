<?php
	require(__DIR__.'/../functions.php');
    
    
    
    $before_cutid = request('before_cutid');
    $after_cutid = request('after_cutid');
    
     if($before_cutid != '' && $after_cutid != ''){
        $y = prepareExecuteQuery("UPDATE `product` SET cut_id = ? WHERE cut_id = ?",'ii',[$after_cutid,$before_cutid]);
        
        $y = prepareExecuteQuery("DELETE FROM `cuts` WHERE id=?",'i',[$before_cutid]);

        ?><script> window.location.href = '../manageCuts.php'; </script><?php
    }else{
        echo 'failed';
    }
?>