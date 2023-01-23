<?php
require_once(__DIR__.'/../functions.php');
if (isset(request("ids")))
{
	foreach(request("ids") as $customerID)
	{
		prepareExecuteQuery("INSERT INTO mail_queue (customer_id) VALUES (?)",'i',[$customerID]);
	}
	putenv("SHELL=/bin/bash");
	print `echo /usr/bin/php -q generatePDFstatement2.php | at now 2>&1`;
}


?>