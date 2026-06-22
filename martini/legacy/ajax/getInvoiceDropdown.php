<?php

	require(__DIR__.'/../functions.php');

	$user_id = $_SESSION['USER'];
    $customer_id = request()->input('customer_id');
    if ($customer_id == null || $customer_id == "") {
        exit;
    }
	$name = request()->input('searchterm');
	$isSaleScreen = (request()->has('salescreen'));
    $isReservationScreen = (request()->has('salescreen') && request()->input('salescreen')=="r");
	$y = prepareExecuteQuery("SELECT * FROM `pickersheets` WHERE `customer_id` = ? AND `deleted` = 0 AND `is_return_to_supplier` = 0 AND `id` LIKE ? ORDER BY `id` DESC",'is',[$customer_id, $name.'%']);
	$count = mysqli_num_rows($y);
	?> <script>var customerIDs =  [];</script> <?php
	if($count > 0){
		while($row = mysqli_fetch_array($y)){
		?>
		<script>customerIDs.push(<?php echo $row['id']; ?>);</script>
		<a href="javascript:;" class="intakeCutDropdown" onclick="setInvoice('<?php echo $row['id']; ?>')">Invoice: <?php echo $row['id']; ?> - Estimated Delivery: <?php echo $row['estimated_delivery_date']; ?></a>
		<?php
		}
	}else{
	?>
	<a href="javascript:;" class="intakeCutDropdown" onclick="setInvoice('')" style="border: 1px #f00f00 solid;color: #f00f00;">No Invoices Found</a>
	<?php
	}
?>

<script type="text/javascript">
$(document).ready(function(){
	$('.speciesName').click(function(){
		$(this).next('.cutsContainer').toggle();
		console.log(1);
	});
});

</script>
