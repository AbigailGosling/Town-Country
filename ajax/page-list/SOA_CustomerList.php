<?php

	require('../../functions.php');
    
    $name = $_POST['searchterm'];
?>
	<div class="cutsContainer">
	<?php
	if($name != ''){

		$customerQueryResult = mysqli_query($conn, "SELECT * FROM `customers` WHERE businessname LIKE '%$name%' || id = '$name'");
	}else{
		$customerQueryResult = mysqli_query($conn, "SELECT * FROM `customers`");
	}				
	while($customer = mysqli_fetch_array($customerQueryResult)){
	?>
	<table width="100%">
		<tr><td align="center" class="pos">
			<a href="/customer_soa.php?id=<?php echo $customer['id']; ?>" class="intake">
				<table width="100%" border="0">
					<tr>
						<td width="100" align="left">ID: <?php echo $customer['id']; ?></td>
						<td align="center" style="font-size: 18px;"><?php echo $customer['businessname']; ?></td>
						<td width="100" id="customer_id_<?php echo $customer['id']; ?>" align="right"></td>
					</tr>
				</table>
			</a>
		</td></tr>
	</table>
	<?php
	}
	?></div><?php


?>
