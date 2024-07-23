<?php
//This PHP Script is responsible for generating a PDF Statement and sends it to the invoice address at Town&Country!

require_once(__DIR__.'/../functions.php');
require_once(__DIR__.'/../scripts/PDFRenderer.php');
require_once(__DIR__.'/../scripts/SLabsEmailer.php');

use InternalScripts\SLabsEmailer;
use InternalScripts\PDFRenderer;
//This function renders a PDF document from a string using mPDF
function renderPDF($customerID){
	global $mysqli;

	$statementDate = time();
	$fileName = 'Statement_'.$customerID.'_'.$statementDate.'.pdf';
	$pathToFile = 'PDF';

	PDFRenderer::generatePDFfromWeb('customer_soam.php?id='.$customerID,$pathToFile,$fileName);

	$customerQueryResult = prepareExecuteQuery("SELECT businessname,accounts_email,internal_email FROM `customers` WHERE id = ?",'i',[$customerID]);
	$customer = mysqli_fetch_assoc($customerQueryResult);
	$customer_emails = array();
	if ($customer['accounts_email']!= null && $customer['accounts_email']!= "")
	{
		$dirty_emails = explode(";",$customer['accounts_email']);
	}
	else
	{
		$dirty_emails = explode(";",$customer['internal_email']);
	}
	foreach($dirty_emails as $dirty_email)
	{
		if (filter_var(trim($dirty_email), FILTER_VALIDATE_EMAIL) !== false)
		{
			$customer_emails[] = trim($dirty_email);
		}
	}
	$subject = "Statement of Account from Town and Country Meats";
	$htmlBody = "<html>Please find attached a statement of account from Town and Country Meats Group for ".$customer['businessname'].".</html>";

	return SLabsEmailer::send_email($customerID,"STATEMENT",$customer_emails,$subject,$htmlBody,$pathToFile,$fileName);

}
$customerQueryResult = prepareExecuteQuery("SELECT customer_id FROM `mail_queue` LIMIT 1");
if (mysqli_num_rows($customerQueryResult) > 0)
{
	$customer = mysqli_fetch_assoc($customerQueryResult);
	$customerID = $customer['customer_id'];
	renderPDF($customerID);
	prepareExecuteQuery("DELETE FROM `mail_queue` WHERE customer_id = ?",'i',[$customerID]);
    pclose(popen('start /B cmd /C "php '.$artisanLocation.' run:statements_queue >NUL 2>NUL"', 'r'));
}
?>
