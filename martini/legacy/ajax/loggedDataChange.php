<?php
require_once(__DIR__.'/../functions.php');
$type = request()->input('type');
$entityid = request()->input('entity_id');
$body = trim(request()->input('body'));
loggedDataChange($type,$entityid,$body);
?>