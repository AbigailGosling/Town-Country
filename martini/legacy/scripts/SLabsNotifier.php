<?php

require_once(__DIR__.'/../scripts/SLabsEmailer.php');
use InternalScripts\SLabsEmailer;

$data = json_decode(file_get_contents('php://input'), true);
if ($data['SecretKey'] != env("MAIL_NOTIFICATION_KEY"))
{
    http_response_code(401);
}
else
{
    http_response_code(200);
    SLabsEmailer::process_notification($data);
}
?>
