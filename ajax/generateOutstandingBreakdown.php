["
<?php
    include_once('../functions.php');
    require_once('customer_soa_results_function.php');

    $where = array("`customers`.`disabled` = 0");
    if (isset($_POST['customer_id']) && $_POST['customer_id'] != "") $where[] = "`customers`.`id` = ".$_POST['customer_id'];
    if (isset($_POST['user_id']) && $_POST['user_id'] != "") $where[] = "`users`.`id` = ".$_POST['user_id'];
    $masterQ = mysqli_query($conn,"SELECT `customers`.*,`users`.`name` as `username` FROM `customers` INNER JOIN `users` ON `users`.`id` = `customers`.`default_salesman_id` WHERE ".implode(" AND ",$where));
    $overallBeyondGrace = 0;
    $overallBeyondDate = 0;
    $overallCloseDate = 0;
    $overallCurrent = 0;
    while ($customer = mysqli_fetch_assoc($masterQ))
    {
        $rollingBeyondGrace = 0;
        $rollingBeyondDate = 0;
        $rollingCloseDate = 0;
        $rollingCurrent = 0;
        $data = get_customer_soa_results($customer['id'],true);
        $gracePeriod =  strtotime("-".$customer['credit_grace']." days");
        $beyondDate = strtotime("-".$customer['credit_terms']." days");
        $closeToOverdue = strtotime("-".$customer['due_warning']." days");
        foreach ($data as $row)
        {
            $date = DateTime::createFromFormat('d/m/Y', $row['date']);
            $date = $date->getTimestamp();
            if ($date > $gracePeriod)
            {
                $rollingBeyondGrace += $row['outstanding'];
            }
            else if ($date > $beyondDate)
            {
                $rollingBeyondDate += $row['outstanding'];
            }
            else if ($date > $closeToOverdue)
            {
                $rollingCloseDate += $row['outstanding'];
            }
            else
            {
                $rollingCurrent += $row['outstanding'];
            }
        }
        $overallBeyondGrace += $rollingBeyondGrace;
        $overallBeyondDate += $rollingBeyondDate;
        $overallCloseDate += $rollingCloseDate;
        $overallCurrent += $rollingCurrent;
        $total = $rollingBeyondGrace + $rollingBeyondDate + $rollingCloseDate + $rollingCurrent;
        if ($total == 0 && $rollingBeyondGrace  == 0 && $rollingBeyondDate  == 0 && $rollingCloseDate  == 0 && $rollingCurrent == 0) continue; 
?>
    <tr>
        <td><?php echo $customer['username'];?></td>
        <td><?php echo $customer['businessname'];?></td>
        <td><?php echo $customer['id'];?></td>
        <td><?php if ($rollingCurrent != 0)echo "£" . number_format($rollingCurrent,2);?></td>
        <td><?php if ($rollingCloseDate != 0) echo "£" . number_format($rollingCloseDate,2);?></td>
        <td><?php if ($rollingBeyondDate != 0) echo "£" . number_format($rollingBeyondDate,2);?></td>
        <td><?php if ($rollingBeyondGrace != 0) echo "£" . number_format($rollingBeyondGrace,2);?></td>
        <td><?php echo "£" . number_format($total,2);?></td>
    </tr>
<?php
    }
    $total = $overallBeyondGrace + $overallBeyondDate + $overallCloseDate + $overallCurrent;
?>
"|"
    <tr class="last" style="position: sticky; bottom: 0;">
        <td><?php echo "Totals:";?></td>
        <td></td>
        <td></td>
        <td><?php if ($overallCurrent != 0) echo "£" . number_format($overallCurrent,2);?></td>
        <td><?php if ($overallCloseDate != 0) echo "£" . number_format($overallCloseDate,2);?></td>
        <td><?php if ($overallBeyondDate != 0) echo "£" . number_format($overallBeyondDate,2);?></td>
        <td><?php if ($overallBeyondGrace != 0) echo "£" . number_format($overallBeyondGrace,2);?></td>
        <td><?php echo "£" . number_format($total,2);?></td>
    </tr>
    "]