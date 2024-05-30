<?php
namespace InternalScripts;
require_once(join(DIRECTORY_SEPARATOR,array(__DIR__,'../functions.php')));
require_once(join(DIRECTORY_SEPARATOR,array(__DIR__,'../vendor/autoload.php')));
require_once(__DIR__.'/../../vendor/laravel/framework/src/Illuminate/Support/Facades/Log.php');
    //SOCKETLABS IMPORTS//
use Socketlabs\SocketLabsClient;
use Socketlabs\Message\BasicMessage;
use Socketlabs\Message\EmailAddress;
use Socketlabs\Message\BulkRecipient;
use Illuminate\Support\Facades\Log;

class SLabsEmailer {
    //This function generates a Unique ID using Mersenne Twister RNG
    //Going to want to switch RNG algo before pushing to LIVE!!!!

    //SOCKETLABS CONFIG//
    const SocketID = 42191;
    const InjectionAPIKey = "Kr86CiGz24Bes9F7Wyk5";
    const NotifcationAPIKey="Zq39SfPb75Ddi4CWa2n";
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
        global $mysqli;
        
        if ($document_id == null) $document_id = "NULL";
        //---PHP CONFIG---//
        ini_set('memory_limit', '1024M');
        set_time_limit(1800); //seconds

        $client = new SocketLabsClient(self::SocketID, self::InjectionAPIKey);
        foreach($toEmails as $email)
        {	
            
            //Set up the socketlabs client
            $message = new BasicMessage();
            $message->subject = $subject;
            $message->htmlBody = $htmlBody;
            $message->from = new EmailAddress("noreply-api@townandcountrymeats.co.uk", "Town and Country Meats Group");
            
        
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
            $trimmed = trim($email);

            $sql = "INSERT INTO `tandc_live`.`mail_tracking` (`customer_id`, `document_id`, `addressee`, `message_id`, `type`, `status`, `attachments`, `date_sent`) VALUES ($customerID, $document_id, '$trimmed', '$mid', '$type', '".SLabsEmailerStatus::Sending."', $fullExplainedPath, NOW())";
            prepareExecuteQuery($sql);

            try
            {
                $message->addToAddress(new BulkRecipient($trimmed));
                $response = $client->send($message);
            }
            catch (\Exception $e) 
            {
                Log::error($e);
            }
            
        }

        return "done";
    }
    public static function process_notification($data) {
        global $mysqli;
        $addressee = $data['Address'];
        $message_id = $data['MessageId'];
       
        $t = prepareExecuteQuery("SELECT * FROM `mail_tracking` WHERE `addressee`='$addressee' AND `message_id`='$message_id'");
        if (mysqli_num_rows($t) == 0 && $_SERVER['SERVER_NAME'] != "13.40.103.56")
        {
            /*ob_start();
            header('Location: https://tcdev.tang.solutions//scripts/SLabsNotifier.php');
            ob_end_flush();
            die();
            exit();*/
        }
        http_response_code(200);
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

        prepareExecuteQuery("UPDATE `mail_tracking` SET `status`='$status_code',`secondary_code`=$secondary_code WHERE `addressee`='$addressee' AND `message_id`='$message_id'") or die(mysqli_error($mysqli));
    }
}
abstract class SLabsEmailerType
{
    const Statment  = 'STATEMENT';
    const Sales     = 'SALES_CONFIRMATION';
    const CrdtAlert = 'CREDIT_ALERT';
    const Retraction= 'RETRACTION';
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
        global $mysqli;
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
            $q = prepareExecuteQuery("SELECT `value` FROM `mail_tracking_codes` WHERE `id` = $secondary_code") or die(mysqli_error($mysqli));
            $returningValue = mysqli_fetch_assoc($q);
            $returningValue = $returningValue['value'];
        }
        return $returningValue;
    }
}
?>