<?php
//This PHP Script is responsible for generating a PDF Statement and sends it to the invoice address at Town&Country!

require_once('/var/www/html/functions.php');
require_once('../scripts//PDFRenderer.php');
require_once('../scripts/SLabsEmailer.php');

use InternalScripts\SLabsEmailer;
use InternalScripts\PDFRenderer;
//This function renders a PDF document from a string using mPDF
function renderPDF($customerID){
	global $conn; 

	$statementDate = time();
	$fileName = 'Statement_'.$customerID.'_'.$statementDate.'.pdf';
	$pathToFile = 'PDF';

	PDFRenderer::generatePDFfromWeb('customer_soam.php?id='.$customerID,$pathToFile,$fileName);
	
	$customerQueryResult = mysqli_query($conn, "SELECT businessname,accounts_email,internal_email FROM `customers` WHERE id = $customerID");
	$customer = mysqli_fetch_assoc($customerQueryResult);
	if ($customer['accounts_email']!= null && $customer['accounts_email']!= "")
	{
		$customer_emails = explode(";",$customer['accounts_email']);
	}
	else
	{
		$customer_emails = explode(";",$customer['internal_email']);
	}
	$subject = "Statement of Account from Town and Country Meats";
	$htmlBody = "<html>Please find attached a statement of account from Town and Country Meats Group for ".$customer['businessname'].".</html>";

	return SLabsEmailer::send_email($customerID,"STATEMENT",$customer_emails,$subject,$htmlBody,$pathToFile,$fileName);
	
}
$customerQueryResult = mysqli_query($conn, "SELECT customer_id FROM `mail_queue` LIMIT 1");
if (mysqli_num_rows($customerQueryResult) > 0)
{
	$customer = mysqli_fetch_assoc($customerQueryResult);
	$customerID = $customer['customer_id'];
	renderPDF($customerID);
	mysqli_query($conn, "DELETE FROM `mail_queue` WHERE customer_id = $customerID");
	putenv("SHELL=/bin/bash");
	print `echo php -q generatePDFstatement2.php | at now 2>&1`;
}
?>