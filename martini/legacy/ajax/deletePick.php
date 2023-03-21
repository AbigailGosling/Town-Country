<?php
        include_once('../functions.php');
        include_once('../scripts/SLabsEmailer.php');
        include_once('../scripts/PDFRenderer.php');
        use InternalScripts\SLabsEmailer;
        use InternalScripts\SLabsEmailerType;
        use InternalScripts\PDFRenderer;
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        if($_POST['id'] != ''){
    
            $delid = mysqli_real_escape_string($conn, $_POST['id']);

            $customerResult = mysqli_query($conn, "SELECT `customer_id` FROM `pickerSheets` WHERE `id` = $delid");
            $customerID = mysqli_fetch_array($customerResult)['customer_id'];
            
            $customerQueryResult = mysqli_query($conn, "SELECT businessname,customer_email,accounts_email,internal_email FROM `customers` WHERE id = $customerID");
            $customer = mysqli_fetch_assoc($customerQueryResult);
            if ($customer['customer_email']!= null && $customer['customer_email']!= "")
            {
                $customer_emails = explode(";",$customer['customer_email']);
            }
            else if ($customer['accounts_email']!= null && $customer['accounts_email']!= "")
            {
                $customer_emails = explode(";",$customer['accounts_email']);
            }
            else
            {
                $customer_emails = explode(";",$customer['internal_email']);
            }
            $subject = "NOTICE: Sale Retraction ".$delid." from Town and Country Meats";
	        $htmlBody = "<html>Please find attached a sale retraction from Town and Country Meats Group for ".$customer['businessname']." Invoice No: ".$delid.".</html>";
            $statementDate = time();
            $fileName = 'SalesRetraction_'.$delid.'_'.$statementDate.'.pdf';
            $pathToFile = 'PDF';
            PDFRenderer::generatePDFfromWeb('viewSalesRetraction.php?id='.$_POST['id'],$pathToFile,$fileName);
            SLabsEmailer::send_email($customerID,SLabsEmailerType::Retraction,$customer_emails,$subject,$htmlBody,$pathToFile,$fileName);

            $picksheetResult = mysqli_query($conn, "UPDATE `pickerSheets` SET deleted=1, deleted_by_user_id=$userid WHERE id='$delid'");
    
            $pickerItemsResult = mysqli_query($conn, "UPDATE `pickerItems` SET deleted=1 WHERE pickersheet_id='$delid'");
    
            $palletsOutResult = mysqli_query($conn, "SELECT * FROM `palletsOut` WHERE pickersheet_id='$delid'");
    
            while($palletOut = mysqli_fetch_array($palletsOutResult)){
                $weightIDS = $palletOut['weight_ids'];
    
                $deleteWeightsResult = mysqli_query($conn, "UPDATE `weights` SET status_id='0' WHERE id IN ($weightIDS)");
            }
    
            $x = "DELETE FROM `palletsOut` WHERE pickersheet_id='$delid'";
            $y = mysqli_query($conn, $x);

        }
?>