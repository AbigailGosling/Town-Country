<?php

	require('../../functions.php');
    require('../../scripts/SLabsEmailer.php');
	require_once('../customer_soa_results_function.php');
    use InternalScripts\SLabsEmailerStatus;
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
	$title = "";
	$style = "";
	if (isset($_POST['history']))
	{
		$historyQuery = mysqli_query($conn, "SELECT * FROM `mail_tracking` WHERE customer_id = ".$customer['id']." AND `type` = 'STATEMENT'  ORDER BY `mail_tracking`.`id` DESC");
		if (mysqli_num_rows($historyQuery) > 0)
		{
			$history = null;
            while ($tmpD = mysqli_fetch_assoc($historyQuery))
            {
                $history = $tmpD;
                if (strpos($tmpD['addressee'],"townandcountrymeats.co.uk") == -1) break;
            }
			$style = 'style="background-color:'.SLabsEmailerStatus::getTrafficStatus($history['status'],$history['secondary_code']).'"';
			$title = 'title="'.SLabsEmailerStatus::getTextStatus($history['status'],$history['secondary_code']).'"';
		}
	}
	else
	{
		$creditCheck = precredit_check($customer['id']);
		echo "<script>console.log(".json_encode($creditCheck).");</script>";
		if ($creditCheck['saleAllowed'] == true)
		{
			if ($creditCheck['showWarning'] == true)
			{
				$style = 'style="background-color:orange"';
			}
			else
			{
				$style = 'style="background-color:green"';
			}
		}
		else
		{
			$style = 'style="background-color:red"';
		}
	}
?>
<table width="100%">
	<tr><td align="center" class="pos">
		<?php if (isset($_POST['history'])) {?><a href="/history_stms.php?id=<?php echo $customer['id']; ?>" class="intake"><?php }
										else{?><a href="/customer_soa.php?id=<?php echo $customer['id']; ?>" class="intake"><?php } ?>			
			<table width="100%" border="0">
				<tr>
					<td width="100" align="left">ID: <?php echo $customer['id']; ?></td>
					<td align="center" style="font-size: 18px;"><?php echo $customer['businessname']; ?></td>
					<td width="40" id="customer_id_<?php echo $customer['id']; ?>" align="right" <?php echo $title . " " . $style; ?>></td>
				</tr>
			</table>
		</a>
	</td></tr>
</table>
<?php
}
?></div>
