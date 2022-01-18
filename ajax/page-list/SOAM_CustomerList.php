<?php

	require('../../functions.php');
    require('../customer_soa_results_function.php');
    $name = $_POST['searchterm'];
	$showBal = false;
	$rollingTotal = 0;
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
	while($customer = mysqli_fetch_assoc($customerQueryResult)){
		$customer['balance'] = "";
		$customer['balNeg'] = false;
		if ($showBal == true)
		{
			$cache_check = check_customer_outstanding_cache($customer['id']);
			if ($cache_check['outdated'] == true)
			{
				$cache_check['outstanding2'] = $cache_check['outstanding'];
				$cache_check['outstanding'] = (float)totalOutstandingForCustomer($customer['id']);
				update_customer_outstanding_cache($cache_check);
			}
			$customer['cache'] = $cache_check;
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
		if ($customer['balance'] == 0) continue;
		$rollingTotal = $rollingTotal + $customer['balance'];
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
		</td>
		<td align="center" class="pos" width="50" height="50">
			<a href="javascript:void(0)" height="100%" id="mail-selector-<?php echo $customer['id']?>" class="mail-selector"
	>
				<table border="0" style="min-height: 46px;">
					<tr>
						<td min_ align="center" style="font-size: 18px;"><i id="img-mail-selector-<?php echo $customer['id']?>" class="fa fa-check img-mail-selector"></i></td>
					</tr>
				</table>
			</a>
		</td>
	</tr>
	</table>
	<?php
	}
	?>
	<tr><td align="center" class="pos">
			<a href="#" class="intake">
				<table width="100%" border="0">
				<tr>
					<td width="100" align="left"></td>
					<td align="center" style="font-size: 18px;">Total: </td>
					<td width="100" align="right">
						<?php if ($showBal) {
							echo "£".number_format($rollingTotal,2);								 
						}?></td>
				</tr>
				</table>
			</a>
		</td></tr>
	</table>
	</div><?php


?>
