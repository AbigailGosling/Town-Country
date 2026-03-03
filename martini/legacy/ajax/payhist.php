<?php
    require(__DIR__.'/../functions.php');
    $sql = "SELECT id,name FROM users";
    $usersT = mysqli_fetch_all(prepareExecuteQuery($sql),MYSQLI_ASSOC);
    $users = array();

    foreach($usersT as $user)
    {
        $users[$user['id']] = $user;
    }

    $sql = "SELECT id,businessname FROM customers";
    $customersT = mysqli_fetch_all(prepareExecuteQuery($sql),MYSQLI_ASSOC);
    $customers = array();

    foreach($customersT as $customer)
    {
        $customers[$customer['id']] = $customer;
    }

    $sql = "SELECT invoice_payments.*,pickerSheets.customer_id FROM invoice_payments INNER JOIN pickerSheets ON invoice_payments.invoice_id = pickerSheets.id WHERE invoice_payments.created_at > NOW() - INTERVAL 6 MONTH AND pickerSheets.is_return_to_supplier = 0 ORDER BY invoice_payments.id DESC";
    $invoice_payments = mysqli_fetch_all(prepareExecuteQuery($sql),MYSQLI_ASSOC);

    foreach($invoice_payments as $payment)
    {
        $isNeg = false;
        if ($payment['payment_method'] == "CREDIT_NOTE") $payment['amount'] = creditNoteTotal($payment['id']);
        if ($payment['amount'] < 0)
        {
            $payment['amount'] = $payment['amount'] * -1;
            $isNeg = true;
        }
        $payment['customer_businessname'] = $customers[$payment['customer_id']]['businessname'];
        $payment['user_name'] = $users[$payment['payment_recorded_by']]['name'];
?>
<tr class="" role="row">
        <td><?php echo $payment['invoice_id']; ?></td>
        <td><?php echo $payment['customer_businessname']; ?></td>
        <td><?php echo $payment['payment_method']; ?></td>
        <td><?php if($isNeg) echo "- "; ?>£<?php echo number_format($payment['amount'],2); ?></td>
        <td class="" value=""><?php echo $payment['meta_data']; ?></td>
        <td class="" value=""><?php echo $payment['user_name']; ?></td>
        <td class="" value=""><?php echo $payment['created_at']; ?></td>
</tr>
<?php
    }
?>
