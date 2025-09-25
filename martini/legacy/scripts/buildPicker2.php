<?php

use Illuminate\Support\Facades\Log;

include(__DIR__.'/../functions.php');
$picker_id = request()->input('picker_id', $_SESSION['USER']);
$customer_id = request()->input('customer_id');
$estimated_delivery_date = request()->input('date');

$orderReferenceNumber = request('orderReferenceNumber');
$weightnote = request()->input('weightnote','');
$picksheet_note = request()->input('picksheet_note');

//$user_from_id = $_SESSION['USER'];
$user_from_id = request()->input('user');
$addressid = request()->input('addressid',1);
$transaction_id = request()->input('transaction_id');
$isCredit = (request()->input('sup_type') == "credit");

if ($transaction_id != null && $transaction_id != "")
{
    $transactCheck = prepareExecuteQuery("SELECT * FROM `pickerSheets` WHERE transaction_id = ?",'s',[$transaction_id]);
    if ($transactCheck->num_rows > 0) {
        throw new \Exception("duplicate transaction");
        abort(500);
        die();
    }
}
$x = "INSERT INTO `pickerSheets` (picker_id,user_from_id,customer_id,estimated_delivery_date,orderReferenceNumber,date_completed,addressid,picksheet_note,transaction_id,isSupplemental,isSupplementalCredit) VALUES (?,?,?,?,?,?,?,?,?,1,".(($isCredit)?"1":"0").")";
$y = prepareExecuteQuery($x,'iiissssss',[$picker_id,$user_from_id,$customer_id,$estimated_delivery_date,$orderReferenceNumber,$today,$addressid,$picksheet_note,$transaction_id],true);

$pickersheet_id = $y;
if ((int)$pickersheet_id !== $pickersheet_id)
{
    abort(500);
    die();
}
loggedDataChange("picksheet_note",$picksheetid,$picksheet_note);
loggedDataChange("picksheet_orderReferenceNumber",$picksheetid,$orderReferenceNumber);
$items = request()->input('items');
$weights = [];
$totalCost = 0;
foreach ($items as $item){
    if ($item == null) continue;
    $name = $item['name'];
    $cost = $item['cost'];
    $weight=$item['weight'];
    $amount=$item['amount'];
    if ($name == null || $name == "" || $cost == null || $cost == "")continue;
    $totalCost = $totalCost + (floorDec($weight,3)*floorDec($cost,3)*floorDec($amount,3));

    //Cut Row
    $cutid = prepareExecuteQuery("INSERT INTO `tandc_live`.`cuts` ( `species_id`, `name`, `cutgroup_id`) VALUES (13, ?, -1)",'s',[$name],true);
    //Product Row
    $product_id = prepareExecuteQuery("INSERT INTO `tandc_live`.`product` (`pallet_id`, `cut_id`, `brand_id`, `nationality_id`, `cooling_id`, `status`, `range_from`, `range_to`, `ubbb`, `unit`, `comments`, `best_by`, `pricetype`, `cost`, `price`, `box_id`, `weightnote`, `product_temp`, `original_intake_id`, `original_pallet_id`, `note_units`, `note_weight`, `akg`, `quantity`)
                                                  VALUES (-1, $cutid, -1, -1, -1, 1, NULL, NULL, NULL, 'C', NULL, NULL, NULL, '0','0', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, ?)",'i',[$amount],true);
    //Weight Row
    for ($i=0;$i<$amount;$i++){
        $weight_id = $weights[] = prepareExecuteQuery("INSERT INTO `tandc_live`.`weights` (`product_id`, `status_id`, `weight_gross`, `weight_tear`, `pallet_tare`, `tare_per_carton`, `number_of_cartons`, `original_gross`, `tampered`, `grosstare`) VALUES ($product_id, 1, $weight, $weight, 1, 1, 1, 1, '0', '0')",'',[],true);

        $x = "INSERT into `pickerItems` (pickersheet_id,product_id,price,price_type,comment,target_weight) VALUES (?,?,?,?,?,?)";
        if (!$isCredit) $y = prepareExecuteQuery($x,'iissss',[$pickersheet_id,$product_id,$cost,0,'',$weight]);
        else $y = prepareExecuteQuery($x,'iissss',[$pickersheet_id,$product_id,0,0,'',$weight]);
    }
}
if (count($weights) == 0) die("N/A");
$weightString = implode(',', $weights);
$x = "INSERT INTO `palletsOut` (pickersheet_id,weight_ids,stringName,picker_ids) VALUES (?,?,'#',?)";
$y = prepareExecuteQuery($x,'iss',[$pickersheet_id,$weightString,$userid]);

$x = "UPDATE `weights` SET status_id = '1' WHERE id IN ($weightString) LIMIT 1";
$y = prepareExecuteQuery($x);

date_default_timezone_set("Europe/London");

$date_completed = date('Y/m/d H:i:s');

$session_USERID = $_SESSION['USER'];

$x = "UPDATE `pickerSheets` SET completed = '1',completed_frozen='1',completed_fresh='1', completedby_userid=?, date_completed = ? WHERE id = ?";
$y = prepareExecuteQuery($x,'ssi',[$session_USERID,$date_completed,$pickersheet_id]);

$val = getPicksheetValue($pickersheet_id);

$x1 = "SELECT * FROM `customers` WHERE id=?";
$y1 = prepareExecuteQuery($x1,'i',[$customer_id]);
$customer = mysqli_fetch_array($y1);

$current_outstanding = (float) $customer['current_outstanding'];
$newVal = $current_outstanding + (float) $val;

$x = "UPDATE `customers` SET current_outstanding = ?,`override` = 0, `delivery_day_override` = 0 WHERE id = ? LIMIT 1";
$y = prepareExecuteQuery($x,'si',[$newVal,$customer_id]);
# END update customer price

if ($isCredit)
{
    $x = "INSERT INTO `invoice_payments` (`invoice_id`,`payment_method`,`amount`,`payment_recorded_by`) VALUES (?,?,?,?)";
    $y = prepareExecuteQuery($x,'issi',[$pickersheet_id,'CREDIT_NOTE',$totalCost,$userid],true);

    $x = "INSERT INTO `credit_note_items` (`payment_id`,`product_id`,`quantity`,`price`) VALUES (?,?,?,?)";
    $y = prepareExecuteQuery($x,'iiis',[$y,$product_id,1,$totalCost],true);
}

echo $pickersheet_id;
?>
