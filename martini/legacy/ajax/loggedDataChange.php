<?php
require_once(__DIR__.'/../functions.php');
$type = request('type');
$entityid = request('entity_id');
$body = trim(request('body'));
loggedDataChange($type,$entityid,$body);
?>