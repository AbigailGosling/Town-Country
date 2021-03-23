<?php
	include('includes/frontHeader.php');


    if($_POST['user_id'] != ''){
        $USER_ID = mysqli_real_escape_string($conn, $_POST['user_id']);

        $date_start = mysqli_real_escape_string($conn, $_POST['date_start']);
        $date_start = str_replace('/', '-', $date_start);
        $date_start = date('Y-m-d', strtotime($date_start));
        
        
        $date_end = mysqli_real_escape_string($conn, $_POST['date_end']);
        $date_end = str_replace('/', '-', $date_end);
        $date_end = date('Y-m-d', strtotime($date_end));

        if($_POST['date_start'] != ''){
            $dateQuery = " && `date` >= '$date_start' && `date` <= '$date_end'";
        }

        if($USER_ID == 0){
        
            // all
            $searchQueryString = "SELECT * FROM `pickerSheets` WHERE completed=1 $dateQuery";
        }else{
            // specific user
            $searchQueryString = "SELECT * FROM `pickerSheets` WHERE completed=1 && user_from_id='$USER_ID' $dateQuery";
        }
    }
?>
<div id="top">
	<a href="menu.php" id="menu">MENU</a>
	<a href="logout.php" id="logout">LOGOUT</a>
</div>

<style type="text/css">
    
    .result{
        width:100%;
        background:#f2f2f2;
        margin:5px;
        height:50px;
    }
 
	.leftPanel{
		height:100%;
		padding:30px;
		border:1px solid #f4f4f4;
		position:relative;
	}
	
	.clearfix{
		clear:both;
	}
	
	.resultsContainer{
		min-height: 400px;
		border: 2px dashed #cacaca;
		padding: 0px;
		margin-top: 20px;
		padding-top: 14px;
	}
   
</style>
<div class="leftPanel" style="position:relative;">
    <h2>Turnover VS Profit Reports</h2>
    <form method="POST">
	<select name="user_id" style="width:322px;height:40px;">
        <option value="" disabled selected>Select salesman..</option>
        <option value="0">All sales team</option>
		<?php
			$x = "SELECT * FROM `users`";
			$y = mysqli_query($conn, $x);
			
			while($row = mysqli_fetch_array($y)){
			?><option value="<?php echo $row['id']; ?>" <?php if($_POST['user_id'] == $row['id']){ echo 'selected'; } ?>><?php echo $row['name']; ?></option><?php
			}
		?>
	</select>
    <?php
        if($date_start != ''){
            $uk_date_start = str_replace('/', '-', $date_start);
            $uk_date_start = date('d/m/Y', strtotime($uk_date_start));
        }

        if($date_end != ''){
            $uk_date_end = str_replace('/', '-', $date_end);
            $uk_date_end = date('d/m/Y', strtotime($uk_date_end));
        }
    ?>
    <input type="submit" value="Search" style="height: 39px;width: 80px;">
    <b>BETWEEN</b>
    <input class="datepicker" name="date_start" placeholder="START DATE" value="<?php echo $uk_date_start; ?>" style="height:34px;width:100px;">
    <b>AND</b>
    <input class="datepicker" name="date_end" placeholder="END DATE" value="<?php echo $uk_date_end; ?>" style="height:34px;width:100px;">
    </form>
 	
	<div id="loadResults" class="resultsContainer">
        <table style="width:100%;">
            <tr>
                <th align="left">INVOICE ID</th>
                <th align="left">Customer</th>
                <th align="left">Total Invoice Cost</th>
                <th align="left">Total Invoice Amount</th>
                <th align="left">Cost / Price Difference</th>
            </tr>
        <?php
            $searchResults = mysqli_query($conn, $searchQueryString);

            while($invoice = mysqli_fetch_array($searchResults)){
                $invoice_cost = invoiceTotalCost($invoice['id']);
                $invoice_price = invoiceTotal($invoice['id']);
                ?>
                <tr class="result">
                    <td><a href="invoice.php?id=<?php echo $invoice['id']; ?>" target="_blank"><?php echo $invoice['id']; ?></a></td>
                    <td><?php echo customerName($invoice['customer_id']); ?> </td>
                    <td>£<?php echo $invoice_cost; ?></td>
                    <td>£<?php echo $invoice_price; ?></td>
                    <td>£<?php echo $invoice_price - $invoice_cost; ?></td>
                </tr>
                <?php
            }
        ?>
        </table>
    </div>
</div>


<div class="clearfix"></div>
 
<script type="text/javascript">

    $(document).ready(function() {
        $( ".datepicker" ).datepicker({
            dateFormat: 'dd/mm/yy'
        });
		
    });


</script>
 
  
</script>