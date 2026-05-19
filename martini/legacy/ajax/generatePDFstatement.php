<?php

use App\Helpers\ProcessHelper;

require_once(__DIR__.'/../functions.php');
if (request()->input("ids") !== null)
{
	foreach(request()->input("ids") as $customerID)
	{
		prepareExecuteQuery("INSERT INTO mail_queue (customer_id) VALUES (?)",'i',[$customerID]);
	}
    ProcessHelper::runInBackground('run:statements_queue');
}


?>
