<?php
require_once('../functions.php');

if (isset($_POST['start']) && $_POST['start']!="" && isset($_POST['end']) && $_POST['end']!="") 
{
    $start = DateTime::createFromFormat("d/m/Y",$_POST['start'])->format('Y-m-d');
    $end  = DateTime::createFromFormat("d/m/Y",$_POST['end'])->format('Y-m-d');
    $sql = "SELECT * FROM pickerSheets WHERE (`estimated_delivery_date` BETWEEN '".$start."' AND '".$end."') ORDER BY id ASC";
}
else if (isset($_POST['startInv']) && $_POST['startInv']!="" && isset($_POST['end']) && $_POST['end']!="") 
{
    $start = $_POST['startInv'];
    $end  = DateTime::createFromFormat("d/m/Y",$_POST['end'])->format('Y-m-d');
    $sql = "SELECT * FROM pickerSheets WHERE id >= ".$start." AND `estimated_delivery_date` < '".$end."' ORDER BY id ASC";
}
else if (isset($_POST['start']) && $_POST['start']!="" && isset($_POST['endInv']) && $_POST['endInv']!="") 
{
    $start = DateTime::createFromFormat("d/m/Y",$_POST['start'])->format('Y-m-d');
    $end  = $_POST['endInv'];
    $sql = "SELECT * FROM pickerSheets WHERE `estimated_delivery_date` >= ".$start." AND `id` < '".$end."' ORDER BY id ASC";
}
else if (isset($_POST['startInv']) && $_POST['startInv']!="" && isset($_POST['endInv']) && $_POST['endInv']!="") 
{
    $start = $_POST['startInv'];
    $end  = $_POST['endInv'];
    $sql = "SELECT * FROM pickerSheets WHERE id >= ".$start." AND `estimated_delivery_date` < '".$end."' ORDER BY id ASC";
}

$totPrevOut = 0;
$rolSaleVal = 0;
$rolPayment = 0;
$res = mysqli_query($conn, $sql) or die(mysqli_error($conn)." ". $sql);
$list = mysqli_fetch_all($res, MYSQLI_ASSOC);

$sql = "SELECT key_value FROM system_settings WHERE key_name = 'FINANCIAL_REPORT'";
$res1 = mysqli_query($conn, $sql) or die(mysqli_error($conn)." ". $sql);

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
    $sql = "SELECT SUM(amount) as amount FROM invoice_payments WHERE invoice_payments.payment_method != 'CREDIT_NOTE' && invoice_payments.invoice_id = ".$pick['id'];
    $res = mysqli_query($conn, $sql) or die(mysqli_error($conn)." ". $sql);
   
    $thisInvTotal = number_format((float)invoiceTotal($pick['id']), 2, '.', '');
    $thisPayment = mysqli_fetch_assoc($res)['amount'];
    $thisPayment = number_format((float)totalValueCreditedOnInvoiceID($pick['id']), 2, '.', '');
    
    $rolSaleVal += $thisInvTotal;  
    $rolPayment += $thisPayment;
}

$output = array();
$output['user_id'] = $userid;
$output['previous_id'] = $_POST['previous_id'];
$output['sales'] = round($rolSaleVal,2,PHP_ROUND_HALF_DOWN);
$output['payments'] = round($rolPayment,2,PHP_ROUND_HALF_DOWN);
$output['start_invoice_id'] = $firstid;
$output['end_invoice_id'] = $lastid;
$output['aborted_id'] = $aborted;
$output['start_date'] = date('d/m/Y H:i:s', strtotime($first_date));
$output['end_date'] = date('d/m/Y H:i:s', strtotime($end_date));
if (isset($_POST['previous_id']) && $_POST['previous_id'] != null)
{
    $output['previous'] = get_previous($_POST['previous_id']);
}
else if (isset($_POST['previous_value']) && $_POST['previous_value'] != null && $_POST['previous_value'] != "" && $_POST['previous_value'] != 0 && $_POST['previous_value'] != "0")
{
    $output['previous'] = array(
        'rolTotal'=>$_POST['previous_value'],
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
    global $conn;
    global $userid;
    $sql = "SELECT * FROM `finance_report_history` WHERE `user_id` = $userid AND id = $id";
    $res = mysqli_query($conn, $sql) or die(mysqli_error($conn)." ". $sql);
    $output = mysqli_fetch_assoc($res);
    if ($output != null)
    {
        if ($output['previous_id'] != null && $output['previous_id'] > 0)
        {
            $output['previous'] = get_previous($output['previous_id']);
            $output['rolTotal'] = round(($output['sales'] + $output['previous']->rolTotal) - $output['payments'],2,PHP_ROUND_HALF_DOWN);
        }
        else
        {
            $output['rolTotal'] = round($output['sales'] - $output['payments'],2,PHP_ROUND_HALF_DOWN);
        }
    }
    return $output;
}
?>