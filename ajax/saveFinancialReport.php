<?php
require_once('../functions.php');
$sql = "REPLACE INTO system_settings (key_name, key_value) VALUES ('FINANCIAL_REPORT','".mysqli_real_escape_string($conn,$_POST['input'])."')";
$res = mysqli_query($conn, $sql) or die(mysqli_error($conn)." ". $sql);

?>