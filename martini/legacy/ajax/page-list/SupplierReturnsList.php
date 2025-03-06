<?php
	require(__DIR__.'/../../functions.php');

    $name = request()->input('searchterm');
	$showBal = true;
	$rollingTotal = 0;
?>
	<div class="cutsContainer">
	<?php
	if($name != ''){

		$supplierQueryResult = prepareExecuteQuery("SELECT * FROM `supplier` WHERE `name` LIKE ? || `id` = ?",'ss',['%'.$name.'%',$name]);
	}else{
		$supplierQueryResult = prepareExecuteQuery("SELECT * FROM `supplier`");
	}
	$workingSet = [];
    $rollingTotal = 0;
	while($supplier = mysqli_fetch_assoc($supplierQueryResult)){
		$supplier['balance'] = 0;
        $supplierReturns = prepareExecuteQuery("SELECT * FROM `pickersheets` WHERE `customer_id`= ? AND `is_return_to_supplier` = 1","i",[$supplier['id']]);
        while($supplierReturn = mysqli_fetch_assoc($supplierReturns))
        {
            $returnProducts = prepareExecuteQuery("SELECT *,COUNT(product_id) as `count` FROM `pickeritems` WHERE `pickersheet_id` = ".$supplierReturn['id']);
            $quickWeightLookup = prepareExecuteQuery("SELECT GROUP_CONCAT(`weight_ids`) as `weight_ids` FROM `palletsout` WHERE `pickersheet_id` = ".$supplierReturn['id'])->fetch_assoc()['weight_ids'];

            while($returnProduct= mysqli_fetch_assoc($returnProducts))
            {
                if ($returnItem['unit']=="PPC")
                {
                    $itemCost = $returnProduct["price"] * $returnProduct["count"];
                }
                else
                {
                    $tear = prepareExecuteQuery("SELECT SUM(`weight_tear`) as `tear` FROM `weights` WHERE id IN (".$quickWeightLookup.") AND `product_id` = ".$returnProduct['product_id'])->fetch_assoc()['tear'];
                    $itemCost = $returnProduct["price"] * $tear;
                }
                $supplier['balance'] += $itemCost;
            }
            $amount = prepareExecuteQuery("SELECT SUM(`amount`) as `amount` FROM `invoice_payments` WHERE `invoice_id`= ".$supplierReturn['id'])->fetch_assoc()['amount'];
            $supplier['balance'] -= $amount;
        }
        $rollingTotal += $supplier['balance'];
		$workingSet[] = $supplier;
	}
    usort($workingSet, function ($item1, $item2) {
        return $item2['balance'] <=> $item1['balance'];
    });
	foreach($workingSet as $supplier)
	{
	?>
	<table width="100%">
		<tr><td align="center" class="pos">
			<a href="supplier_return_statement.php?id=<?php echo $supplier['id']; ?>" class="intake">
				<table width="100%" border="0">
					<tr>
						<td width="100" align="left">ID: <?php echo $supplier['id']; ?></td>
						<td align="center" style="font-size: 18px;"><?php echo $supplier['name']; ?></td>
						<td width="100" align="right">
							<?php if ($showBal) {
								if ($supplier['balNeg'] == true) echo "-";
								echo "£".number_format($supplier['balance'],2);
							}?></td>
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
	</div>
