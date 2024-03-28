<?php
include_once(__DIR__.'/../functions.php');
$sql = "SELECT `pickerSheets`.*,`customers`.`businessname`,`customers`.`id` as `customer_id`,`customers`.`sage_no` FROM `pickerSheets` INNER JOIN `customers` ON `pickerSheets`.`customer_id` = `customers`.`id` WHERE `pickerSheets`.`admin_approved` = 0 AND ";
if (request()->input('start') !== null && request()->input('start')!="" && request()->input('end') !== null && request()->input('end')!="") 
{
    $start = request()->input('start');
    $end =  request()->input('end');
    $sql .= "(STR_TO_DATE(`estimated_delivery_date`, '%d/%c/%Y') BETWEEN ? AND ?) ORDER BY `pickerSheets`.id ASC";
}
else if (request()->input('startInv') !== null && request()->input('startInv') !="" && request()->input('end') !== null && request()->input('end')!="") 
{
    $start = request()->input('startInv');
    $end =  request()->input('end');
    $sql .= "(`pickerSheets`.`id` >= ? AND STR_TO_DATE(`estimated_delivery_date`, '%d/%c/%Y')` <= ?) ORDER BY `pickerSheets`.id ASC";
}
else if (request()->input('startInv') !== null && request()->input('startInv')!="" && request()->input('endInv')!==null && request()->input('endInv')!="") 
{
    $start = request()->input('startInv');
    $end =  request()->input('endInv');
    $sql .= "(`pickerSheets`.`id` >= ? AND `pickerSheets`.`id` <= ?) ORDER BY `pickerSheets`.id ASC";
}
else if (request()->input('start') !== null && request()->input('start')!="" && request()->input('endInv')!==null && request()->input('endInv')!="") 
{
    $start = request()->input('start');
    $end =  request()->input('endInv');
    $sql .= "(STR_TO_DATE(`estimated_delivery_date`, '%d/%c/%Y') > ? AND `pickerSheets`.`id` <= ?) ORDER BY `pickerSheets`.id ASC";
}
$res = prepareExecuteQuery($sql,'ss',[$start,$end]);
$list = mysqli_fetch_all($res, MYSQLI_ASSOC);
$output = "";
$tracker=array();
foreach ($list as $item)
{
    $id = $item['id'];
    $customername = $item['businessname'];
    $completedDate = DateTime::createFromFormat("Y-m-d H:i:s",$item['date_completed'])->format("d/m/Y");
    $delDate = $item['estimated_delivery_date'];
    $custID = $item['customer_id'];
    $sageNo = $item['sage_no'];
    $value = number_format((double)invoiceTotal($item['id']), 2, '.', '');
    $href = '';
    $status = '';
    $rowStyle = '';
    $showHide = ' display: none;';
    if ($item['admin_approved'] == 1) $showHide = '';
    if ($item['completed'] == 1)
    {
        $href = 'invoice.php?id='.$id.'&or=1';
        $status = "Completed";
        $tracker[]= $id;
        $tracker[]= 'i';
    }
    else
    {
        $href = 'viewSalesconfirmation.php?id='.$id.'&or=1';
        $tracker[]= $id;
        $tracker[]= 's';
        if ($item['deleted'] == 1)
        {
            $status = "VOID";
            $rowStyle = ' style="background-color: #ff5456;"';
        }
        else
        {
            $status = "In Pick";
            $rowStyle = ' style="background-color: #bebebe;"';
        }
    }
    $output .= "<tr$rowStyle><td align='center'><a href='$href' target='_blank'>$id</a></td><td align='left'>$status</td><td align='left'>$customername</td><td align='center'>$custID</td><td align='center'>$sageNo</td><td align='right'>$completedDate</td><td align='center'>$delDate</td><td align='right'>£$value</td><td align='center' style='font-size: 18px;'><div onclick='ticked($id)' style='margin: 0 10px 0 10px; height: 32px; width: 32px; border: 1px solid black;'><i id='img-mail-selector-$id' class='fa fa-check img-mail-selector' style='height:100%; margin-top: 5px;$showHide'></i></div></td></tr>";
}
$tracker[]=$output;
echo json_encode($tracker);
?>