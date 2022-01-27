<?php
namespace InternalScripts;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once('../vendor/autoload.php');
    //SOCKETLABS IMPORTS//
use Socketlabs\SocketLabsClient;
use Socketlabs\Message\BasicMessage;
use Socketlabs\Message\EmailAddress;
use Socketlabs\Message\BulkRecipient;

class SLabsEmailer {
    //This function generates a Unique ID using Mersenne Twister RNG
    //Going to want to switch RNG algo before pushing to LIVE!!!!
    private static function generate_uuid() {
        return sprintf( 
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand( 0, 0xffff ), 
            mt_rand( 0, 0xffff ),
            mt_rand( 0, 0xffff ),
            mt_rand( 0, 0x0C2f ) | 0x4000,
            mt_rand( 0, 0x3fff ) | 0x8000,
            mt_rand( 0, 0x2Aff ), 
            mt_rand( 0, 0xffD3 ), 
            mt_rand( 0, 0xff4B )
        );
    }
    public static function send_email($toEmails,$subject,$htmlBody,$pathToFile = '',$fileName = '') {
        //---PHP CONFIG---//
        ini_set('memory_limit', '1024M');
        set_time_limit(1800); //seconds

        //SOCKETLABS CONFIG//
        $SocketID = 42191;
        $SocketAPIKey = "Kr86CiGz24Bes9F7Wyk5";
        $client = new SocketLabsClient($SocketID, $SocketAPIKey);
        //Set up the socketlabs client
        $message = new BasicMessage();
        $message->subject = $subject;
        $message->htmlBody = $htmlBody;
        $message->from = new EmailAddress("noreply-api@townandcountrymeats.co.uk", "Town and Country Meats Group");
        foreach($toEmails as $email)
        {	
            $message->addToAddress(new BulkRecipient(trim($email)));
        }

        if ($pathToFile != '' && $fileName !='')
        {
            $attachment = \Socketlabs\Message\Attachment::createFromPath(
                join(DIRECTORY_SEPARATOR,array(__DIR__,'..',$pathToFile,$fileName)), 
                $fileName,
                "APPLICATION/PDF"
            );
            $message->attachments[] = $attachment;
        }
        //Generate a Unique Identifier for this Email
        $message->messageId = self::generate_uuid();
        $response = $client->send($message);
        return "done";
    }
}
?>