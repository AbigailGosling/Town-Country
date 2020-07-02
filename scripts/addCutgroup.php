<?php
    require('../functions.php');
    
	$name = mysqli_real_escape_string($conn, $_POST['name']);
	$species_id = mysqli_real_escape_string($conn, $_POST['species_id']);
    
    $y = mysqli_query($conn, "SELECT * FROM `cutgroups` WHERE species_id='$species_id' && name='$name'");
    $count = mysqli_num_rows($y);
    if($count > 0){
    ?>
    <script>
        window.location = '../manageCutgroups.php?msg=Theres already a cut group called <?php echo $name; ?> for that species';
    </script>
    <?php
    }else{
    	$x = "INSERT into `cutgroups` (name,species_id) VALUES ('$name','$species_id')";
        $y = mysqli_query($conn, $x);
    ?>
    <script>
        window.location = '../manageCutgroups.php';
    </script>
    <?php      
    }
?>