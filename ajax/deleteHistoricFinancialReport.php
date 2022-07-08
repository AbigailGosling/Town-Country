<?php
require_once('../functions.php');
$sql = "DELETE FROM `finance_report_history` WHERE `user_id` = $userid AND `id` = ".$_POST['id'];
$res = mysqli_query($conn, $sql) or die(mysqli_error($conn)." ". $sql);