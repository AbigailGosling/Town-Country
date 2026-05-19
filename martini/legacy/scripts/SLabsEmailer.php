<?php
namespace InternalScripts;
require_once(join(DIRECTORY_SEPARATOR,array(__DIR__,'../functions.php')));
require_once(join(DIRECTORY_SEPARATOR,array(__DIR__,'../../vendor/autoload.php')));
require_once(__DIR__.'/../../vendor/laravel/framework/src/Illuminate/Support/Facades/Log.php');
    //SOCKETLABS IMPORTS//
use Socketlabs\SocketLabsClient;
use Socketlabs\Message\BasicMessage;
use Socketlabs\Message\EmailAddress;
use Socketlabs\Message\BulkRecipient;
use \Socketlabs\Message\Attachment;
use Illuminate\Support\Facades\Log;

class SLabsEmailer {
    //SOCKETLABS CONFIG//
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
    public static function send_email(int $customerID, string $type, array $toEmails, string $subject, string $htmlBody, string $pathToFile = '', string $fileName = '', $document_id = null, bool $isAbsolPath = false) {
        global $mysqli;
        if (env("APP_DEBUG",true)==true) $toEmails = [env("MAIL_TEST_ADDRESS")];
        if ($document_id == null) $document_id = "NULL";
        //---PHP CONFIG---//
        ini_set('memory_limit', '1024M');
        set_time_limit(1800); //seconds

        $client = new SocketLabsClient(env("MAIL_SOCKET_ID"), env("MAIL_INJECTION_KEY"));
        foreach($toEmails as $email)
        {
            $trimmed = trim($email);
            if ($trimmed == "")continue;

            //Set up the socketlabs client
            $message = new BasicMessage();
            $message->subject = $subject;
            $message->plainTextBody = $message->htmlBody = $htmlBody;
            $message->charset = "utf-8";
            $message->from = new EmailAddress(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'). " Group");

            $fullExplainedPath = "NULL";
            if ($pathToFile != '' && $fileName !='')
            {
                $fullExplainedPath = "$pathToFile/$fileName";
                try {
                    $p = ($isAbsolPath == false)?join(DIRECTORY_SEPARATOR,array(__DIR__,'..',$pathToFile,$fileName)):$pathToFile.DIRECTORY_SEPARATOR.$fileName;
                    $attachment = Attachment::createFromPath(
                        $p,
                        $fileName,
                        "APPLICATION/PDF"
                    );
                    $message->attachments[] = $attachment;
                }
                catch (\Exception $exc){
                    Log::error($exc,[$customerID,$type,$toEmails,$subject,$htmlBody,$pathToFile,$fileName,$document_id]);
                    return "error";
                }
            }
            //Generate a Unique Identifier for this Email
            $mid = self::generate_uuid();
            $message->mailingId = $message->messageId = $mid;

            $sql = "INSERT INTO `tandc_live`.`mail_tracking` (`customer_id`, `document_id`, `addressee`, `message_id`, `type`, `status`, `attachments`, `date_sent`) VALUES ($customerID, $document_id, '$trimmed', '$mid', '$type', '".SLabsEmailerStatus::Sending."', ?, NOW())";
            prepareExecuteQuery($sql,'s',[$fullExplainedPath]);

            try
            {
                $message->addToAddress(new BulkRecipient($trimmed));
                $response = $client->send($message);
                if ($response->result != "Success") {
                    Log::error(new \Exception(json_encode(['response'=>$response,'customerID'=>$customerID,'type'=>$type,'toEmails'=>$toEmails,'subject'=>$subject,'htmlBody'=>$htmlBody,'pathToFile'=>$pathToFile,'fileName'=>$fileName,'document_id'=>$document_id])));
                }
            }
            catch (\Exception $e)
            {
                Log::error($e,[$customerID,$type,$toEmails,$subject,$htmlBody,$pathToFile,$fileName,$document_id]);
                return "error";
            }

        }

        return "done";
    }
    public static function process_notification($data) {
        global $mysqli;
        $addressee = $data['Address'];
        $message_id = $data['MessageId'];
        $t = prepareExecuteQuery("SELECT * FROM `mail_tracking` WHERE `addressee`='$addressee' AND `message_id`='$message_id'");

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
    const SuppReturn= 'SUPPLIER_RETURN';
    const ShortStock= 'SHORT_STOCK_NOTICE';
    const Reservatin= 'RESERVATION';
    const ShortPick = 'SHORT_PICK';
    const PrceChnge = 'PRICE_CHANGE';
    const TwoFactor = 'TWO_FACTOR_CODE';
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
