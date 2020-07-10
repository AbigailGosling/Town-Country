<?php
    # fixPurchaseDB.php

    include_once('functions.php');

    $x = "UPDATE `purchase_form` SET price = replace(price,',','|');
            UPDATE `purchase_form` SET units = replace(units,',','|');
            UPDATE `purchase_form` SET species = replace(species,',','|');
            UPDATE `purchase_form` SET cut = replace(cut,',','|');";
    $y = mysqli_query($conn,$x);

    echo mysqli_affected_rows($conn) . ' rows';
?>
