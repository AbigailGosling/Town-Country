<?php
	include('includes/frontHeader.php');
	
	$pickersheet_id = $_GET['id'];
	
	$x = "SELECT * FROM `pickerSheets` WHERE id='$pickersheet_id'";
	$y = mysqli_query($conn, $x);
	$pickSheetRow = mysqli_fetch_array($y);
	
	$customer_id = $pickSheetRow['customer_id'];
	
	$x2 = "SELECT * FROM `customers` WHERE id='$customer_id'";
	$y2 = mysqli_query($conn, $x2);
	
	$customerRow = mysqli_fetch_array($y2); 
	 
?>
<div id="top">
	<a href="menu.php" id="menu">MENU</a>
	<a href="logout.php" id="logout">LOGOUT</a>
</div>
<script type="text/javascript">

</script>
<a href="<?php echo $domain; ?>invoiceList.php"class="backbtn"  onclick="goBack()">< Back</a>
<main class="int int--extra-padding">	
	<div class="formBackButton formBackButton--invoice" style="float:right;font-size:22px;">
		<a href="viewCompletedPickSheet.php?id=<?php echo $pickersheet_id; ?>">Pick Note</a> |
 		<a href="javascript:;" onclick="printStuff()">Print</a>
	</div>
	<div id="print">
	<div class="topheading">
		
		 <div class="logocontainer">
				<img class="logo" src="<?php echo $domain; ?>images/tandclogo.jpg">
			
				13-17 Landport Ind. Est. Landport Road<br/>
				Wolverhampton WV2 2QJ<br/>
				<span>Vat. No: 701 075 285</span><br/>
				<b>01902457924</b><br/>
				
			</div>
		
		<div class="invoice">
			 
			<b style="font-size:10px;color:#8c8c8c;">Invoice address</b>
			<div class="invoicebox">
				<?php
					$customer_id = $pickSheetRow['customer_id'];
					$x = "SELECT * FROM `customers` WHERE id='$customer_id'";
					$y = mysqli_query($conn, $x);
					$customer = mysqli_fetch_array($y);
					
				?>
				<p>
					<?php echo $customer['businessname']; ?><br/>
					t/a <?php echo $customer['tradingas']; ?><br/>
					<?php echo $customer['accounts_address_1']; ?><br/>
					<?php echo $customer['accounts_address_2']; ?><br/>
					<?php echo $customer['accounts_address_3']; ?><br/>
                    <?php echo $customer['accounts_address_4']; ?><br/>
                    Customer ID: <?php echo str_pad($customer['id'], 4, '0', STR_PAD_LEFT); ?><br/>
				</p>
				<span style="display:none;">Account No: 1123ml</span>
			</div>
		</div>
		
 		
		<div class="delivery">
			<div class="deliverybox" style="border:0px;">
				<div class="po">Invoice No: <span>000<?php echo $pickersheet_id; ?></span></div>
				<h2>Invoice</h2>
			</div>
			<br/>
			<div class="deliverydate">Delivery Date: <span class="date"><?php echo $pickSheetRow['estimated_delivery_date']; ?></span></div>
			<div class="deliverydate">P.O. Number: <span><?php echo $pickSheetRow['orderReferenceNumber']; ?></span></div>
			<?php
				$date_completed = str_replace('/', '-', $pickSheetRow['date_completed']);
				$date_completed2 = date('d/m/Y', strtotime($date_completed));
				$assemblydate = date('d/m/Y G:i A', strtotime($date_completed));
				
				$date = DateTime::createFromFormat('d/m/Y', ''.$date_completed2);
				
				$paydayDelay = $customerRow['credit_terms'];
				
				$date->modify('+'. $paydayDelay .' day');
				$payByDate = $date->format('d/m/Y');
   			?>
 			<div class="po">Assembed: <span><?php echo $assemblydate; ?></span></div>
			<b style="color: #8c8c8c;font-size: 12px;">Delivery address</b>
			<div class="deliverybox">
			 
				<p>
 					<?php echo $customer['businessname']; ?><br/>
					t/a <?php echo $customer['tradingas']; ?><br/>
					<?php
						
						if($pickSheetRow['addressid'] != ''){
							if($pickSheetRow['addressid'] == 1){
								
								echo $customer['address1_1'] . '<br/>';
								echo $customer['address1_2'] . '<br/>';
								echo $customer['address1_3'] . '<br/>';
								echo $customer['postcode_1'] . '<br/>';
								
							}
							
							if($pickSheetRow['addressid'] == 2){
								
								echo $customer['address2_1'] . '<br/>';
								echo $customer['address2_2'] . '<br/>';
								echo $customer['address2_3'] . '<br/>';
								echo $customer['postcode_2'] . '<br/>';
								
							}
							
							if($pickSheetRow['addressid'] == 3){
								
								echo $customer['address3_1'] . '<br/>';
								echo $customer['address3_2'] . '<br/>';
								echo $customer['address3_3'] . '<br/>';
								echo $customer['postcode_3'] . '<br/>';
								
							}
						}
						
					?>
					
				</p>
 			</div>
		</div>
		
	</div>
	<table width="100%" border="0">
		<tr class="productsHeading" style="background-color: #7fabce9e;">
        <th align="left">Intake ID</th>
			<th align="left">Plt ID</th>
 			<th align="left" colspan="5"></th>
			<th align="center">Qty</th>
			<th align="left">Unit</th>
            <th align="right">Weight</th>
            <th align="right" class="price">Price</th>
            <th align="right" class="price">Total</th>
         </tr>
         
        <?php
                $outpalletQuery = "SELECT * FROM `palletsOut` WHERE pickersheet_id='$pickersheet_id'";
                $outpalletResult2 = mysqli_query($conn, $outpalletQuery);
                
                $outpalletCount = mysqli_num_rows($outpalletResult2);

                while($outpallet = mysqli_fetch_array($outpalletResult2)){
                    $weightids = explode(',', $outpallet['weight_ids']);
 
                    $productIDArray = array();
						
                    foreach($weightids as $weightid){
                        $x = "SELECT * FROM `weights` WHERE id='$weightid'";
                        $y = mysqli_query($conn, $x);
                        $weight = mysqli_fetch_array($y);
                       
                        if(!in_array($weight['product_id'], $productIDArray)){
                            array_push($productIDArray, $weight['product_id']);
                        }

                        $queryBits .= ' id = ' . $weightid . ' || ';
                    }
 
                    foreach($productIDArray as $productID){

                        $x1 = "SELECT * FROM `product` WHERE id='$productID'";
                        $y1 = mysqli_query($conn, $x1);
                        $product = mysqli_fetch_array($y1);
                         

                        if($product['unit'] == 'PPC'){
                            $ext = ' Cases';
                        }else{
                            $ext = ' kg';
                        }

                        $x2 = "SELECT * FROM `weights` WHERE ";

                        foreach($weightids as $weightid){
                            $x2 .= "product_id='$productID' && id='$weightid' || ";
                        }

                        $x2 = rtrim($x2," || ");
                        $y2 = mysqli_query($conn, $x2);
                        $count = mysqli_num_rows($y2);

                        ${"globalProductCount" . $product['id']} += $count;
                         
                        $k = 0;

                        while($weight = mysqli_fetch_array($y2)){
                            
                            if($weight['weight_tear'] == $weight['weight_gross']){
                                $w = $weight['weight_gross'];
                            }else{
                                $w = $weight['weight_gross'] - $weight['weight_tear'];
                            }

                            $k = $k + $w;
                        }
                        ?>
                    <tr class="productsRow">
                        <td align="left"><span class="palletid"><?php echo intakeIDfromPalletID($product['pallet_id']); ?></span></td>
                        <td align="left"><span class="palletid"><?php echo $product['pallet_id']; ?></span></td>
                        <td align="left"><span class="palletid"><?php echo getNationality($product['nationality_id']); ?></span></td>
                        <td align="left"><span class="chilled"><?php echo getTemp($product['cooling_id']); ?></span></td>
                        <td align="left"><b class="species"><?php echo getSpeciesFromCutID($product['cut_id']); ?></b></td>
                        <td align="left"><b class="cut"><?php echo getCut($product['cut_id']); ?></b></td>
                        <td align="left"><b class="brand"><?php echo getBrand($product['brand_id']); ?></b></td>
                        <?php
                            $productID = $product['id'];
                            $howManyX = "SELECT * FROM `pickerItems` WHERE pickersheet_id='$pickersheet_id' AND product_id='$productID'";
                            $howManyY = mysqli_query($conn, $howManyX);
                            $pickerItem = mysqli_fetch_array($howManyY);
                            $howMany = mysqli_num_rows($howManyY);
                        ?>
                        <td align="left"><b class="quantity"><?php echo $count; ?></b></td>
                        <td align="left">
                            <b class="unit">
                            <?php
                                
                                if($product['unit'] == 'C'){
                                    $unit = 'Cases';
                                }else if($product['unit'] == 'PPC'){
                                    $unit = 'Per Case';
                                }else if($product['unit'] == 'P'){
                                    $unit = 'Pallet';
                                }else if($product['unit'] == 'KG'){
                                    $unit = 'Kilo';
                                }else{
                                    $unit = 'Cases';
                                }
                                
                                echo $unit;
                            ?>
                            </b>
					    </td>
                        <td align="left">
                            <b class="weight">
                            <?php
                            
                                $qBit = '';
                                
                                $kg = 0;
                                
                                foreach($weightids as $weightid){
                                    $qBit .= " id = '$weightid' && product_id='$productID' || ";
                                }

                                $qBit = rtrim($qBit," || ");
                                
                                $xxWeight = "SELECT * FROM `weights` WHERE $qBit";
                                $yyWeight = mysqli_query($conn, $xxWeight);
                                
                                while($weightRow = mysqli_fetch_array($yyWeight)){
                                    
                                    if($weightRow['weight_tear'] == $weightRow['weight_gross']){
                                        $tw = $weightRow['weight_gross'];
                                    }else{
                                        $tw = $weightRow['weight_gross'] - $weightRow['weight_tear'];
                                    }
                                    
                                    $kg = $kg + $tw;
                                    
                                    $kg = number_format($kg, 2, '.', '');
                                }
                                
                                if($product['unit'] == 'PPC'){
				    echo $count . ' Cases';
				    $totalPriceRow = number_format((float)$count * $pickerItem['price'], 2, '.', '');
$totalPrice += number_format((float)$count * $pickerItem['price'], 2, '.', '');                                
                                }else{
                                    echo $kg . ' kg';
				    $totalPriceRow = number_format((float)$kg * $pickerItem['price'], 2, '.', '');
				    $totalPrice += number_format((float)$kg * $pickerItem['price'], 2, '.', '');                                
				}
                                
                            ?>
                            </b>
			</td>
                        <td align="right" class="price">£<?php echo number_format((float)$pickerItem['price'], 2, '.', ''); ?></td>
                        <td align="right" class="price">£<?php echo $totalPriceRow; ?></td>
                    </tr>
                    <?php
                        }
                    }
                ?>
 
		<?php
		$target = 16 - sizeof($productIDArray);
	 
		for($i=0;$i<$target;$i++){ ?>
			<tr class="productsRow">
					<td align="left"><span class="palletid">.</span></td>
					<td align="left"><b class="species"></b></td>
					<td align="left"><b class="cut"></b></td>
					<td align="left"><b class="brand"></b></td>	
					<td align="left"><b class="quantity"></b></td>
					<td align="left">
						<b class="unit"></b>
					</td>
					<td align="left">
						<b class="weight"></b>
					</td>
					<td align="left" class="price"></td>
					<td align="right" class="price"></td>
 				</tr>
		<?php } ?>
	</table>
  	 
		
		<div class="bankdetails">
			<b style="font-size: 10px;">BANK DETAILS</b>
			<div class="bankbox" style="background-color: #7fabce9e;">
				<div class="col1">
					<p>Town and Country Meats<br/>
					Sort Code: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 40 10 39<br/>
					Account No: 40057924</p>
					
				</div>
				<div class="col2" align="center">
                    <div class="flex">
                        <div class="bankcircle" style="margin-right:10px;">
                            UK<br/>WN070<br/>EC
                        </div>
                        <div>
                            <div class="bankcircle">
                                UK<br/>WN082<br/>EC
                            </div>
                            <span style="font-size:12px;padding-top:5px;display:block;">Unit 23</span>
                        </div>
                    </div>
				</div>
				<div class="col3">
					<div class="totalPayable"><b>Total Payable:</b> <span class="payvalue"><b>£<?php echo number_format((float)$totalPrice, 2, '.', ''); ?></b></span></div>
					<div class="paymentDue">Payment due by: <span class="payvalue"><?php echo $payByDate; ?></span></div>
				</div>
			</div>
		</div>
		
		<div class="bottom">
			<div class="col footerlogo">
                <img class="one" src="<?php echo $domain; ?>images/footer1.png">
 				<img class="one" src="<?php echo $domain; ?>images/footer2.png" style="margin-left:5px;margin-right:5px;">
				<img class="two" src="<?php echo $domain; ?>images/AIMS_LOGO_2008_002.gif">
				<img class="two" src="<?php echo $domain; ?>images/the-food-awards-england-2017-winner.jpg">
 			</div>
			
			<div class="col">
				<p>All goods remain the property of Town and Country Meats until paid for in full.</p>
				<p>Any claims must be notified within 24 hours of delivery by e-mail to:</p>
				<p>gemma@townandcountrymeats.co.uk</p>
				<p>office@townandcountrymeats.co.uk</p>
			</div>
			
			<div class="col">
				<div class="signbox">
					 
				</div>
			</div>
		</div>
	</div>
	
</main>
<div id="btm"></div>
<div id="box" style="display:none;">

</div>
<div id="editBox" style="display:none;">

</div>
<script>
	
	$(document).ready(function(){
		var totalIntakeWeight = 0.0;
		
		$('.aWeight').each(function() {
			totalIntakeWeight = parseFloat(totalIntakeWeight) + parseFloat($(this).val());
			
			// var xxD = parseFloat(totalIntakeWeight).toFixed(2);
		
		});
		
		var xxD = parseFloat(totalIntakeWeight).toFixed(3);
		
		$('#intakeTotalWeightA').text(xxD + ' KG');
	
	});
	
	function togglePrices(){
		$('.price').toggle('');
	}
	
	
	function editWeight(intake_id, pallet_id, product_id, weight_id){
		console.log('intake_id ' + intake_id);
		console.log('pallet_id ' + pallet_id);
		console.log('product_id ' + product_id);
		console.log('weight_id ' + weight_id);
		
		
		$.get( "/ajax/getEditProduct.php?intake_id=" + intake_id + "&pallet_id=" + pallet_id + "&product_id=" + product_id + "&weight_id=" + weight_id, function( data ) {	
			$('#editBox').html(data);
			$('#editBox').fadeIn();
		});
		
		
	}
	
	$('#updateIntakeButton').click(function(){
		
		var supplier_id = $('#supplier_id').val();
		var vehicle_reg = $('#vehicle_reg').val();
		var date_received = $('#date_received').val();
		var vehicle_temperature = $('#vehicle_temp').val();
		var delivery_note_number = $('#delivery_note_number').val();
		
		var good = 1;
		var msg = "";
		
		if(vehicle_reg == ''){
			msg = "The highlighted fields cannot be blank!";
			$('#vehicle_reg').css('border','2px solid red');
			good = 0;
		}else{
			$('#vehicle_reg').css('border','1px solid grey');
		}
		
		if(date_received == ''){
			msg = "The highlighted fields cannot be blank!";
			$('#date_received').css('border','2px solid red');
			good = 0;
		}else{
			$('#date_received').css('border','1px solid grey');
		}
		
		if(vehicle_temperature == ''){
			msg = "The highlighted fields cannot be blank!";
			$('#vehicle_temp').css('border','2px solid red');
			good = 0;
		}else{
			$('#vehicle_temperature').css('border','1px solid grey');
		}
		
		if(delivery_note_number == ''){
			msg = "The highlighted fields cannot be blank!";
			$('#delivery_note_number').css('border','2px solid red');
			good = 0;
		}else{
			$('#delivery_note_number').css('border','1px solid grey');
		}
		
		$('#msgNotice').html(msg);
		
		if(good == 1){
			$('#updateIntakeInfo').submit();
		}
	});
	
	function deleteProduct(product_id, cut_id){
		console.log(product_id);
		console.log(cut_id);
	}
	
	function palletDetail(id){
		
		$('.palletDetail-' + id).toggle();
	}
	
	function printPallet(intake_id, pallet_id){
		window.location.href = "http://tandc.phenixdevelopment.co.uk/printContent.php?intake_id=" + intake_id + "&pallet_id=" + pallet_id;
	}
	
	function printIntake(intake_id){
		$.ajax({
			type: "POST",
			url: 'printIntake.php?intake_id=' + intake_id,
			type: 'get',
			success: function( response ) { 

				var contents = response;
				var idname = name;

				var frame1 = document.createElement('iframe');
				frame1.name = "frame1";
				frame1.style.position = "absolute";
				frame1.style.top = "-1000000px";
				document.body.appendChild(frame1);

				var frameDoc = frame1.contentWindow ? frame1.contentWindow : frame1.contentDocument.document ? frame1.contentDocument.document : frame1.contentDocument;

				frameDoc.document.open();
				frameDoc.document.write('<html><head><meta http-equiv="Content-Type" content="text/html; charset=euc-kr"><title></title>');

	 


				frameDoc.document.write('</head><body>');
				frameDoc.document.write(contents);
				frameDoc.document.write('</body></html>');
				frameDoc.document.close();
				setTimeout(function () {
				window.frames["frame1"].focus();
				window.frames["frame1"].print();
				document.body.removeChild(frame1);
				}, 500);
				return false; 
			}
		});
	}
	
	function printContent(el){
		var restorepage = $('body').html();
		var printcontent = $('#' + el).clone();
		$('body').empty().html(printcontent);
		window.print();
		// $('body').html(restorepage);
		
		setTimeout(
			function() {
				window.location.reload(1);
			}, 10000);
	}

	function palletDetail(id){
		
		$('.palletDetail-' + id).toggle();
	}
	
	function openAddPallet(intake_id){
		
		$.get( "/ajax/addPalletForm.php?intake_id=" + intake_id, function( data ) {
			// console.log(data);
			// $('#cut_id').html('<option></option>');
			$('#box').html(data);
		});
		
		// $('#add_to_pallet_id').val(pallet_id);
		// $('.add_to_pallet_id').html('0000' + pallet_id);
		$('#box').fadeIn();
	}
	
	
	function openAddtoPallet(intake_id, pallet_id){
		
		$.get( "/ajax/editPalletForm.php?intake_id=" + intake_id + "&pallet_id=" + pallet_id, function( data ) {
			// console.log(data);
			// $('#cut_id').html('<option></option>');
			$('#box').html(data);
		});
		
		// $('#add_to_pallet_id').val(pallet_id);
		// $('.add_to_pallet_id').html('0000' + pallet_id);
		$('#box').fadeIn();
	}
	
	function deleteRow(intake_id, pallet_id){
		if(confirm('Are you sure you want to delete this?')){
			window.location.href = "/scripts/deletePallet.php?intake_id=" + intake_id + "&pallet_id=" + pallet_id;
			// console.log(intake_id + '  ' + pallet_id);
		}
	}
	
	function printStuff(){
		
		$.get("<?php echo $domain; ?>ajax/markInvoiceAsPrinted.php?id=<?php echo $_GET['id']; ?>", function(data, status){
			console.log(data);
			$('#top').hide();
			$('.formBackButton').hide();
			$('.backbtn').hide();
			$('main').css('padding','0px')
			
			window.print();
		});

	}

	function printCompleted() {
		$('#top').show();
		$('.formBackButton').show();
		$('.backbtn').show();
		$('main').removeAttr("style")
	}
	
	// printContent(1);
	
	function printContent(id){
	   $.ajax({
				type: "POST",
				url: 'printContent.php?id=' + id,
				type: 'get',
				success: function( response ) { 

					  var contents = response;
					 var idname = name;

					 var frame1 = document.createElement('iframe');
					 frame1.name = "frame1";
					 frame1.style.position = "absolute";
					frame1.style.top = "-1000000px";
					document.body.appendChild(frame1);

					var frameDoc = frame1.contentWindow ? frame1.contentWindow : frame1.contentDocument.document ? frame1.contentDocument.document : frame1.contentDocument;

				frameDoc.document.open();
				frameDoc.document.write('<html><head><title></title>');
			   
			 frameDoc.document.write('<style>table {  border-collapse: collapse;  border-spacing: 0; width:100%; margin-top:20px;} .table td, .table > tbody > tr > td, .table > tbody > tr > th, .table > tfoot > tr > td, .table > tfoot > tr > th, .table > thead > tr > td, .table > thead > tr > th{ padding:8px 18px;  } .table-bordered, .table-bordered > tbody > tr > td, .table-bordered > tbody > tr > th, .table-bordered > tfoot > tr > td, .table-bordered > tfoot > tr > th, .table-bordered > thead > tr > td, .table-bordered > thead > tr > th {     border: 1px solid #e2e2e2;} </style>');
		 
		  // your title
		   frameDoc.document.title = "Print Content with ajax in php";
	   
	   
		  frameDoc.document.write('</head><body>');
				frameDoc.document.write(contents);
				frameDoc.document.write('</body></html>');
				frameDoc.document.close();
				setTimeout(function () {
					window.frames["frame1"].focus();
					window.frames["frame1"].print();
					document.body.removeChild(frame1);
				}, 500);
				return false; 

		
		
		
				}
			});
	  
	}
	
</script>
<?php
if($customerRow['pricedefault'] == '0'){
	?><script> //$('.price').hide(); </script> <?php 
	}
?>
<style type="text/css">
	.printICON span{
		font-size:18px;
		text-transform:uppercase;
		font-weight:700;
		padding-left:10px;
	}
	
	.printICON{
		font-size:24px !important;
	}

	.printICON:active{
		color:#3faddd;
	}
	a{
		text-decoration:none !important;
	}
</style>
</body>
</html>