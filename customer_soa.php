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
    <table class="table" width="100%">
        <tr class="heading">
            <th align="left">Invoice ID</th>
            <th align="left">Date</th>
            <th align="left">PDF</th>
            <th align="right">Price</th>

        </tr>
	<?php   
            $customer_id = $customer['id'];

            if($_GET['date_from'] != '' && $_GET['date_to'] != ''){

                $date_from = $_GET['date_from'];
                $date_to = $_GET['date_to'];

                $customerPicksheets = mysqli_query($conn, "SELECT * FROM `pickerSheets` WHERE completed = 1 && customer_id=$customer_id && date BETWEEN '$date_from' AND '$date_to'");
            }else{
                $customerPicksheets = mysqli_query($conn, "SELECT * FROM `pickerSheets` WHERE completed = 1 && customer_id=$customer_id");
            }

            $totalPrice = 0.00;
            
            $i = 0;
			while($picksheet = mysqli_fetch_array($customerPicksheets)){
                $this_price = invoiceTotal($picksheet['id']);
                $totalPrice += (float) $this_price;

                $date = str_replace('/', '-', $picksheet['date']);
                $date = date('d/m/Y', strtotime($date));

			?>
			<tr class="<?php  if($i%2 == 0){ echo 'odd'; }else{ echo 'even'; } ?>">
				<td><a href="/invoice.php?id=<?php echo $picksheet['id']; ?>"><?php echo $picksheet['id']; ?></a></td>
				<td><?php echo $date; ?></td>
                <td><a href="javascript:;" onclick="generatePDF(<?php echo $picksheet['id']; ?>)">Invoice_<?php echo $picksheet['id']; ?>.pdf</a></td>
                <td align="right">£<?php echo number_format($this_price,2,".",","); ?></td>
			</tr>
			<?php
                $i++;
			}
	        ?>
            <tr class="last">
                <td align="right" colspan="4">Total: £<?php echo number_format($totalPrice,2,".",","); ?></td> 
            </tr>
	</table>
    <?php
    }
    ?>
</div>

<div class="clearfix"></div>
<script type="text/javascript"> 
</script>

<style type="text/css">
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

<script>
    function generatePDF(id){
		$.get("<?php echo $domain; ?>ajax/generatePDFInvoice.php?id=" + id, function(data, status){
			
			var name = data.replace(/\s+/g, '');
			
			downloadURI('<?php echo $domain; ?>PDF/' + name, name);
			
		});
	}
	
	function downloadURI(uri, name) 
	{
		var link = document.createElement("a");
		link.download = name;
		link.href = uri;
		link.click();
	}
</script>