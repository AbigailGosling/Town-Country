<?php
//This PHP Script is responsible for generating a PDF Statement and sends it to the invoice address at Town&Country!
require_once(__DIR__.'/../functions.php');
require_once(__DIR__.'/../scripts/PDFRenderer.php');
require_once(__DIR__.'/../scripts/SLabsEmailer.php');

use InternalScripts\SLabsEmailer;
use InternalScripts\PDFRenderer;

//This function renders a PDF document from a string using mPDF
function renderPDF($saleID){

	$statementDate = time();
	$fileName = 'Sale_'.$saleID.'_'.$statementDate.'.pdf';
	$pathToFile = 'PDF';

	PDFRenderer::generatePDFfromWeb('viewSupplierreturn.php?id='.$saleID,$pathToFile,$fileName);

	$x = "SELECT `customer_id` FROM `pickerSheets` WHERE id=?";
	$y = prepareExecuteQuery($x,'i',[$saleID]);
	$picksheet = mysqli_fetch_assoc($y);

	$customer_id = $picksheet['customer_id'];

	$customerQueryResult = prepareExecuteQuery("SELECT `name`,`email` FROM `supplier` WHERE id = ?",'i',[$customer_id]);
	$customer = mysqli_fetch_assoc($customerQueryResult);
	$customer_emails = [$customer['email'],"reena.sangha@townandcountrymeats.co.uk"];
	$subject = "Supplier Return ".$saleID." from Town and Country Meats";
	$htmlBody = "<html>Please find attached a supplier return from Town and Country Meats Group for ".$customer['name']." No: ".$saleID.".</html>";

	$x = "UPDATE `pickerSheets` SET sent=1 WHERE id=?";

	$y = prepareExecuteQuery($x,'i',[$saleID]);

	return SLabsEmailer::send_email($customer_id,"SUPPLIER_RETURN",$customer_emails,$subject,$htmlBody,$pathToFile,$fileName,$saleID);

}
if (request()->has('id')) renderPDF(request()->input('id'));
?>
