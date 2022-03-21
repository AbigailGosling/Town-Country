<?php
	include('includes/frontHeader.php');
?>

<style type="text/css">

    .dataTables_length{ display:none; }
    #soaTable_filter{ display:none; }
    

    .mp{
        float: right;
        margin-bottom: 10px;
    }
    
    .search{
        background:#f8f8f8;
        padding:10px;
    }

    .back{
        font-size:18px;
        text-decoration:none;
        color:#888;
        font-weight:bold;
    }

    .table{
        margin-top:10px;
    }

    .table td{
        height:30px;
        font-size:16px;
    }
    
    tr.heading, tr.last{
        font-size:18px;
        background:#e2e2e2;
        height:30px;
    }
    
    tr.even, tr.odd{
        padding:5px !important;
    }

    tr.even{
        background:#f7f7f7;
    }

    .datePicker{
        width: 150px;
        height: 30px;
    }

    .searchbtn{
        height:32px;
    }
</style>
<div id="top">
	<a href="menu.php" id="menu">MENU</a>
	<a href="logout.php" id="logout">LOGOUT</a>
</div>
<div class="search">
    <div class="container flex space-between" style="align-items:center">
        <a href="javascript: window.history.back()" class="back">< BACK</a>
    </div>
</div>
<div class="container">
    <?php
        
        // Get all users that have created picksheets 
        $USER_IDS = array();
        $usersWithPicksResult = mysqli_query($conn, "SELECT user_from_id FROM `pickerSheets` WHERE completed=1 GROUP BY user_from_id");
        
        while($userPicks = mysqli_fetch_array($usersWithPicksResult)){
            if($userPicks['user_from_id'] != ''){
                array_push($USER_IDS, $userPicks['user_from_id']);
            }
        }
        $USER_IDS = implode(',', $USER_IDS);
    ?>
    <form method="GET">
        <h2>Statement of accounts</h2>
        <p> Showing results for: 
        <select name="user_id" style="height:30px;width:150px;">
            <option disabled selected>Select a salesman</option>
            <option value="0" <?php if($_GET['user_id'] == '0'){ echo 'selected'; } ?>>All Salesmen</option>
            <?php
                $usersResult = mysqli_query($conn, "SELECT * FROM users WHERE id IN ($USER_IDS)");
                while($user = mysqli_fetch_array($usersResult)){
            ?>
            <option value="<?php echo $user['id']; ?>" <?php if($_GET['user_id'] == $user['id']){ echo 'selected'; } ?>><?php echo $user['name']; ?></option>
            <?php } ?>
        </select>
        <?php
        if($_GET['date_start'] != ''){
            $uk_date_start = str_replace('/', '-', $_GET['date_start']);
            $uk_date_start = date('d/m/Y', strtotime($uk_date_start));
        }

        if($_GET['date_end'] != ''){
            $uk_date_end = str_replace('/', '-', $_GET['date_end']);
            $uk_date_end = date('d/m/Y', strtotime($uk_date_end));
        }
    ?>
        <b>BETWEEN</b>
        <input class="datepicker" name="date_start" id="date_start" placeholder="START DATE" value="<?php echo $uk_date_start; ?>" style="height:30px;width:100px;">
        <b>AND</b>
        <input class="datepicker" name="date_end" id="date_end" placeholder="END DATE" value="<?php echo $uk_date_end; ?>" style="height:30px;width:100px;">

        <input type="submit" style="height:30px;" value="Search">
        <a href="soa_all.php" style="font-size:12px;text-decoration:none">Reset Form</a>
        </p>
    </form>
    <?php
        $form_user_id = mysqli_real_escape_string($conn, $_GET['user_id']);
        
        // if the form has been submitted
        if($form_user_id != null){

            // Dates were selected
            if($_GET['date_start'] != '' && $_GET['date_end'] != ''){
                $date_start = mysqli_real_escape_string($conn, $_GET['date_start']);
                $date_end = mysqli_real_escape_string($conn, $_GET['date_end']);

                $date_start = str_replace('/', '-', $date_start);
                $date_start = date('Y-m-d', strtotime($date_start));

                $date_end = str_replace('/', '-', $date_end);
                $date_end = date('Y-m-d', strtotime($date_end));


                $dateQueryPiece = " && date_completed >= '$date_start' && date_completed <= '$date_end'";
            }

            // if 'all salesmen' was selected
            if($form_user_id == 0){
                $usersResult = mysqli_query($conn, "SELECT * FROM users WHERE id IN ($USER_IDS)");
            }else{ // a specfic user was selected
                $usersResult = mysqli_query($conn, "SELECT * FROM users WHERE id IN ($form_user_id)");
            }

            // loop though all the users 
            while($user = mysqli_fetch_array($usersResult)){
                $user_id = $user['id'];
                $total_charged_user = 0;
                $total_paid_user = 0;
                $total_sales = 0;
                $myCustomersResult = mysqli_query($conn, "SELECT id FROM `customers` WHERE default_salesman_id='$user_id'");

                $customer_ids = [];
                while($customer = mysqli_fetch_array($myCustomersResult)){ array_push($customer_ids, $customer['id']); }
                $customer_ids = implode(',', $customer_ids);


            ?>
            <table width="100%" border="1" style="margin-bottom:10px;">
            <tr>
                <td style="vertical-align:top;width:150px;">
                    <table width="100%">
                        <tr>
                            <th align="left"><?php echo ucfirst($user['name']); ?></th>
                        </tr>
                    </table>
                </td>
                <td align="left">
                    <table width="100%" border="0">
                        <tr>
                            <th align="left">Customer</th>
                            <th align="right">Total Sales</th>
                            <th align="right">Total Charged</th>
                            <th align="right">Total Received</th>
                        </tr>
                        
                            <?php

                                
                                $picksheetsResult = mysqli_query($conn, "SELECT GROUP_CONCAT(id) as id,customer_id FROM `pickerSheets` WHERE completed=1 && customer_id IN ($customer_ids) $dateQueryPiece GROUP BY customer_id");
                                
                                $i=0;
                                while($picksheet = mysqli_fetch_array($picksheetsResult)){
                                    $i++;
                                    $customer = getCustomer($picksheet['customer_id']);
                                    $num_of_sales = count(explode(",",$picksheet['id']));
                                    $total_charged_picksheet = getChargedPicksheetTotalList(explode(",",$picksheet['id']));
                                    $total_paid_picksheet = getPaidPicksheetTotalList(explode(",",$picksheet['id']));
                                    
                                    $total_sales += $num_of_sales;
                                    $total_charged_user += (float)$total_charged_picksheet;
                                    $total_paid_user += (float)$total_paid_picksheet;

                                ?>
                                <tr class="<?php if($i % 2 == 0){ echo 'even'; }else{ echo 'odd'; } ?>">
                                    <td align="left"><?php echo $customer['businessname']; ?></td>
                                    <td align="right"><?php echo $num_of_sales; ?></td>
                                    <td align="right" width="200">£<?php echo number_format($total_charged_picksheet, 2); ?></td>
                                    <td align="right" width="200">£<?php echo number_format($total_paid_picksheet, 2); ?></td>
                                </tr>
                                <?php
                                }
                            ?>
                            <tr>
                                <td><b>Total:</b></td>
                                <td align="right" width="200"><b><?php echo $total_sales; ?></b></td>
                                <td align="right" width="200"><b>£<?php echo number_format($total_charged_user,2); ?></b></td>
                                <td align="right" width="200"><b>£<?php echo number_format($total_paid_user,2); ?></b></td>
                            </tr>
                    </table>
                    <?php
                        
                    ?>
                </td>
                </tr>
            </table>
            <?php
            }
        }else{
            ?><Br/><h4 style="color:#333;padding:10px;background:#cacaca"><i class="fa fa-info-circle"></i> Select a salesman and click search for data</h4><?php
        }
    ?>
</div>

<div class="clearfix"></div>
<script type="text/javascript"> 

   $(document).ready(function() {
        $( ".datepicker" ).datepicker({
            dateFormat: 'dd/mm/yy'
        });
    });
</script>