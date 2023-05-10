<?php
include_once(__DIR__.'/../functions.php');
$state = request()->input('state');
$id = request()->input('id');

$t = $mysqli->prepare("UPDATE `pickerSheets` SET `admin_approved` = ? WHERE `id` = ?");
$t->bind_param('ii', $state, $id);
$t->execute();

?>