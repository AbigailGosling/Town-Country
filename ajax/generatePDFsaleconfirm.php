<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//This PHP Script is responsible for generating a PDF Statement and sends it to the invoice address at Town&Country!
require_once('../functions.php');
require_once('../scripts/PDFRenderer.php');
require_once('../scripts/SLabsEmailer.php');

use InternalScripts\SLabsEmailer;
use InternalScripts\PDFRenderer;

//---POST DATA---//
$saleID = $_POST["id"];

//This function renders a PDF document from a string using mPDF
function renderPDF($saleID){
	global $conn; 

	$statementDate = time();
	$fileName = 'Sale_'.$saleID.'_'.$statementDate.'.pdf';
	$pathToFile = 'PDF';

	PDFRenderer::generatePDFfromWeb('viewSalesconfirmation.php?id='.$saleID,$pathToFile,$fileName);
	
	$x = "SELECT `customer_id` FROM `pickerSheets` WHERE id=$saleID";
	$y = mysqli_query($conn, $x) or die(mysqli_error($conn));
	$picksheet = mysqli_fetch_assoc($y);
		
	$customer_id = $picksheet['customer_id'];

	$customerQueryResult = mysqli_query($conn, "SELECT businessname,accounts_email,internal_email FROM `customers` WHERE id = $customer_id");
	$customer = mysqli_fetch_assoc($customerQueryResult);
	if ($customer['accounts_email']!= null && $customer['accounts_email']!= "")
	{
		$customer_emails = explode(";",$customer['accounts_email']);
	}
	else
	{
		$customer_emails = explode(";",$customer['internal_email']);
	}
	$subject = "Sale Confirmation ".$saleID." from Town and Country Meats";
	$htmlBody = "<html>Please find attached a sale confiramtion from Town and Country Meats Group for ".$customer['businessname']." Invoice No: ".$saleID.".</html>";

	$x = "UPDATE `pickerSheets` SET sent=1 WHERE id=$saleID";
	
	$y = mysqli_query($conn, $x) or die(mysqli_error($conn));

	return SLabsEmailer::send_email($customer_emails,$subject,$htmlBody,$pathToFile,$fileName);
	
}
//Main Decleration
echo renderPDF($saleID);

?>