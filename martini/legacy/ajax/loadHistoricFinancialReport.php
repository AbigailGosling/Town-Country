<?php
require_once(__DIR__.'/../functions.php');
$sql = "SELECT `created`,`start_date`,`end_date`,`start_invoice_id`,`end_invoice_id`,`sales`,`payments`,`id` FROM `finance_report_history` WHERE `user_id` = ".$userid;
$res = mysqli_query($conn,$sql);
$rows= mysqli_fetch_all($res,MYSQLI_ASSOC);

foreach ($rows as $sample) {
    echo '<tr name="hist_'.$sample['id'].'" id="hist_'.$sample['id'].'" onclick="selectHist('.$sample['id'].')">';
    foreach ($sample as $key => $value) {
        if ($key != "id") 
        {
            if ($key == "sales" || $key == "payments")
            {
                echo '<td id="hist_'.$sample['id'].'_'.$key.'">£'.$value.'</td>';
            }
            else
            {
                if (strpos($key,'date') != false || $key == "created")
                {
                    echo '<td id="hist_'.$sample['id'].'_'.$key.'">'.date('d/m/Y H:i:s', strtotime($value)).'</td>';
                    
                }
                else
                {
                    echo '<td id="hist_'.$sample['id'].'_'.$key.'">'.$value.'</td>';
                }
            }
        }
    }
    echo '<td><button style="width: 55px;" onclick="deleteHist('.$sample['id'].')">Delete</td></tr>';
}
?>