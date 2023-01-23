<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    include('includes/frontHeader.php');
    require 'vendor/autoload.php';    

	$pickersheet_id = request('id');

	$x = "SELECT * FROM `pickerSheets` WHERE id=?";
	$y = prepareExecuteQuery($x,'i',[$pickersheet_id]);
    $pickSheetRow = mysqli_fetch_array($y);

    $customer_id = $pickSheetRow['customer_id'];

    $customer = getCustomer($customer_id);

    $link = request('link');


    // Instantiation and passing `true` enables exceptions
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->SMTPDebug = 0;
        $mail->isSMTP();                                            // Send using SMTP
        $mail->SMTPSecure = "tls";
        $mail->Host       = $mail_host;                    // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = $mail_email;                     // SMTP username
        $mail->Password   = $mail_password;                               // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $mail->Port       = $mail_port;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

        //Recipients
        $mail->setFrom($mail_from_address, 'Town & Country');
        $mail->addAddress($customer['accounts_email']);     // Add a recipient
       
        // Attachments
        $mail->addAttachment(request('link'));         // Add attachments

        // Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = 'Town & Country Invoice #' . $pickersheet_id;
        $mail->Body    = '
                        <p style="font-family:16px;font-family: Verdana, sans-serif;">'. $customer['businessname'] .',<br/></p>
                        <p style="font-family:16px;font-family: Verdana, sans-serif;">Please see attached PDF for Invoice <b>#'. $pickersheet_id .'</b></p>
                        ';

        $mail->send();
        ?><script> window.location.href = 'invoice.php?id=<?php echo $pickersheet_id; ?>&msg=Invoice Sent'; </script><?php
    } catch (Exception $e) {
        echo "Message could not be sent. Please contact support (#" . $pickersheet_id  . ')';
    }
?> 