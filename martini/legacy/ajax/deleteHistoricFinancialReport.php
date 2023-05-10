<?php
require_once(__DIR__.'/../functions.php');
$sql = "DELETE FROM `finance_report_history` WHERE `user_id` = ? AND `id` = ?";
$res = prepareExecuteQuery($sql,'ii',[$userid,request()->input('id')]);