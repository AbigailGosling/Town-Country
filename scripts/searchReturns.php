<table width="100%" class="slim searchRContent"   style="display:table;padding-left:20px;">
	<th align="left" width="30">Pallet ID</th>
	<th align="left" width="200">Product</th>
<?php
	require('../functions.php');
	
	$intakeID = $_GET['intakeID'];
	
	$px = "SELECT * FROM `pallet` WHERE intake_id='$intakeID'";
	$py = mysqli_query($conn, $px);
	
	
	$pString = '';
	$palletID = array();
	while($pallet = mysqli_fetch_array($py)){
		$palletID[] = $pallet['id'];

	}
	$x = "SELECT * FROM `product` WHERE pallet_id IN (".implode(",",$palletID).")";
	$y = mysqli_query($conn, $x) or die(mysqli_error($conn));
	
	while($product = mysqli_fetch_array($y)){	 
 	?>
	<tr class="content" onclick="toggleSelect(this, <?php echo $product['id']; ?>)">
		<td><?php echo $product['pallet_id']; ?></td>
		<td><?php echo getSpeciesFromCutID($product['cut_id']); ?> - <?php echo getCut($product['cut_id']); ?></td>
	</tr>
	<?php
	}
?>
</table>

<form method="POST" action="/scripts/markProductsReturned.php">
	<input type="text" id="ids" name="ids" style="display:none;">
	<input type="submit" class="returnStockBtn" value="Return to stock">
</form>
<script>
	
	var array = [];
	
	function toggleSelect(ele, product_id){
		
		if($(ele).hasClass('sel')){
			$(ele).removeClass('sel');
			removeItem(array, product_id);
			
		}else{
			$(ele).addClass('sel');
 			array.push(product_id);
		}
		
		console.log(array);
	}
	
	function removeItem(array, item){
		for(var i in array){
			if(array[i]==item){
				array.splice(i,1);
				break;
			}
		}
	}
	
	
	setInterval(function(){
		if(array.length > 0){
			$('.returnStockBtn').fadeIn();
			$('#ids').val(array);
		}else{
			$('.returnStockBtn').fadeOut();
		}
	}, 200);
	
</script>

<style type="text/css">
	
	.searchRContent tr.content:hover{
		background:#cacaca;
		cursor:pointer;
	}
	
	tr.content.sel{
		background:#cacaca;
	}
	
	tr.content {
		height: 30px;
	}
	
</style>