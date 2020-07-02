<?php

	require('../functions.php');

	$name = $_POST['searchterm'];
	
	$x = "SELECT * FROM `customers` WHERE businessname LIKE '%$name%'";
	$y = mysqli_query($conn, $x);
	$count = mysqli_num_rows($y);
	
	if($count > 0){
		while($row = mysqli_fetch_array($y)){
		?>
		<a href="javascript:;" class="intakeCutDropdown" onclick="setCustomer('<?php echo $row['id']; ?>','<?php echo $row['businessname']; ?>')"><?php echo $row['businessname']; ?><br/> t/a <?php echo $row['tradingas']; ?></a>
		<?php
		}
	}else{
	?>
	<a href="javascript:;" class="intakeCutDropdown" style="border: 1px #f00f00 solid;color:#f00f00;">You must select a vaild customer!</a>
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


function setCustomer(customer_id, text){
	console.log('customer_id: ' + customer_id);
	console.log('text: ' + text);
	$('#customer_search_results').fadeOut();
	$('#customer_id').val(customer_id);
	$('#customer').val(text);
 	setCustomerDetails(customer_id);
}
</script>