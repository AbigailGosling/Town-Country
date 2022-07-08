<?php
namespace InternalScripts;
require_once(join(DIRECTORY_SEPARATOR,array(__DIR__,'../functions.php')));
require_once(join(DIRECTORY_SEPARATOR,array(__DIR__,'../vendor/autoload.php')));
    //SOCKETLABS IMPORTS//
use Socketlabs\SocketLabsClient;
use Socketlabs\Message\BasicMessage;
use Socketlabs\Message\EmailAddress;
use Socketlabs\Message\BulkRecipient;

class SLabsEmailer {
    //This function generates a Unique ID using Mersenne Twister RNG
    //Going to want to switch RNG algo before pushing to LIVE!!!!

    //SOCKETLABS CONFIG//
    const SocketID = 42191;
    const InjectionAPIKey = "Kr86CiGz24Bes9F7Wyk5";
    const NotifcationAPIKey="Te8y2S5NfCq6a9LRt74X";
    static function generate_uuid() {
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
    public static function send_email($customerID,$type,$toEmails,$subject,$htmlBody,$pathToFile = '',$fileName = '',$document_id =null) {
        global $conn;
        if ($document_id == null) $document_id = "NULL";
        //---PHP CONFIG---//
        ini_set('memory_limit', '1024M');
        set_time_limit(1800); //seconds

        $client = new SocketLabsClient(self::SocketID, self::InjectionAPIKey);
        foreach($toEmails as $email)
        {	
        $trimmed = trim($email);
        //Set up the socketlabs client
        $message = new BasicMessage();
        $message->subject = $subject;
        $message->htmlBody = $htmlBody;
        $message->from = new EmailAddress("noreply-api@townandcountrymeats.co.uk", "Town and Country Meats Group");
            $message->addToAddress(new BulkRecipient($trimmed));
    
        $fullExplainedPath = "NULL";
        if ($pathToFile != '' && $fileName !='')
        {
            $fullExplainedPath = "'".join(DIRECTORY_SEPARATOR,array($pathToFile,$fileName))."'";
            $attachment = \Socketlabs\Message\Attachment::createFromPath(
                join(DIRECTORY_SEPARATOR,array(__DIR__,'..',$pathToFile,$fileName)), 
                $fileName,
                "APPLICATION/PDF"
            );
            $message->attachments[] = $attachment;
        }
        //Generate a Unique Identifier for this Email
        $mid = self::generate_uuid();
        $message->messageId = $mid;
        $response = $client->send($message);
            $sql = "INSERT INTO `tandc_live`.`mail_tracking` (`customer_id`, `document_id`, `addressee`, `message_id`, `type`, `status`, `attachments`, `date_sent`) VALUES ($customerID, $document_id, '$trimmed', '$mid', '$type', '".SLabsEmailerStatus::Sending."', $fullExplainedPath, NOW())";
            mysqli_query($conn, $sql) or die(mysqli_error($conn)." ". $sql);
            
        }

        return "done";
    }
    public static function process_notification($data) {
        global $conn;
        $addressee = $data['Address'];
        $message_id = $data['MessageId'];
        $secondary_code = (isset($data['FailureCode']))?$data['FailureCode']:0;

        $status_code = SLabsEmailerStatus::Unknown;
        if (isset($data['FailureCode']))
        {
            switch ($data['FailureCode']){
                case 0:
                    $status_code = SLabsEmailerStatus::TempFail;
                    break;
                case 1:
                    $status_code = SLabsEmailerStatus::PermFail;
                    break;
                case 2:
                    $status_code = SLabsEmailerStatus::Suppressed;
                    break;   
            }
        }
        else
        {
            switch ($data['Type']){
                case "Failed":
                    $status_code = SLabsEmailerStatus::TempFail;
                    break;
                case "Complaint":
                    $status_code = SLabsEmailerStatus::Complaint;
                    break;   
                case "Delivered":
                    $status_code = SLabsEmailerStatus::Received;
                    break;
                case "Tracking":
                    $status_code = SLabsEmailerStatus::Open;
                    break;  
            }
        }

        mysqli_query($conn, "UPDATE `mail_tracking` SET `status`='$status_code',`secondary_code`=$secondary_code WHERE `addressee`='$addressee' AND `message_id`='$message_id'") or die(mysqli_error($conn));
    }
}
abstract class SLabsEmailerType
{
    const Statment  = 'STATEMENT';
    const Sales     = 'SALES_CONFIRMATION';
    const CrdtAlert = 'CREDIT_ALERT';
}
abstract class SLabsEmailerStatus
{
    const Sending   = 'SENDING';
    const Sent      = 'SENT';
    const TempFail  = 'TEMP_FAIL';
    const PermFail  = 'PERM_FAIL';
    const Suppressed= 'SUPPRESSED';
    const Complaint = 'COMPLAINT';
    const Received  = 'RECEIVED';
    const Open      = 'OPENED';
    const Unknown   = 'UNKNOWN';

    static function getTrafficStatus($status){
        $trafficColour = "black";
        switch ($status){
            case SLabsEmailerStatus::Sending:          
            case SLabsEmailerStatus::TempFail:
            case SLabsEmailerStatus::PermFail:
            case SLabsEmailerStatus::Suppressed:
            case SLabsEmailerStatus::Complaint:
                $trafficColour = "red";
                break;
            case SLabsEmailerStatus::Sent:
            case SLabsEmailerStatus::Received:
                $trafficColour = "orange";
                break;
            case SLabsEmailerStatus::Open:
                $trafficColour = "green";
                break;
        }
        return $trafficColour;
    }
    static function getTextStatus($status,$secondary_code){
        global $conn;
        $returningValue = null;
        switch ($status){
            case SLabsEmailerStatus::Sending:          
            case SLabsEmailerStatus::Sent:
                $returningValue = "Sending";
                break;
            case SLabsEmailerStatus::Received:
                $returningValue = "Received but Unopened";
                break;
        }
        if ($returningValue == null)
        {
            $q = mysqli_query($conn, "SELECT `value` FROM `mail_tracking_codes` WHERE `id` = $secondary_code") or die(mysqli_error($conn));
            $returningValue = mysqli_fetch_assoc($q);
            $returningValue = $returningValue['value'];
        }
        return $returningValue;
    }
}
?>