<?php
	include('includes/frontHeader.php');
?>
<div id="top">
	<a href="menu.php" id="menu">MENU</a>
	<a href="logout.php" id="logout">LOGOUT</a>
</div>
<div class="search">
    <div class="container flex space-between" style="align-items:center">
        <a href="/manageCustomers.php?id=<?php echo $_GET['id']; ?>" class="back">< BACK</a>
        <div class="daterange">
            <form method="GET">
            <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>">
            From:
            <input type="date" name="date_from" class="datePicker">
            To:
            <input type="date" name="date_to" class="datePicker">

            <input type="submit" value="Search" class="searchbtn">
        </div>
    </div>
</div>
<div class="container">
    <?php
		if($_GET['id'] != ''){
        
        $customer = getCustomer($_GET['id']);
    ?>
    <h2>Statement of account for <?php echo $customer['businessname']; ?>
        <?php
            if($_GET['date_from'] != '' && $_GET['date_to'] != ''){

                $date = str_replace('/', '-', $_GET['date_from']);
                $date_from = date('d/m/Y', strtotime($date));

                $date = str_replace('/', '-', $_GET['date_to']);
                $date_to = date('d/m/Y', strtotime($date));
                
                echo '(' . $date_from . ' - ' . $date_to . ')';
            }
        ?>
    </h2>
    <a class="mp" href="/multi_invoice_payments.php?customer_id=<?php echo $_GET['id']; ?>">Make / View payments</a>
    <table id="soaTable" class="table" width="100%">
        <thead>
            <tr class="heading">
                <th align="left">Invoice ID</th>
                <th align="left">Add Payment</th>
                <th align="left">Due Date</th>
                <th align="left">Date</th>
                <th align="right">Value</th>
                <th align="right">Paid</th>
                <th align="right">Credit</th>
                <th align="right">Outstanding</th>
            </tr>
        </thead>
        <tbody>
	<?php   
            $customer_id = $customer['id'];

            if($_GET['date_from'] != '' && $_GET['date_to'] != ''){

                $date_from = $_GET['date_from'];
                $date_to = $_GET['date_to'];
                
                $customerPicksheets = mysqli_query($conn, "SELECT pickerSheets.*, SUM(invoice_payments.amount) as paid FROM `pickerSheets` left join invoice_payments on invoice_payments.payment_method != 'CREDIT_NOTE' && pickerSheets.id = invoice_payments.invoice_id WHERE (pickerSheets.completed = 1 AND pickerSheets.customer_id=$customer_id AND pickerSheets.date BETWEEN '$date_from' AND '$date_to') GROUP by pickerSheets.id");
            }else{
                $customerPicksheets = mysqli_query($conn, "SELECT pickerSheets.*, SUM(invoice_payments.amount) as paid FROM `pickerSheets` left join invoice_payments on invoice_payments.payment_method != 'CREDIT_NOTE' && pickerSheets.id = invoice_payments.invoice_id WHERE (pickerSheets.completed = 1 AND pickerSheets.customer_id=$customer_id) GROUP by pickerSheets.id");
            }
            
            $totalPrice = 0.00;
            $totalPaid = 0.00;
            $totalCredited = 0.00;
            $totalOutstanding = 0.00;

            $i = 0;
			while($picksheet = mysqli_fetch_array($customerPicksheets)){
                $total_credit = totalValueCreditedOnInvoiceID($picksheet['id']);
                $this_price = (float) invoiceTotal($picksheet['id']);
                $totalPrice += $this_price;

                $date = str_replace('/', '-', $picksheet['date']);
                $date = date('d/m/Y', strtotime($date));

                $totalPaid += (float) $picksheet['paid'];
                $invoicePaid = false;
                $epsilon = 0.00001;
                if(($this_price - $picksheet['paid']) <= $epsilon){
                    $invoicePaid = true;
                    $currentOutstanding = (float) 0;
                }else{
                    $currentOutstanding = (float) $this_price - $picksheet['paid'] - $total_credit;
                }
                
                $totalOutstanding += $currentOutstanding;

			?>
			<tr class="<?php  if($i%2 == 0){ echo 'odd'; }else{ echo 'even'; } ?>">
				<td><a href="/invoice.php?id=<?php echo $picksheet['id']; ?>"><?php echo $picksheet['id']; ?></a>
                    <?php
                        $hasReturns = doesInvoiceHaveReturns($picksheet['id']);
                        $hasCreditNote = doesInvoiceHaveCreditNote($picksheet['id']);
                        
                        if(!$hasCreditNote){
                            if($hasReturns){
                                ?><div class="soa_cr_label">CR</div><?php
                            }
                        }
                    ?> 
                </td>
                <?php if(!$invoicePaid) { ?>
                    <td><a href="/single_invoice_payments.php?customer_id=<?php echo $_GET['id']; ?>&invoice_id=<?php echo $picksheet['id']; ?>">Make / View payments</a></td>
				<?php }else{ ?>
                    <td><a href="/single_invoice_payments.php?customer_id=<?php echo $_GET['id']; ?>&invoice_id=<?php echo $picksheet['id']; ?>">Invoice Paid</a></td>
                <?php }?>

                <?php
                    $estimated_delivery_date = strtotime($picksheet['estimated_delivery_date']);
                    $sortableDueDateFormat = date('d-m-Y',$estimated_delivery_date);
                ?>
                <td data-sort="<?php echo $sortableDueDateFormat; ?>">
                    <?php
                        echo $picksheet['estimated_delivery_date'];

                        if (strtotime($picksheet['estimated_delivery_date']) < time()) {
                            ?><div class="overdue" style="display:inline-block;background:red;border-radius:20px;height:20px;width:20px;color:#fff;text-align:center;font-weight:bold;line-height:20px;">!</div><?php
                        }
                    ?>
                 </td>

                
                 <?php
                    $sortableDateFormat = date('d-m-Y',$date);

                    $totalCredited += totalValueCreditedOnInvoiceID($picksheet['id']);
                ?>
                <td data-sort="<?php echo $sortableDateFormat; ?>" width="100"><?php echo $date; ?></td>
                <td align="right" width="100"><?php if($this_price != 0) { echo '£' . number_format($this_price,2,".",","); } ?></td>
                <td align="right" width="100"><?php if($picksheet['paid'] != 0){ echo '£' . number_format($picksheet['paid'], 2, ".", ","); } ?></td>
                <td align="right" style="color:red;"><?php if(totalValueCreditedOnInvoiceID($picksheet['id'])){ echo '£' . number_format(totalValueCreditedOnInvoiceID($picksheet['id']), 2, ".", ","); }?></td>
                <td align="right" width="100"><?php if($currentOutstanding){ echo '£' . number_format($currentOutstanding, 2, ".", ","); } ?></td>
			</tr>
			<?php
                $i++;
			}
	        ?>
            </tbody>
	</table>
    <table class="table" width="100%">
        <tr class="last">
            <td align="right">Total:</td> 
            <td align="right" width="120">£<?php echo number_format($totalPrice, 2, ".", ","); ?></td> 
            <td align="right" width="120">£<?php echo number_format($totalPaid, 2, ".", ","); ?></td> 
            <td align="right" width="120" style="color:red;">£<?php echo number_format($totalCredited, 2, ".", ","); ?></td>
            <td align="right" width="120">£<?php echo number_format($totalOutstanding, 2, ".", ","); ?></td>
        </tr>
    </table>
    <?php
    }
    ?>
</div>

<div class="clearfix"></div>
<script type="text/javascript"> 

    $(document).ready( function () {
        $('#soaTable').DataTable( {
            "pageLength": 30
        });
    });

</script>

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