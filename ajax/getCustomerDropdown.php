<?php

	require('../functions.php');
	
	$user_id = $_SESSION['USER'];

	$name = $_POST['searchterm'];

	$x = "SELECT * FROM `customers` WHERE businessname LIKE '$name%' || businessname LIKE '%$name%' || businessname LIKE '$name%' || REPLACE(businessname, ' ', '') LIKE '$name%' || REPLACE(businessname, ' ', '') = '$name'";
	$y = mysqli_query($conn, $x);
	$count = mysqli_num_rows($y);
	?> <script>var customerIDs =  [];</script> <?php
	if($count > 0){
		while($row = mysqli_fetch_array($y)){
		?>
		<script>customerIDs.push(<?php echo $row['id']; ?>);</script>
		<a href="javascript:;" class="intakeCutDropdown" onclick="setCustomer('<?php echo $row['id']; ?>','<?php echo addslashes($row['businessname']); ?>')"><?php echo $row['businessname']; ?><br/> t/a <?php echo $row['tradingas']; ?></a>
		<?php
		}
	}else{
	?>
	<a href="javascript:;" class="intakeCutDropdown" style="border: 1px #f00f00 solid;color:#f00f00;">You must select a valid customer!</a>
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