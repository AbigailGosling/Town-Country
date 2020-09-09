<?php
	include('functions.php');
	
	if($_GET['id'] != ''){
		
		$id = mysqli_real_escape_string($conn, $_GET['id']);
		
		$x = "SELECT * FROM `purchase_form` WHERE id='$id'";
		$y = mysqli_query($conn, $x);
		
		$purchase = mysqli_fetch_array($y);
		$edit=true;
		
	}else{ $edit=false; }
?>
<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Town &amp; Country</title>
	<link href="css/style.css" rel="stylesheet" type="text/css">
	<link href="css/font-awesome.css" rel="stylesheet" type="text/css">
	<link href="css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css">
	<link href="css/jquery.datetimepicker.min.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script src="js/jquery.datetimepicker.min.js"></script>
	<link href="/css/bootstrap-combined.min.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" media="screen" href="/css/bootstrap-datetimepicker.min.css">
</head>
<body onafterprint="afterPrint()">
<div id="top" class="printhide">
	<a href="menu.php" id="menu">MENU</a>
	<a href="logout.php" id="logout">LOGOUT</a>
</div>
<main class="int">
	<div id="product">
		<div id="product_heading" class="printhide"><?php if($edit){ echo 'Edit'; }else{ echo 'New'; } ?> Purchase</div>		
		<form method="POST" enctype="multipart/form-data" id="mainForm" action="/scripts/<?php if($edit){ echo 'editPurchase.php'; } else { echo 'newPurchase.php'; } ?>" autocomplete="off">
		<input autocomplete="off" name="hidden" type="text" style="display:none;">
        <div id="product_options" style="display:flex;flex-wrap:wrap;width:190px;">
			<?php if($edit){ ?><a href="javascript:myprint();" class="printhide bluebtn" style="width:100%;text-align:center;margin-top:10px;">Print Purchase</a><?php } ?>
			<input type="submit" value="<?php if($edit){ echo 'Update'; }else{ echo 'Save'; } ?> Purchase" class="printhide bluebtn" style="margin-top:10px;width:100%;display:block;">
			
			<?php
				$purchaseid = $purchase['id'];
				
				$tx = "SELECT id FROM intake WHERE purchase_id='$purchaseid'";
				$ty = mysqli_query($conn, $tx);
				$txcount = mysqli_num_rows($ty);
				$intake = mysqli_fetch_array($ty);

				if($txcount > 0){
				?><a href="intake.php?id=<?php echo $intake['id']; ?>" id="viewIntake" class="printhide bluebtn" style="width:100%;text-align:center;margin-top:10px;">View Intake</a><?php
				}else{
				?><a href="newDelivery.php?purchaseid=<?php echo $purchaseid; ?>" class="printhide bluebtn" style="width:100%;text-align:center;margin-top:10px;">Create Intake</a><?php
				}
            ?>
            
		</div>
		<table>
			<tbody>
				<tr>
					<td><div id="msgNotice" style="color:red;"></div></td>
				</tr>
				<tr>
				<td>
					<label>Supplier</label>
					<?php
						if($edit){
							$supplier_id = $purchase['supplier_id'];
							
							$x ="SELECT * FROM `supplier` WHERE id='$supplier_id'";
							$y = mysqli_query($conn, $x);
							$row = mysqli_fetch_array($y);	
						}
					?>
					<input name="id" id="id" type="text" style="display:none;" value="<?php echo $purchase['id']; ?>">
					<input name="supplier_id" id="supplier_id" type="text" style="display:none;" value="<?php echo $purchase['supplier_id']; ?>" required>
					<input name="supplier_search" id="supplier_search" type="text" value="<?php echo $row['name']; ?>">
					<div id="supplier_search_results">
						
					</div> 
				</td>
				<td>
					<label class="printinput">Purchased By</label>
					<input type="text"  name="purchased_by" value="<?php echo $purchase['purchased_by']; ?>">
				 
				</td>
				</tr>
				<tr>
				<td>
					<label class="printinput">Booking Ref No</label>
					<input type="text" name="booking_ref_number" value="<?php echo $purchase['booking_ref_number']; ?>">
				</td>
				<td>
					<div style="display:none;">
						<label class="printinput" >Transportation</label>
						<input type="text" name="transportation" value="<?php echo $purchase['transportation']; ?>">
					</div>
				</td>
				</tr>
				<tr>
				<td>
					<label class="printinput">Haulier</label>
					<input type="text" name="haulier" value="<?php echo $purchase['haulier']; ?>">
				</td>
				<td>
					<label>Direct Drop</label>
					<select name="direct_drop" id="direct_drop" style="width:192px;height:30px;">
						<option value="0" <?php if($purchase['direct_drop'] == 0){ echo 'selected'; } ?>>No</option>
						<option value="1" <?php if($purchase['direct_drop'] == 1){ echo 'selected'; } ?>>Yes</option>
					</select>
				</td>
				</tr>
				<tr>
				<td>
					<?php
						if($edit){
							$date_purchased = str_replace('/', '-', $purchase['date_purchased']);
							$date_purchased = date('d/m/Y', strtotime($date_purchased));
						}
					?>
					<div style="display:none;">
						<label>Date Purchased</label>
						<input type="text" name="date_puriuhsaiuhsachased" value="<?php if($date_purchased != ''){ echo $date_purchased; }else { echo date('d/m/Y'); }?>" id="date_purchased" required>
					</div>
					
					<div id="datetimepicker2" class="input-append date">
					  <label>Date Purchased</label>
					  <input type="text" name="date_purchased" value="<?php if($date_purchased != ''){ echo $date_purchased; }else { echo date('d/m/Y'); }?>" required></input>
					  <span class="add-on printhide">
						<i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
					  </span>
					</div>
					
				</td> 
				<td>
					<?php
						if($edit){
							$date_due = str_replace('/', '-', $purchase['date_due']);
							$date_due = date('d/m/Y H:00', strtotime($date_due));
						}
					?>
					<div style="display:none;">
					<label>Date Due</label>
					<input type="text" name="datasde_due" id="date_due" value="<?php if($edit){ echo $date_due; } ?>">
					</div>
					
					<div id="datetimepicker" class="input-append date">
					  <label>Date Due</label>
					  <input type="text" name="date_due" value="<?php echo $date_due; ?>" required></input>
					  <span class="add-on printhide">
						<i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
					  </span>
					</div>
					
					 
					
				</td> 
				</tr>
				<tr>
				<td colspan="2">
					<label>Attachment <?php if($purchase['dfile'] != ''){ ?>- <a href="<?php echo $domain . 'documents/' . $purchase['dfile']; ?>" target="_blank">Click here to view file</a><?php } ?></label>
					<?php if($purchase['dfile'] != ''){ ?>
					 
					<?php } ?>
					<input type="file"name="dfile" style="width:600px;border:1px solid #cacaca;padding:5px;">
				</td>
				</tr>
				<tr>
				<td colspan="2">
					<label>Comments</label>
					<textarea name="comments" style="width:600px;height:110px;resize:none;padding:5px;"><?php echo $purchase['purchase_comments']; ?></textarea>
				</td>
				</tr>
			</tbody>
		</table>
		<b style="margin:0;padding-top:20px;display:block;">Products  <a href="javascript:;" onclick="newProduct()">[+]</a></b><br/>
			<div class="productsList">
				<?php
					if($edit){
						 

						$species = explode('|', $purchase['species']);
						$cuts = explode('|', $purchase['cut']);
						$units = explode('|', $purchase['units']);
						$prices = explode('|', $purchase['price']);
						
						$size = sizeof($species);
						
						for($i=0;$i<$size;$i++){
							
							?>
							<div>
							
							<input type="text" name="species[]" class="producttext" value="<?php echo $species[$i]; ?>">
							<input type="text" name="cuts[]" class="producttext" value="<?php echo $cuts[$i]; ?>">
							<input type="text" name="units[]" class="producttext" value="<?php echo $units[$i]; ?>" style="width:120px;">
							<input type="text" name="prices[]" class="producttext" value="<?php echo $prices[$i]; ?>" style="width:120px;">
 
							<span class="printhide"onclick="removeProduct(this);">[-]</span>
							</div>
							<?php
						}
					}else{
					?>
					<?php
					}
				?>
			</div>
		</form>
	</div>
	 
</main>
<div id="btm"></div>
<script>
	
	<?php
		$speciesX = "SELECT * FROM `species`";
		$speciesY = mysqli_query($conn, $speciesX);
		
		$cutX = "SELECT * FROM `cuts` ORDER BY name";
		$cutY = mysqli_query($conn, $cutX);
	?>
	
	function newProduct(){
		div = '<div>';
		div += '<input type="text" placeholder="species" class="producttext inputProductsText2" name="species[]">';
		div += '<input type="text" placeholder="cuts" class="producttext inputProductsText2" name="cuts[]">';
		div += '<input type="text" placeholder="units" class="producttext inputProductsText2" name="units[]" style="width:120px;">';
		div += '<input type="text" placeholder="price" class="producttext inputProductsText2" name="prices[]" style="width:120px;">';
		div += '<span onclick="removeProduct(this);">[-]</span>';
		div += '</div>';
		
		$('.productsList').append(div  + '');
	}
	
	// newProduct();
	function removeProduct(obj){
		$(obj).parent().remove();
	}
	
	<?php if(!$edit){ ?> for(i=0;i<5;i++){ newProduct(); } <?php } ?>
	
	$(document).ready(function(){
		$('#direct_drop').change(function(){
			if($(this).val() == '1'){
				$('#viewIntake').fadeOut();
			}else{
				$('#viewIntake').fadeIn();
			}
		});
		// $( "#date_purchased" ).datepicker({ dateFormat: 'dd/mm/yy' });
			
		// $( "#date_due" ).datepicker({ dateFormat: 'dd/mm/yy' });
		// $( "#date_due" ).datetimepicker({format: 'yyyy-mm-dd hh:ii'});

		 
		$('#supplier_search').keyup(function(){
			var val = $('#supplier_search').val();
			if(val != ''){
				$('#supplier_search_results').fadeIn();
			}else{
				$('#supplier_search_results').fadeOut();
			}
			
			var species = $('#species_id').val();
			
			var xhttp = new XMLHttpRequest();
			xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
			  // document.getElementById("demo").innerHTML = this.responseText;
			  $('#supplier_search_results').html(this.responseText);
			}
			};
			xhttp.open("POST", "/ajax/getSupplierDropdown.php", true);
			xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhttp.send("searchterm=" + val + "&species_id=" + species);
		
		}); 
	});

    function myprint(){
        $('.printhide').hide();
        //$('.printinput').css('padding','5px');
		print();   


    }

    function afterPrint(){
        $('.printhide').show();
    }
</script>
  <style>
  select, textarea, input[type="text"], input[type="password"], input[type="datetime"], input[type="datetime-local"], input[type="date"], input[type="month"], input[type="time"], input[type="week"], input[type="number"], input[type="email"], input[type="url"], input[type="search"], input[type="tel"], input[type="color"], .uneditable-input{
	margin:0;
	margin-bottom:5px !important;
	margin-right:5px !important;
  }
  </style>
    <script type="text/javascript"
     src="/js/bootstrap.min.js">
    </script>
    <script type="text/javascript"
     src="/js/bootstrap-datetimepicker.min.js">
    </script>
    <script type="text/javascript"
     src="/js/bootstrap-datetimepicker.pt-BR.js">
    </script>
    <script type="text/javascript">
      $('#datetimepicker').datetimepicker({
			format: 'dd/MM/yyyy hh:mm',
			minuteStep: 30,
			minView : 2,
			minuteStepping:30,
			
			timeFormat:  "HH:00",
      });
	  
	  $('#datetimepicker2').datetimepicker({
        format: 'dd/MM/yyyy',
		minuteStep: 30,
		minuteStepping:30,
		timeFormat:  "HH:00"
      });
    </script>
	
</body>
</html>
