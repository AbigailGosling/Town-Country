<?php
    include_once('functions.php');


    # grab all pickersheets that are marked as completed

    $yPickersheets = mysqli_query($conn, "SELECT * FROM `pickerSheets` WHERE completed =1");
    $count = mysqli_num_rows($yPickersheets);

    echo 'There are '. $count .' completed picksheets<br/><br/>';


    while($pickersheet = mysqli_fetch_array($yPickersheets)){
        $pickersheet_id = $pickersheet['id'];
        
        # foreach completed pickersheet, mark all the pickeritems as picked
        $y = mysqli_query($conn, "UPDATE `pickerItems` SET status=1 WHERE pickersheet_id='$pickersheet_id'");
        $affected = mysqli_affected_rows($conn);
        echo 'Picksheet #' . $pickersheet_id . ' => '. $affected . ' rows updated<br/><Br/>'; 
    }
?>