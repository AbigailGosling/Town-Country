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
        <a href="/manageCustomers.php?id=<?php echo $_GET['id']; ?>" class="back">< BACK</a>
    </div>
</div>
<div class="container">
    
    <h2>Statement of accounts: All Customers</h2>
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


        // loop though all the users 
        $usersResult = mysqli_query($conn, "SELECT * FROM users WHERE id IN ($USER_IDS)");
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
                        <th align="left">Total Sales</th>
                        <th align="left">Total Outstanding</th>
                        <th align="left">Total Received</th>
                     </tr>
                    
                        <?php
                            $picksheetsResult = mysqli_query($conn, "SELECT * FROM `pickerSheets` WHERE completed=1 && user_from_id='$user_id' GROUP BY customer_id");
                            
                            $i=0;
                            while($picksheet = mysqli_fetch_array($picksheetsResult)){
                                $i++;
                                $customer = getCustomer($picksheet['customer_id']);
                                $num_of_sales = countCustomerSalesBySalesman($picksheet['customer_id'], $user_id);
                                
                                $total_outstanding_picksheet = getOutstandingPicksheetTotal($picksheet['id']);
                                $total_paid_picksheet = getPicksheetTotalPaid($picksheet['id']);

                                $total_outstanding_user += $total_outstanding_picksheet;
                                $total_paid_user += $total_paid_picksheet;

                            ?>
                            <tr class="<?php if($i % 2 == 0){ echo 'even'; }else{ echo 'odd'; } ?>">
                                <td align="left"><?php echo $customer['businessname']; ?></td>
                                <td align="left"><?php echo $num_of_sales; ?></td>
                                <td align="left" width="200">£<?php echo number_format($total_outstanding_picksheet); ?></td>
                                <td align="left" width="200">£<?php echo $total_paid_picksheet; ?></td>
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
                        <td></td>
                        <td></td>
                        <td align="left" width="200">£<?php echo number_format($total_outstanding_user); ?></td>
                        <td align="left" width="200">£<?php echo number_format($total_paid_user); ?></td>
                    </tr>
                </table>
            </td>
        </tr>
        </table>
        <?php
        }
    ?>
</div>

<div class="clearfix"></div>
<script type="text/javascript"> 

   
</script>