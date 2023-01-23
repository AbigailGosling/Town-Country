<?php
    include_once('functions.php');


    # grab all pickersheets that are marked as completed

    $yPickersheets = prepareExecuteQuery("SELECT * FROM `pickerSheets` WHERE completed =1");
    $count = $yPickersheets->num_rows;

    echo 'There are '. $count .' completed picksheets<br/><br/>';


    while($pickersheet = $yPickersheets->fetch_assoc()){
        $pickersheet_id = $pickersheet['id'];
        
        # foreach completed pickersheet, mark all the pickeritems as picked
        $y = prepareExecuteQuery("UPDATE `pickerItems` SET status=1 WHERE pickersheet_id=?",'i',[$pickersheet_id]);
        $affected = $mysqli->affected_rows;
        echo 'Picksheet #' . $pickersheet_id . ' => '. $affected . ' rows updated<br/><Br/>'; 
    }
?>