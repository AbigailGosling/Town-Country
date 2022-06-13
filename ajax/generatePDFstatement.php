<?php
require_once('../functions.php');
if (isset($_POST["ids"]))
{
	foreach($_POST["ids"] as $customerID)
	{
		mysqli_query($conn, "INSERT INTO mail_queue (customer_id) VALUES ($customerID)");
	}
	putenv("SHELL=/bin/bash");
	print `echo /usr/bin/php -q generatePDFstatement2.php | at now 2>&1`;
}


?>