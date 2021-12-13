<?php

	require('../../functions.php');
    require('../customer_soa_results_function.php');
    $name = $_POST['searchterm'];
	$showBal = false;
	if (isset($_POST['showBal']) && $_POST['showBal'] == 1) $showBal = true;
?>
	<div class="cutsContainer">
	<?php
	if($name != ''){

		$customerQueryResult = mysqli_query($conn, "SELECT * FROM `customers` WHERE businessname LIKE '%$name%' || id = '$name'");
	}else{
		$customerQueryResult = mysqli_query($conn, "SELECT * FROM `customers`");
	}
	$workingSet = [];				
	while($customer = mysqli_fetch_array($customerQueryResult)){
		$customer['balance'] = "";
		$customer['balNeg'] = false;
		if ($showBal == true)
		{
			$cache_check = check_customer_outstanding_cache($customer['id']);
			if ($cache_check['outdated'] == true)
			{
				$cache_check['outstanding'] = (float)totalOutstandingForCustomer($customer['id']);
				update_customer_outstanding_cache($cache_check);
			}
			$customer['balance'] = (float) $cache_check['outstanding'];
		}
		$workingSet[] = $customer;
	}
	if ($showBal == true)
	{
		$balance = array_column($workingSet, 'balance');
		array_multisort($balance, SORT_DESC, $workingSet);
	}
	foreach($workingSet as $customer)
	{
	?>
	<table width="100%">
		<tr><td align="center" class="pos">
			<a href="/customer_soam.php?id=<?php echo $customer['id']; ?>" class="intake">
				<table width="100%" border="0">
					<tr>
						<td width="100" align="left">ID: <?php echo $customer['id']; ?></td>
						<td align="center" style="font-size: 18px;"><?php echo $customer['businessname']; ?></td>
						<td width="100" align="right">
							<?php if ($showBal) {
								if ($customer['balNeg'] == true) echo "-";
								echo "£".number_format($customer['balance'],2);								 
							}?></td>
					</tr>
				</table>
			</a>
		</td></tr>
	</table>
	<?php
	}
	?></div><?php


?>
