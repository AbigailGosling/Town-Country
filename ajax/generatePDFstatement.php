<?php
//This PHP Script is responsible for generating a PDF Statement and sends it to the invoice address at Town&Country!
require_once('../functions.php');
require_once('../scripts/PDFRenderer.php');
require_once('../scripts/SLabsEmailer.php');

use InternalScripts\SLabsEmailer;
use InternalScripts\PDFRenderer;

//---POST DATA---//
$customerID = $_POST["id"];

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

	return SLabsEmailer::send_email($customer_emails,$subject,$htmlBody,$pathToFile,$fileName);
	
}
//Main Decleration
echo renderPDF($customerID);

?>