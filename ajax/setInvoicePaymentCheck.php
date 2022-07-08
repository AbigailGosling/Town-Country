<?php
include_once('../functions.php');
$state = $_POST['state'];
$id = $_POST['id'];

$t = $mysqli->prepare("UPDATE `pickerSheets` SET `admin_approved` = ? WHERE `id` = ?");
$t->bind_param('ii', $state, $id);
$t->execute();

?>