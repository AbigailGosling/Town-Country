<?php
    require(__DIR__.'/../functions.php');
    
	$name = $mysqli->real_escape_string( request('name'));
	$species_id = $mysqli->real_escape_string( request('species_id'));
    
    $y = prepareExecuteQuery("SELECT * FROM `cutgroups` WHERE `species_id`= ? && `name`= ?",'ss',[$species_id,$name]);
    $count = mysqli_num_rows($y);
    if($count > 0){
    ?>
    <script>
        window.location = '../manageCutgroups.php?msg=Theres already a cut group called <?php echo $name; ?> for that species';
    </script>
    <?php
    }else{
    	$x = "INSERT into `cutgroups` (`name`,`species_id`) VALUES (?,?)";
        $y = prepareExecuteQuery($x,'ss',[$name,$species_id]);
    ?>
    <script>
        window.location = '../manageCutgroups.php';
    </script>
    <?php      
    }
?>