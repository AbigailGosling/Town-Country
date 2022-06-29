<?php

require_once('../scripts/SLabsEmailer.php');
use InternalScripts\SLabsEmailer;

$data = json_decode(file_get_contents('php://input'), true);
if ($data['SecretKey'] != SLabsEmailer::NotifcationAPIKey) 
{
    http_response_code(401);
}
else
{
    SLabsEmailer::process_notification($data);
}
?>