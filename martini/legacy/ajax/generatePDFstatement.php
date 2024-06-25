<?php
require_once(__DIR__.'/../functions.php');
use Illuminate\Support\Facades\Log;
if (request()->input("ids") !== null)
{
	foreach(request()->input("ids") as $customerID)
	{
		prepareExecuteQuery("INSERT INTO mail_queue (customer_id) VALUES (?)",'i',[$customerID]);
	}
    pclose(popen('start /B cmd /C "php D:\\wwwroot\\martini\\artisan run:statements_queue >NUL 2>NUL"', 'r'));
	//shell_exec("php D:\\wwwroot\\martini\\artisan run:statements_queue > NUL 2>&1 &");
}


?>
