<?php
require_once(__DIR__.'/../functions.php');

if (isset(request('start']) && $_POST['start']!="" && isset($_POST['end']) && $_POST['end')!="") 
{
    $start = DateTime::createFromFormat("d/m/Y",request('start'))->format('Y-m-d');
    $end  = DateTime::createFromFormat("d/m/Y",request('end'))->format('Y-m-d');
    $res = prepareExecuteQuery("SELECT * FROM pickerSheets WHERE (STR_TO_DATE(`estimated_delivery_date`, '%d/%c/%Y') BETWEEN ? AND ?) ORDER BY id ASC",'ss',[$start,$end]);
}
else if (isset(request('startInv']) && $_POST['startInv']!="" && isset($_POST['end']) && $_POST['end')!="") 
{
    $start = request('startInv');
    $end  = DateTime::createFromFormat("d/m/Y",request('end'))->format('Y-m-d');
    $res = prepareExecuteQuery("SELECT * FROM pickerSheets WHERE id >= ? AND STR_TO_DATE(`estimated_delivery_date`, '%d/%c/%Y')< ? ORDER BY id ASC",'is',[$start,$end]);
}
else if (isset(request('start']) && $_POST['start']!="" && isset($_POST['endInv']) && $_POST['endInv')!="") 
{
    $start = DateTime::createFromFormat("d/m/Y",request('start'))->format('Y-m-d');
    $end  = request('endInv');
    $res = prepareExecuteQuery("SELECT * FROM pickerSheets WHERE STR_TO_DATE(`estimated_delivery_date`, '%d/%c/%Y') >= ? AND `id` <= ? ORDER BY id ASC",'si',[$start,$end]);
}
else if (isset(request('startInv']) && $_POST['startInv']!="" && isset($_POST['endInv']) && $_POST['endInv')!="") 
{
    $start = request('startInv');
    $end  = request('endInv');
    $res = prepareExecuteQuery("SELECT * FROM pickerSheets WHERE `id` >= ? AND `id` <= ? ORDER BY id ASC",'ss',[$start,$end]);
}

$totPrevOut = 0;
$rolSaleVal = 0;
$rolPayment = 0;
$list = mysqli_fetch_all($res, MYSQLI_ASSOC);

$sql = "SELECT key_value FROM system_settings WHERE key_name = 'FINANCIAL_REPORT'";
$res1 = prepareExecuteQuery($sql);

$firstid = 0;
$lastid = 0;
$aborted = 0;
$first_date = null;
$end_date = null;
foreach ($list as $pick)
{
    if ($pick['deleted']!=0 || $pick['customer_id']==0) continue;
    if ($firstid == 0) 
    {
        $firstid = $pick['id'];
        $first_date = $pick['estimated_delivery_date'];
    }
    $lastid = $pick['id'];
    $end_date = $pick['estimated_delivery_date'];
    if ($pick['completed'] !=1)
    {
        $aborted = $pick['id'];
        break;
    }
    $sql = "SELECT SUM(amount) as amount FROM invoice_payments WHERE invoice_payments.payment_method != 'CREDIT_NOTE' && invoice_payments.invoice_id = ?";
    $res = prepareExecuteQuery($sql,'i',[$pick['id']]) or die(mysqli_error($conn)." ". $sql);
   
    $thisInvTotal = number_format((float)invoiceTotal($pick['id']), 2, '.', '');
    $thisPayment = mysqli_fetch_assoc($res)['amount'];
    $thisPayment = number_format((float)totalValueCreditedOnInvoiceID($pick['id']), 2, '.', '');
    
    $rolSaleVal += $thisInvTotal;  
    $rolPayment += $thisPayment;
}

$output = array();
$output['user_id'] = $userid;
$output['previous_id'] = request('previous_id');
$output['sales'] = round($rolSaleVal,2,PHP_ROUND_HALF_DOWN);
$output['payments'] = round($rolPayment,2,PHP_ROUND_HALF_DOWN);
$output['start_invoice_id'] = $firstid;
$output['end_invoice_id'] = $lastid;
$output['aborted_id'] = $aborted;
$output['start_date'] = date('d/m/Y H:i:s', strtotime($first_date));
$output['end_date'] = date('d/m/Y H:i:s', strtotime($end_date));
if (isset(request('previous_id']) && $_POST['previous_id') != null)
{
    $output['previous'] = get_previous(request('previous_id'));
}
else if (isset(request('previous_value']) && $_POST['previous_value'] != null && $_POST['previous_value'] != "" && $_POST['previous_value'] != 0 && $_POST['previous_value') != "0")
{
    $output['previous'] = array(
        'rolTotal'=>request('previous_value'),
        'start_invoice_id'=>0,
        'end_invoice_id'=>0
    );
}
if (array_key_exists('previous',$output) && $output['previous'] != null && array_key_exists('rolTotal',$output['previous'])) 
{
    $output['rolTotal'] = round(($output['sales'] + $output['previous']->rolTotal) - $output['payments'],2,PHP_ROUND_HALF_DOWN);
}
else 
{
    $output['rolTotal'] = round($output['sales'] - $output['payments'],2,PHP_ROUND_HALF_DOWN);
}
echo json_encode($output);
function get_previous($id)
{
    global $mysqli;
    global $userid;
    $sql = "SELECT * FROM `finance_report_history` WHERE `user_id` = $userid AND id = ?";
    $res = prepareExecuteQuery($sql,'i',[$id]);
    $output = mysqli_fetch_assoc($res);
    if ($output != null)
    {
        if ($output['previous_id'] != null && $output['previous_id'] > 0)
        {
            $output['previous'] = get_previous($output['previous_id']);
            $output['rolTotal'] = round(($output['sales'] + $output['previous']['rolTotal']) - $output['payments'],2,PHP_ROUND_HALF_DOWN);
        }
        else
        {
            $output['rolTotal'] = round($output['sales'] - $output['payments'],2,PHP_ROUND_HALF_DOWN);
        }
    }
    return $output;
}
?>