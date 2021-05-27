<?php
	include('includes/frontHeader.php');


    if($_POST['user_id'] != '' || $_POST['customer_id'] != '' || $_POST['species_id'] != '' || $_POST['intake_id'] != '' || $_POST['pallet_id'] != ''){
        $INTAKE_ID = mysqli_real_escape_string($conn, $_POST['intake_id']);
        $PALLET_ID = mysqli_real_escape_string($conn, $_POST['pallet_id']);
        $USER_ID = mysqli_real_escape_string($conn, $_POST['user_id']);
        $CUSTOMER_ID = mysqli_real_escape_string($conn, $_POST['customer_id']);
        $SPECIES_ID = mysqli_real_escape_string($conn, $_POST['species_id']);

        if($_POST['date_start'] != ''){
            $date_start = mysqli_real_escape_string($conn, $_POST['date_start']);
            $date_start = str_replace('/', '-', $date_start);
            $date_start = date('Y-m-d', strtotime($date_start));
            
            if($_POST['date_end'] == ''){
                $date_end = date('d/m/Y');
            }else{
                $date_end = mysqli_real_escape_string($conn, $_POST['date_end']);
            }

            $date_end = str_replace('/', '-', $date_end);
            $date_end = date('Y-m-d', strtotime($date_end));

         
            $dateQueryPiece = " && `pickerSheets.date` >= '$date_start' && `pickerSheets.date` <= '$date_end'";
        }

        if($CUSTOMER_ID != 0){
            $customerQueryPiece = " && pickerSheets.customer_id ='$CUSTOMER_ID'";
        }else{
            $customerQueryPiece = "";
        }

        if($USER_ID != 0){
            $userQueryPiece = " && pickerSheets.user_from_id ='$USER_ID'";
        }else{
            $userQueryPiece = "";
        }
        
        if($INTAKE_ID != 0){
            $picksheet_ids = array();

            $intakePicksheetSearchQuery = "SELECT pickerSheets.id FROM `pickerSheets`
                        JOIN `pickerItems` ON pickerItems.pickersheet_id = pickerSheets.id
                        JOIN `product` ON product.id = pickerItems.product_id
                        JOIN `pallet` ON pallet.id = product.pallet_id
                        JOIN `intake` ON intake.id = pallet.intake_id WHERE intake.id = $INTAKE_ID GROUP BY pickerSheets.id";

            $intakeQueryResult = mysqli_query($conn, $intakePicksheetSearchQuery);
            
            while($intakePicksheet = mysqli_fetch_array($intakeQueryResult)){
                array_push($picksheet_ids, $intakePicksheet['id']);
            }

            if(sizeof($picksheet_ids) > 0){
                $picksheet_ids = implode(',', $picksheet_ids);

                $intakeQueryPiece = " && pickerSheets.id IN ($picksheet_ids)";
            }
        }

        if($PALLET_ID != 0){
            $picksheet_ids = array();

            $palletPicksheetSearchQuery = "SELECT pickerSheets.id FROM `pickerSheets`
                        JOIN `pickerItems` ON pickerItems.pickersheet_id = pickerSheets.id
                        JOIN `product` ON product.id = pickerItems.product_id
                        JOIN `pallet` ON pallet.id = product.pallet_id WHERE pallet.id = $PALLET_ID GROUP BY pickerSheets.id";

            $palletQueryResult = mysqli_query($conn, $palletPicksheetSearchQuery);
            
            while($palletPicksheet = mysqli_fetch_array($palletQueryResult)){
                array_push($picksheet_ids, $palletPicksheet['id']);
            }

            if(sizeof($picksheet_ids) > 0){
                $picksheet_ids = implode(',', $picksheet_ids);

                $palletQueryPiece = " && pickerSheets.id IN ($picksheet_ids)";
            }
        }

        if($SPECIES_ID != 0){
            $cuts_array = array();
            
            $cutsResult = getCutsFor($SPECIES_ID);
            
            while($cut = mysqli_fetch_array($cutsResult)){ array_push($cuts_array, $cut['id']); }

            $cut_ids = implode(',', $cuts_array);
            
            $searchQueryString = "SELECT pickerSheets.* FROM `pickerSheets`
                        JOIN `pickerItems` ON pickerItems.pickersheet_id = pickerSheets.id
                        JOIN `product` ON product.id = pickerItems.product_id
                        WHERE pickerSheets.completed = 1 && product.cut_id in ($cut_ids) $intakeQueryPiece $palletQueryPiece $userQueryPiece $dateQueryPiece $customerQueryPiece GROUP BY pickerSheets.id";
        }else{
            $searchQueryString = "SELECT pickerSheets.* FROM `pickerSheets`
                        JOIN `pickerItems` ON pickerItems.pickersheet_id = pickerSheets.id
                        JOIN `product` ON product.id = pickerItems.product_id
                        WHERE completed=1 $intakeQueryPiece $palletQueryPiece $userQueryPiece $dateQueryPiece $customerQueryPiece GROUP BY pickerSheets.id";
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
    <input name="intake_id" placeholder="Intake ID" value="<?php echo $_POST['intake_id']; ?>" style="height:34px;width:100px;">
    <input name="pallet_id" placeholder="Pallet ID" value="<?php echo $_POST['pallet_id']; ?>" style="height:34px;width:100px;margin-right:20px;">
	<select name="species_id" style="width:152px;height:40px;">
        <option value="" disabled selected>Select species..</option>
        <option value="0">All species</option>
		<?php
			$x = "SELECT * FROM `species`";
			$y = mysqli_query($conn, $x);
			
			while($row = mysqli_fetch_array($y)){
			?><option value="<?php echo $row['id']; ?>" <?php if($_POST['species_id'] == $row['id']){ echo 'selected'; } ?>><?php echo $row['name']; ?></option><?php
			}
		?>
	</select>

    <select name="user_id" style="width:152px;height:40px;">
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


    <select name="customer_id" style="width:182px;height:40px;">
        <option value="" disabled selected>Select customer..</option>
        <option value="0">All customers</option>
		<?php
			$x = "SELECT * FROM `customers` order by businessname ASC";
			$y = mysqli_query($conn, $x);
			
			while($row = mysqli_fetch_array($y)){
			?><option value="<?php echo $row['id']; ?>" <?php if($_POST['customer_id'] == $row['id']){ echo 'selected'; } ?>><?php echo $row['businessname']; ?></option><?php
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
                    <td>£<?php echo number_format($invoice_cost, 2); ?></td>
                    <td>£<?php echo number_format($invoice_price, 2); ?></td>
                    <td>£<?php echo number_format($invoice_price - $invoice_cost, 2); ?></td>
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