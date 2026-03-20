<?php
        require_once(__DIR__.'/../functions.php');
        require_once(__DIR__.'/../scripts/PDFRenderer.php');
        require_once(__DIR__.'/../scripts/SLabsEmailer.php');
        use InternalScripts\SLabsEmailer;
        use InternalScripts\SLabsEmailerType;
        use InternalScripts\PDFRenderer;
        if(request()->input('id') != ''){

            $delid = request()->input('id');

            $customerResult = prepareExecuteQuery("SELECT `customer_id` FROM `pickerSheets` WHERE `id` = $delid");
            $customerID = mysqli_fetch_array($customerResult)['customer_id'];

            $customerQueryResult = prepareExecuteQuery("SELECT businessname,customer_email,accounts_email,internal_email FROM `customers` WHERE id = $customerID");
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
            PDFRenderer::generatePDFfromWeb('viewSalesRetraction.php?id='.request()->input('id'),$pathToFile,$fileName);
            SLabsEmailer::send_email($customerID,SLabsEmailerType::Retraction,$customer_emails,$subject,$htmlBody,$pathToFile,$fileName);
            $picksheetResult = prepareExecuteQuery("UPDATE `pickerSheets` SET deleted=1, deleted_by_user_id=$userid WHERE id='$delid'");

            $pickerItemsResult = prepareExecuteQuery("UPDATE `pickerItems` SET deleted=1 WHERE pickersheet_id='$delid'");

            $pickWeightOutResult = prepareExecuteQuery("SELECT * FROM `pickWeightOut` WHERE pickersheet_id='$delid'");

            while($palletOut = mysqli_fetch_array($pickWeightOutResult)){
                $weightIDS = $palletOut['weight_ids'];

                $deleteWeightsResult = prepareExecuteQuery("UPDATE `weights` SET status_id='0' WHERE id IN ($weightIDS)");
            }

            $x = "DELETE FROM `pickWeightOut` WHERE pickersheet_id='$delid'";
            $y = prepareExecuteQuery($x);

        }
?>
