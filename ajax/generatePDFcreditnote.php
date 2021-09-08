<?php
	require('../functions.php');

	ini_set('memory_limit', '1024M');
	
	require_once '../vendor/autoload.php';
	
	
	$perPage = 29;
 	$border = 0;
 	$mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => [210, 297],
		'setAutoTopMargin' => 'stretch',
        'autoMarginPadding' => 0,
        'bleedMargin' => 0,
        'crossMarkMargin' => 0,
        'cropMarkMargin' => 0,
        'nonPrintMargin' => 0,
        'margBuffer' => 0,
        'collapseBlockMargins' => true,
    ]);
	
 	$pageArray = array();
	
	$payment_id = $_GET['payment_id'];
	$pickersheet_id = $_GET['id'];
	
	$x = "SELECT * FROM `pickerSheets` WHERE id='$pickersheet_id'";
	$y = mysqli_query($conn, $x);
	$pickSheetRow = mysqli_fetch_array($y);
	
	$customer_id = $pickSheetRow['customer_id'];
	
	$x2 = "SELECT * FROM `customers` WHERE id='$customer_id'";
	$y2 = mysqli_query($conn, $x2);
	
	$customerRow = mysqli_fetch_array($y2); 

	$customer_id = $pickSheetRow['customer_id'];
	$x = "SELECT * FROM `customers` WHERE id='$customer_id'";
	$y = mysqli_query($conn, $x);
	$customer = mysqli_fetch_array($y);
	
	$date = str_replace('/', '-', $pickSheetRow['date_completed']);
	$assemblydate = date('d/m/Y', strtotime($date));
	
	$date = DateTime::createFromFormat('d/m/Y', ''.$assemblydate);
	
	$paydayDelay = $customerRow['credit_terms'];
	
	$date->modify('+'. $paydayDelay .' day');
	$payByDate = $date->format('d/m/Y');
	
	$header .= '<link href="https://fonts.googleapis.com/css?family=Roboto:300,400,700&display=swap" rel="stylesheet">';
	$header .= '<link href="https://fonts.googleapis.com/css?family=Handlee&display=swap" rel="stylesheet">';
	
	$css ="
		body{
			font-family: 'Roboto', sans-serif;
			margin:0px;
		}
		
		table{
			display:table;
		}
		
		 
		
		tr.productsRow{
			height:70px;
 		}
		
		td{ display:table-cell; }
		.logo{
			width: 330px;
			padding-bottom: 10px;
			display:block;
		}
		
		.deliverybox{
			padding: 5px;
			background-color: #cacaca;
			display:block;
			width:200px;
		}
		
		
		.mainaddress{
			display:block;
 		}
		
		.greybox{
			border:1px solid #8c8c8c;
			background-color:#cacaca;
		}
		
		.assembed{
			text-align: right;
			font-size: 13px;
			color: #8c8c8c;
		}
		
		.greybox .invoiceno{
			text-align: right;
			font-size: 13px;
			color: #8c8c8c;
		}
		
		.deliveryaddresstd{
			border:1px solid #8c8c8c;
			background-color:#cacaca;
		}
		
		.invoiceaddresstd{
			border: 1px solid #8c8c8c;
			padding:5px;
		}
		
		.heading{
			background-color:#b4454b;
		}
		
		td.bankdetails{
			background-color:#b4454b;
		}
		
		td.bankdetailsLabel{
			font-size: 10px;
		}
		
		td.signbox{
 			font-family: 'Handlee', cursive !important;
		}
		
		td.footertext{
			text-align: center;
			padding: 0;
			margin: 0;
			font-size: 10px;
			padding-bottom: 2px;
		}
		
		td.picknotetd{
			font-size: 14px;
			text-decoration:none;
			color:grey;
		}
		
		td.brand{
			font-size:8px;
		}

		td.species, td.cut{
			font-size:10px;
		}
		
		td.palletid{
			font-size:10px;
		}
		
		td.chilled{
			font-size:10px;
			padding-right:10px;
		}
		
		td.quantity{
			font-size:10px;
		}
		
		td.unit{
			font-size:10px;
		}
		
		td.weight{
			font-size:10px;
		}
	";
	
	$mpdf->WriteHTML($css,\Mpdf\HTMLParserMode::HEADER_CSS);
	$header .= '
	<table border="'.$border.'" width="100%">
		<tr valign="top">
			<td align="center" width="50%">
				<img class="logo" src="' .$domain .'images/tandclogo.jpg"><br/>
				<div class="mainaddress">
					13-17 Landport Ind. Est. Landport Road<br/>
					Wolverhampton WV2 2QJ<br/>
					<span>Vat. No: 701 075 285</span><br/>
					<b>01902457924</b><br/>
				</div>
			</td>
			<td align="right" width="50%">
			 	<table width="200" border="'.$border.'" style="padding-bottom:10px;">
					<tr>
						<td class="greybox">
							<span class="invoiceno">Credit Note No:  000'. $payment_id .'</span>
							<h2>Credit note</h2>
						</td>
					</tr>
				</table>
				
				<table width="200" border="'.$border.'">
					<tr>
						<td class="greybox">
							<div class="deliverydate">Delivery Date: <span class="date">'.  $pickSheetRow['estimated_delivery_date'] .'</span></div>
						</td>
					</tr>
				</table>
				
				
				<table width="200" border="'.$border.'">
					<tr>
						<td class="greybox">
							<div class="deliverydate">Invoice Number: <span class="date">'. $pickSheetRow['id'] .'</span></div>
						</td>
					</tr>
				</table>
				
				<table width="200" border="'.$border.'">
					<tr>
						<td class="greybox">
							<div class="deliverydate">P.O Number: <span class="date">mt396</span></div>
						</td>
					</tr>
				</table>
				
				<table width="200" border="'.$border.'">
					<tr>
						<td>
							<div class="assembed">Assembled: <span>'. $assemblydate .'</span></div>
						</td>
					</tr>
				</table>
				 
				 
 			</td>
		</tr>
		<tr>
			<td class="invoiceaddresstd" width="100px">
				<div class="invoiceaddress">
					'. $customer['businessname'] .'<br/>
					t/a'. $customer['tradingas'] .'<br/>
					'. $customer['address1_1'].'<br/>
					'. $customer['address1_2'].'<br/>
					'. $customer['address1_3'].'<br/>
					'. $customer['postcode_1'].'<br/>
				</div>
			</td>
			<td align="right" width="90%">
				
				<table width="200" border="'.$border.'">
					<tr>
						<td align="right"><span>Delivery address</span></td>
					</tr>
					<tr>
						<td class="deliveryaddresstd">
							<div class="deliveryaddress">
								'. $customer['businessname'] .'<br/>
								t/a'. $customer['tradingas'] .'<br/>
								'. $customer['address1_1'].'<br/>
								'. $customer['address1_2'].'<br/>
								'. $customer['address1_3'].'<br/>
								'. $customer['postcode_1'].'<br/>
							</div>
						</td>
					</tr>
				</table>
				
 				 
			</td>
		</tr>
		
		</table>';
		
		$pageHeader .= '<table width="100%"><tr>
			<td colspan="2">
				<table width="100%" border="'.$border.'">
					<tr>
						<td class="heading" width="50">Plt ID</td>
						<td class="heading" colspan="4"></td>
						<td class="heading" width="65">Quantity</td>
						<td class="heading" width="65">Unit</td>
						<td class="heading" colspan="1">Weight</td>
						<td class="heading" colspan="1">Price</td>
						<td class="heading" colspan="2">Sub Total</td>
					</tr>';
			 	
				$paymentsResult = mysqli_query($conn, "SELECT * FROM `credit_note_items` WHERE payment_id='$payment_id'");

				$total_qty_count = 0;
				while($payment = mysqli_fetch_array($paymentsResult)){
					$total_qty_count += $payment['quantity'];
					$productID = $payment['product_id'];
				
					$productResult = mysqli_query($conn, "SELECT * FROM `product` WHERE id='$productID'");
					$product = mysqli_fetch_array($productResult);
					
					if($productID == 0){
						$html .='
						<tr class="productsRow">
						<td></td>
						<td align="left" colspan="4" class="brand"><b class="brand">'. $payment['description'] .'</b></td>
						<td align="right" class="quantity"><b class="quantity">'. $payment['quantity'] .'</b></td>
						<td align="left" class="unit">
							<b class="unit">'. $unit .'</b>
						</td>
						<td></td>
						<td>£'. number_format((float)$payment['price'], 2, '.', '') .'</td>
						<td>£'. number_format((float)$payment['price'] * $payment['quantity'], 2, '.', '') .'</td>
						</tr>';

						$totalPrice += number_format((float)$payment['price'] * $payment['quantity'], 2, '.', '');
					}else{
					
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

						$weight = weightFromProductIDArray([$productID]);

						$html .='
						<tr class="productsRow">
						<td align="left" class="palletid"><span class="palletid">'. $product['pallet_id'] .'</span></td>
						<td align="left" class="chilled"><span class="chilled">'. getTemp($product['cooling_id']) .'</span></td>
						<td align="left" class="species"><b class="species">' . getSpeciesFromCutID($product['cut_id']) .'</b></td>
						<td align="left" class="cut"><b class="cut">'. getCut($product['cut_id']).'</b></td>
						<td align="left" class="brand"><b class="brand">'. getBrand($product['brand_id']) .'</b></td>
						<td align="right" class="quantity"><b class="quantity">'. $payment['quantity'] .'</b></td>
						<td align="left" class="unit">
							<b class="unit">'. $unit .'</b>
						</td>
						<td>'. $weight .'kg</td>
						<td>£'. number_format((float)$payment['price'], 2, '.', '') .'</td>
						<td>£'. number_format((float)$payment['price'] * $weight, 2, '.', '') .'</td>
						</tr>';
								
					
						$totalPrice += number_format((float)$payment['price'] * $weight, 2, '.', '');					
					}
  				}

				$html .= '<tr class="heading">
				  <th align="left" colspan="5">Total:</th>
				  <th align="center">' . $total_qty_count . '</th>
				  <th align="left"></th>
				  <th align="left"></th>
				  <th align="left"></th>';
				$html .= '<th align="price" colspan="2" class="price">£' . number_format((float)$totalPrice, 2, '.', '') . '</th>';
				

				$html .='</tr>';
				array_push($pageArray, $html);
	

	
	$pageFooter .= '</table>
				</td>
			</tr>
		</tr>
		</table>';
		
	$footer = '<table width="100%">
		<tr>
			<td colspan="2" class="bankdetailsLabel">Bank Details</td>
		</tr>
		<tr>
			<td colspan="2" class="bankdetails">
			<table width="100%" border="'.$border.'">
				<tr>
					<td>
						<p>Town and Country Meats<br/>
						Sort Code: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 40 10 39<br/>
						Account No: 40057924</p>
					</td>
					<td align="center">
						<img src="'. $domain .'images/ecblue.jpg">
					</td>
					<td align="right">
						<div class="totalPayable"><b>Total Payable:</b> <span class="payvalue"><b>£'. number_format((float)$totalPrice, 2, '.', '') .'</b></span></div>
						<div class="paymentDue"></span></div>
					</td>
				</tr>
			</table>
			</td>
		</tr>
		
		<tr>
			<td colspan="2">
			<table width="100%" border="'.$border.'">
			<tr>
			<td>
				<table border="'.$border.'">
				<tr>
					<td><img class="one" height="60" src="' . $domain .'images/image002.png"></td>
					<td><img class="two" height="45" src="' . $domain .'images/AIMS_LOGO_2008_002.gif"></td>
					<td><img class="two" height="45" src="' . $domain .'images/the-food-awards-england-2017-winner.jpg"></td>
				</tr>
				</table>
			</td>
			<td class="footertext">
				<p>All goods remain the property of Town and Country Meats until paid for in full.</p>
				<p>Any claims must be notified within 24 hours of delivery by e-mail to:</p>
				<p>gemma@townandcountrymeats.co.uk</p>
				<p>office@townandcountrymeats.co.uk</p>
			</td>
			<td width="180"></td>
			</tr>
			</table> 
			</td>
			</tr>
			</table>
			</td>
		</tr>
	</table>
	';
	$mpdf->SetHTMLHeader($header);
	
	
 	
 	foreach($pageArray as $page){
		$mpdf->SetHTMLFooter($footer);
		$mpdf->AddPage();
		$mpdf->WriteHTML($pageHeader);
		$mpdf->WriteHTML($page);
		$mpdf->WriteHTML($pageFooter);
	}
	
	
   	$mpdf->SetHTMLFooter($footer);
 
 
 
 	$filename2 = 'Credit_Note_'.$pickersheet_id.'.pdf';
	$filename = '../PDF/' . $filename2;
	
 	
	$mpdf->Output($filename,'F');

	echo $filename2;
?>

<script>
	window.location.href="/PDF/<?php echo $filename2; ?>";
</script>