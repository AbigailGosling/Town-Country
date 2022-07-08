<?php
include_once('../functions.php');
$sql = "SELECT `pickerSheets`.*,`customers`.`businessname` FROM `pickerSheets` INNER JOIN `customers` ON `pickerSheets`.`customer_id` = `customers`.`id` WHERE `pickerSheets`.`admin_approved` = 0 AND ";
if (isset($_POST['start']) && $_POST['start']!="" && isset($_POST['end']) && $_POST['end']!="") 
{
    $start = $_POST['start'];
    $end =  $_POST['end'];
    $sql .= "(`pickerSheets`.`date_completed` BETWEEN '".$start."' AND '".$end."') ORDER BY `pickerSheets`.id ASC";
}
else if (isset($_POST['startInv']) && $_POST['startInv']!="" && isset($_POST['end']) && $_POST['end']!="") 
{
    $start = $_POST['startInv'];
    $end =  $_POST['end'];
    $sql .= "(`pickerSheets`.`id` >= ".$start." AND `pickerSheets`.`date_completed` <= '".$end."') ORDER BY `pickerSheets`.id ASC";
}
else if (isset($_POST['startInv']) && $_POST['startInv']!="" && isset($_POST['endInv']) && $_POST['endInv']!="") 
{
    $start = $_POST['startInv'];
    $end =  $_POST['endInv'];
    $sql .= "(`pickerSheets`.`id` >= ".$start." AND `pickerSheets`.`id` <= '".$end."') ORDER BY `pickerSheets`.id ASC";
}
else if (isset($_POST['start']) && $_POST['start']!="" && isset($_POST['endInv']) && $_POST['endInv']!="") 
{
    $start = $_POST['start'];
    $end =  $_POST['endInv'];
    $sql .= "(`pickerSheets`.`date_completed` > ".$start." AND `pickerSheets`.`id` <= '".$end."') ORDER BY `pickerSheets`.id ASC";
}
$res = mysqli_query($conn, $sql) or die(mysqli_error($conn)." ". $sql);
$list = mysqli_fetch_all($res, MYSQLI_ASSOC);

foreach ($list as $item)
{
    $id = $item['id'];
    $customername = $item['businessname'];
    $assembledate = date('d/m/Y H:i:s', strtotime($item['date_completed']));
    $value = number_format((float)invoiceTotal($item['id']), 2, '.', '');
    $href = '';
    $status = '';
    $rowStyle = '';
    $showHide = ' display: none;';
    if ($item['admin_approved'] == 1) $showHide = '';
    if ($item['completed'] == 1)
    {
        $href = 'invoice.php?id='.$id;
        $status = "Completed";
    }
    else
    {
        $href = 'viewSalesconfirmation.php?id='.$id;
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
    echo "<tr$rowStyle><td align='center'><a href='$href' target='_blank'>$id</a></td><td align='left'>$status</td><td align='left'>$customername</td><td align='right'>$assembledate</td><td align='right'>£$value</td><td align='center' style='font-size: 18px;'><div onclick='ticked($id)' style='margin: 0 10px 0 10px; height: 32px; width: 32px; border: 1px solid black;'><i id='img-mail-selector-$id' class='fa fa-check img-mail-selector' style='height:100%; margin-top: 5px;$showHide'></i></div></td></tr>";
}
?>