<?php
	require('../functions.php');

	
    $pickersheet_id = mysqli_real_escape_string($conn, $_POST['pickersheet_id']);

    $weightIDS = [];
    
    # Get Pallets Out
    $y = mysqli_query($conn, "SELECT * FROM `palletsout` WHERE pickersheet_id='$pickersheet_id'");
    
    while($palletOut = mysqli_fetch_array($y)){
        $weight_ids = $palletOut['weight_ids'];
        $weight_ids = explode(',',$weight_ids);

        foreach($weight_ids as $weight_id){ array_push($weightIDS, $weight_id); }
    }

    $weightIDS = implode(',', $weightIDS);



    # set weights available
    $x = "UPDATE `weights` SET status_id = '0' WHERE id IN ($weightIDS)";
    $y = mysqli_query($conn, $x);


    # delete pallets out
    $y = mysqli_query($conn, "DELETE FROM `palletsout` WHERE pickersheet_id='$pickersheet_id'");

    # delete picker items
    $y = mysqli_query($conn, "DELETE FROM `pickeritems` WHERE pickersheet_id='$pickersheet_id'");
    
    # delete pickersheet
    $y = mysqli_query($conn, "DELETE FROM `pickersheets` WHERE id='$pickersheet_id'");
    

    header('Location: ../salesconfirmationList.php');
?>