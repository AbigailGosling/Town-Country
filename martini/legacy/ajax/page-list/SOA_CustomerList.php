<?php

	require(__DIR__.'/../../functions.php');
    require(__DIR__.'/../../scripts/SLabsEmailer.php');
	require_once('../customer_soa_results_function.php');
    use InternalScripts\SLabsEmailerStatus;
    $name = request('searchterm');
?>
<div class="cutsContainer">
<?php
if($name != ''){

	$customerQueryResult = prepareExecuteQuery("SELECT * FROM `customers` WHERE businessname LIKE ? || id = ?",'ss',['%'.$name.'%',$name]);
}else{
	$customerQueryResult = prepareExecuteQuery("SELECT * FROM `customers` WHERE `disabled`=0");
}				
while($customer = mysqli_fetch_array($customerQueryResult)){
	$title = "";
	$style = "";
	if (isset(request('history')))
	{
		$historyQuery = prepareExecuteQuery("SELECT * FROM `mail_tracking` WHERE customer_id = ? AND `type` = 'STATEMENT'  ORDER BY `mail_tracking`.`id` DESC",'i',[$customer['id']]);
		if (mysqli_num_rows($historyQuery) > 0)
		{
			$history = null;
            while ($tmpD = mysqli_fetch_assoc($historyQuery))
            {
                $history = $tmpD;
                if (strpos($tmpD['addressee'],"townandcountrymeats.co.uk") === FALSE) break;
            }
			$style = 'style="background-color:'.SLabsEmailerStatus::getTrafficStatus($history['status']).'"';
			$title = 'title="'.SLabsEmailerStatus::getTextStatus($history['status'],$history['secondary_code']).'"';
		}
	}
	else
	{
		$creditCheck = precredit_check($customer['id']);
		$title = "";
		if ($creditCheck['saleAllowed'] == true)
		{
			if ($creditCheck['showWarning'] == true)
			{
				$style = 'style="background-color:orange"';
				$title = $creditCheck['message'];
			}
			else
			{
				$style = 'style="background-color:green"';
				$title = "";
			}
		}
		else
		{
			$style = 'style="background-color:red"';
			$title = $creditCheck['message'];
		}
	}
?>
<table width="100%">
	<tr><td align="center" class="pos">
		<?php if (isset(request('history'])) {?><a href="history_stms.php?id=<?php echo $customer['id'); ?>" class="intake"><?php }
										else{?><a href="customer_soa.php?id=<?php echo $customer['id']; ?>" class="intake"><?php } ?>			
			<table width="100%" border="0">
				<tr>
					<td width="100" align="left">ID: <?php echo $customer['id']; ?></td>
					<td align="center" style="font-size: 18px;"><?php echo $customer['businessname']; ?></td>
					<td width="40" id="customer_id_<?php echo $customer['id']; ?>" align="right" <?php echo $title . " " . $style; ?> title="<?php echo $title; ?>"></td>
				</tr>
			</table>
		</a>
	</td></tr>
</table>
<?php
}
?></div>
