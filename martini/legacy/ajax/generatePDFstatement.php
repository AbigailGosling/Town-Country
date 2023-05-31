<?php
require_once(__DIR__.'/../functions.php');
use Illuminate\Support\Facades\Log;
if (request()->input("ids") !== null)
{
	foreach(request()->input("ids") as $customerID)
	{
		prepareExecuteQuery("INSERT INTO mail_queue (customer_id) VALUES (?)",'i',[$customerID]);
	}
	shell_exec("php /var/www/html/martini/artisan run:statements_queue > /dev/null 2>&1 &");
}


?>