<?php
require_once('../functions.php');
$type = $_POST['type'];
$entityid = $_POST['entity_id'];
$body = trim($_POST['body']);
$sql = "INSERT INTO `tandc_live`.`comment_logging` (`type`, `user_id`, `entity_id`, `body`) VALUES ('$type', '$userid', '$entityid', '$body')";
mysqli_query($conn,$sql) or die (mysqli_error($conn)." ".$sql);
?>