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

        <input type="submit" style="height:30px;" value="Search">
        <a href="soa_all.php" style="font-size:12px;text-decoration:none">Reset Form</a>
        </p>
    </form>
    <?php
        $form_user_id = mysqli_real_escape_string($conn, $_GET['user_id']);
        
        // if the form has been submitted
        if($form_user_id != null){

            // if 'all salesmen' was selected
            if($form_user_id == 0){
                $usersResult = mysqli_query($conn, "SELECT * FROM users WHERE id IN ($USER_IDS)");
            }else{ // a specfic user was selected
                $usersResult = mysqli_query($conn, "SELECT * FROM users WHERE id IN ($form_user_id)");
            }

            // loop though all the users 
            while($user = mysqli_fetch_array($usersResult)){
                $user_id = $user['id'];
                $total_outstanding_user = 0;
                $total_paid_user = 0;
            ?>
            <table width="100%" border="1" style="margin-bottom:10px;">
            <tr>
                <td style="vertical-align:top;">
                    <table width="100%">
                        <tr>
                            <th align="left"><?php echo ucfirst($user['name']); ?></th>
                        </tr>
                    </table>
                </td>
                <td align="left">
                    <table width="100%">
                        <tr>
                            <th align="left">Customer</th>
                            <th align="right">Total Sales</th>
                            <th align="right">Total Outstanding</th>
                            <th align="right">Total Received</th>
                        </tr>
                        
                            <?php
                                $picksheetsResult = mysqli_query($conn, "SELECT * FROM `pickerSheets` WHERE completed=1 && user_from_id='$user_id' GROUP BY customer_id");
                                
                                $i=0;
                                while($picksheet = mysqli_fetch_array($picksheetsResult)){
                                    $i++;
                                    $customer = getCustomer($picksheet['customer_id']);
                                    $num_of_sales = countCustomerSalesBySalesman($picksheet['customer_id'], $user_id);
                                    
                                    $total_outstanding_picksheet = getOutstandingPicksheetTotal($picksheet['id']);
                                    $total_paid_picksheet = getTotalPaidByCustomerIDForUserID($picksheet['customer_id'], $user['id']);

                                    $total_outstanding_user += $total_outstanding_picksheet;
                                    $total_paid_user += $total_paid_picksheet;

                                ?>
                                <tr class="<?php if($i % 2 == 0){ echo 'even'; }else{ echo 'odd'; } ?>">
                                    <td align="left"><?php echo $customer['businessname']; ?></td>
                                    <td align="right"><?php echo $num_of_sales; ?></td>
                                    <td align="right" width="200">£<?php echo number_format($total_outstanding_picksheet); ?></td>
                                    <td align="right" width="200">£<?php echo number_format($total_paid_picksheet, 2); ?></td>
                                </tr>
                                <?php
                                }
                            ?>
                        
                    </table>
                    <?php
                        
                    ?>
                </td>
                </tr>
                <tr>
                <td></td>
                <td colspan="1" align="right">
                    <table width="100%" border="0">
                        <tr>
                            <td><b>Total:</b></td>
                            <td></td>
                            <td align="left" width="200"><b>£<?php echo number_format($total_outstanding_user); ?></b></td>
                            <td align="left" width="200"><b>£<?php echo number_format($total_paid_user); ?></b></td>
                        </tr>
                    </table>
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

   
</script>