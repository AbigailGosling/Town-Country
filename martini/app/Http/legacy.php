<?php

global $mysqli;
global $conn;
$targetFile = str_replace("?".request()->server('QUERY_STRING'),'',request()->server('REQUEST_URI'));
if (file_exists(realpath(__DIR__.'/../..'.$targetFile)))
{
    require realpath(__DIR__.'/../..'.$targetFile);
}
else
{
    abort(404);
}
?>