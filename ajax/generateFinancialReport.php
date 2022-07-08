<?php
require_once('../functions.php');
if (isset($_POST['start']) && $_POST['start']!="") $start = $_POST['start'];
else $start = "2022-04-19 00:00:00";

if (isset($_POST['end']) && $_POST['end']!="") $end = $_POST['end'];
else $end = "2022-04-26 00:00:00";

$sql = "SELECT key_value FROM system_settings WHERE key_name = 'FINANCIAL_REPORT')";
//$res = mysqli_query($conn, $sql) or die(mysqli_error($conn)." ". $sql);

$totPrevOut = 0;
$curSaleVal = 0;
$curPayment = 0;

$sql = "SELECT id FROM pickerSheets WHERE (`date` BETWEEN '".$start."' AND '".$end."')";
$res = mysqli_query($conn, $sql) or die(mysqli_error($conn)." ". $sql);
$list = mysqli_fetch_all($res, MYSQLI_ASSOC);
foreach ($list as $pick)
{
    $curSaleVal += number_format((float)invoiceTotal($pick['id']), 2, '.', '');
    $sql = "SELECT SUM(amount) as amount FROM invoice_payments WHERE invoice_payments.payment_method != 'CREDIT_NOTE' && invoice_payments.invoice_id = ".$pick['id'];
    $res = mysqli_query($conn, $sql) or die(mysqli_error($conn)." ". $sql);
    $curPayment +=  mysqli_fetch_assoc($res)['amount'];
    $curPayment += number_format((float)totalValueCreditedOnInvoiceID($pick['id']), 2, '.', '');
}

$output = array();
$output['totPrevOut'] = round($totPrevOut,2,PHP_ROUND_HALF_DOWN);
$output['curSaleVal'] = round($curSaleVal,2,PHP_ROUND_HALF_DOWN);
$output['curPayment'] = round($curPayment,2,PHP_ROUND_HALF_DOWN);

echo json_encode($output);
?>