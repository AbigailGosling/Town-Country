<?php
	require(__DIR__.'/../../functions.php');

	if (isset($_SESSION['SOAM_CACHING']))
	{
		if (time() > ($_SESSION['SOAM_CACHING'] + 600))
		{
			session_start();
			$_SESSION['SOAM_CACHING'] = time();
			session_write_close();
		}
		else
		{
			die("STOP");exit;
		}
	}
	else
	{
		session_start();
		$_SESSION['SOAM_CACHING'] = time();
		session_write_close();
	}

    require(__DIR__.'/../customer_soa_results_function.php');
    $name = request()->input('searchterm');
	$showBal = false;
	$rollingTotal = 0;
	if (request()->input('showBal') !== null && request()->input('showBal') == 1) $showBal = true;
?>
	<div class="cutsContainer">
	<?php
	if($name != ''){

		$customerQueryResult = prepareExecuteQuery("SELECT * FROM `customers` WHERE `businessname` LIKE ? || `id` = ?",'ss',['%'.$name.'%',$name]);
	}else{
		$customerQueryResult = prepareExecuteQuery("SELECT * FROM `customers` WHERE `disabled`=0");
	}
	$workingSet = [];				
	while($customer = mysqli_fetch_assoc($customerQueryResult)){
		$customer['balance'] = "";
		$customer['balNeg'] = false;
		if ($showBal == true)
		{
			$t = time();
			$customer['creditCheck'] = precredit_check($customer['id']);
			$cache_check = $customer['creditCheck']['details'];
			if (!$cache_check) $cache_check = check_customer_outstanding_cache($customer['id']);
			$customer['cache'] = $cache_check;
			$customer['balance'] = (double) $cache_check['outstanding'];
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
		$creditCheck = $customer['creditCheck'];
		$rollingTotal = (double)$rollingTotal + (double)$customer['balance'];
		$title = "";
		if ($creditCheck['saleAllowed'] == true)
		{
			if ($creditCheck['showWarning'] == true)
			{
				$style = 'style="background-color:orange;color:orange"';
				$title = $creditCheck['message'];
				$text = "A";
			}
			else
			{
				$style = 'style="background-color:green;color:green"';
				$title = "";
				$text = "G";
			}
		}
		else
		{
			$style = 'style="background-color:red;color:red"';
			$title = $creditCheck['message'];
			$text = "R";
		}
	?>
	<table width="100%">
		<tr><td align="center" class="pos">
			<a href="customer_soam.php?id=<?php echo $customer['id']; ?>" class="intake">
				<table width="100%" border="0">
					<tr>
						<td width="100" align="left">ID: <?php echo $customer['id']; ?></td>
						<td align="center" style="font-size: 18px;"><?php echo $customer['businessname']; ?></td>
						<td width="100" align="right">
							<?php if ($showBal) {
								if ($customer['balNeg'] == true) echo "-";
								echo "£".number_format($customer['balance'],2);	
							}?></td>
							<td width="40" id="customer_id_<?php echo $customer['id']; ?>" align="right" <?php echo $title . " " . $style; ?> title="<?php echo $title; ?>"><?php echo $text; ?></td>
					</tr>
				</table>
			</a>
		</td>
		<td align="center" class="pos" width="50" height="50">
			<a href="javascript:void(0)" height="100%" id="mail-selector-<?php echo $customer['id']?>" class="mail-selector"
	>
				<table border="0" style="min-height: 46px;">
					<tr>
						<td align="center" style="font-size: 18px;"><i id="img-mail-selector-<?php echo $customer['id']?>" class="fa fa-check img-mail-selector"></i></td>
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
	session_start();
	$_SESSION['SOAM_CACHING'] = (time() - 599);
	session_write_close();

?>
