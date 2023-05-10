<?php
    require(__DIR__.'/../functions.php');
    require('../scripts/SLabsEmailer.php');
    use InternalScripts\SLabsEmailerStatus;

    $customer_id = request()->input('customer_id');
    $customerSTMemails = prepareExecuteQuery("SELECT * FROM `mail_tracking` WHERE customer_id = ? AND `type` = 'STATEMENT'  ORDER BY `mail_tracking`.`id` DESC",'i',[$customer_id]);
    //Check if due days is a string if so strip out the numbers
    while ($email = mysqli_fetch_assoc($customerSTMemails)) {
        $trafficColour = SLabsEmailerStatus::getTrafficStatus($email['status']);
        $tooltip = SLabsEmailerStatus::getTextStatus($email['status'],$email['secondary_code']);
        if (strpos($email['addressee'],"townandcountrymeats.co.uk") == -1) break;
    ?>
    <tr>
        <td align="left" value=""><?php echo $email['addressee'] ?></td>
        <td align="center" value=""><?php echo $email['date_sent'] ?></td>
        <td align="center" value=""><a target="_blank" href="<?php echo $email['attachments'] ?>">Click Here to View</a></td>
        <td align="center" value=""><?php echo $email['last_update'] ?></td>
        <td align="right" value=""><?php echo $email['status'] ?></td>
        <td align="right" title="<?php echo $tooltip; ?>"style="background-color:<?php echo $trafficColour; ?>"></td>
   </tr>
    <?php
    }
    ?>