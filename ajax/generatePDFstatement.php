<?php
//This PHP Script is responsible for generating a PDF Statement and sends it to the invoice address at Town&Country!
//-----IMPORTS-----//
require_once('../functions.php');
include_once('../includes/frontHeader.php');
require_once '../vendor/autoload.php';
//SOCKETLABS IMPORTS//
use Socketlabs\SocketLabsClient;
use Socketlabs\Message\BasicMessage;
use Socketlabs\Message\EmailAddress;
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Page;

//---PHP CONFIG---//
ini_set('memory_limit', '1024M');
set_time_limit(1800); //seconds
//---POST DATA---//
$customerID = $_POST["id"];

//This function generates a Unique ID using Mersenne Twister RNG
//Going to want to switch RNG algo before pushing to LIVE!!!!
function generate_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
        mt_rand( 0, 0xffff ),
        mt_rand( 0, 0x0C2f ) | 0x4000,
        mt_rand( 0, 0x3fff ) | 0x8000,
        mt_rand( 0, 0x2Aff ), mt_rand( 0, 0xffD3 ), mt_rand( 0, 0xff4B )
    );
}

//This function renders a PDF document from a string using mPDF
function renderPDF($customerID){

	$statementDate = time();
	$filename2 = 'Statement_'.$customerID.'_'.$statementDate.'.pdf';
	$filename = '../PDF/' . $filename2;

	$browserFactory = new BrowserFactory('/usr/bin/google-chrome');

	// starts headless chrome
	$browser = $browserFactory->createBrowser();
	try {
		// creates a new page and navigate to an URL
		$page = $browser->createPage();
		$page->navigate('http://localhost/')->waitForNavigation();
		//login
		$evaluation = $page->evaluate(
			'(() => {
					document.querySelector("#email").value = "php-pdf-generator@tang.solutions";
					document.querySelector("#password").value = "{CY_}TD87q&)fUqp";
					document.querySelector("#loginform").submit();
				})()'
			)->waitForPageReload();

		$page->navigate('http://localhost/customer_soam.php?id='.$customerID)->waitForNavigation();
		$hasResult = false;
		$start = time();
		while (!$hasResult)
		{
			try
			{
				$evaluation = $page->evaluate(
					'(() => {
							return renderComplete;
						})()'
					)->getReturnValue(500);
				
			}
			catch (Exception $e) {}
			if ($evaluation)
			{
				$hasResult = true;
				break;
			}
			if (time() - $start > 500)
			{
				break;
			}
			usleep(100000);
		}
		// pdf
		if (!$hasResult) die('error');
		$out= $page->pdf(['printBackground' => false]);
		$out->saveToFile($filename,500000);
		$page->navigate('http://localhost/logout.php')->waitForNavigation();
	} finally {
		// bye
		$browser->close();
	}
	//SOCKETLABS CONFIG//
	$SocketID = 42191;
	$APIKey = "Kr86CiGz24Bes9F7Wyk5";
	//Set up the socketlabs client
	$client = new SocketLabsClient($SocketID, $APIKey);
	$message = new BasicMessage();
	$message->subject = "TEST EMAIL: Statement of Account from Town and Country Meats";
	$message->htmlBody = "<html>Please find attached a statement of account from Town and Country Meats Group.</html>";
	$message->from = new EmailAddress("noreply-api@townandcountrymeats.co.uk");
	$message->addToAddress("andrew.gosling@tang.solutions");
    
	$attachment = \Socketlabs\Message\Attachment::createFromPath(__DIR__ . DIRECTORY_SEPARATOR .$filename, $filename2, "PDF", "Statement of Account");
	$message->attachments[] = $attachment;
	//Generate a Unique Identifier for this Email
	$message->messageId = generate_uuid();

	$response = $client->send($message);
    return "done";

	
}
//Main Decleration
echo renderPDF($customerID);

?>