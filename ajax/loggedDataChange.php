<?php
require_once('../functions.php');
$type = $_POST['type'];
$entityid = $_POST['entity_id'];
$body = trim($_POST['body']);
loggedDataChange($type,$entityid,$body);
?>