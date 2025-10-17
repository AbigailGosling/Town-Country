<?php

	require(__DIR__.'/../functions.php');

	$name = request()->input('searchterm');
    $showDisabled = (request()->input('showDisabled',0)==1);

	if($name != '' && strlen($name) > 1){

		?>
		<div class="cutsContainer">
		<?php

		$customerQueryResult = fuzzyCustomerSearch($name,true,$showDisabled);

		while($customer = mysqli_fetch_array($customerQueryResult)){

			$customer_id = $customer['id'];
			$resultsCheckPicksheets = prepareExecuteQuery("SELECT id FROM pickerSheets WHERE customer_id=?",'i',[$customer_id]);

			$existingPicksheetsCount = mysqli_num_rows($resultsCheckPicksheets);
		?>
		<table width="100%">
			<tr><td align="center" class="pos">
				 <a href="manageCustomers.php?id=<?php echo $customer['id']; ?>" class="intake">
						<table width="100%" border="0">
							<tr>
								<td width="100" align="left">ID: <?php echo $customer['id']; ?></td>
								<td align="center" style="font-size: 18px;"><?php echo $customer['businessname']; ?></td>
								<td width="100" align="right"></td>
							</tr>
						</table>
					</a>
					<!--<a href="manageCustomers.php?id=<?php //echo $customer['id']; ?>"  <?php //if($user['user_type'] == 'A'){ ?> style="right:-35px;" <?php //} ?>id="delete_intake"><i class="fa fa-pencil"style="padding-right:0px;" aria-hidden="true"></i></a>-->
					<a href="javascript:;" onclick="deleteRow(<?php echo $customer['id']; ?>,<?php echo $existingPicksheetsCount; ?>)" style="right:-70px;height:40px;padding-top:6px;" id="delete_intake"><i class="fa fa-trash" style="padding-right:5px;" aria-hidden="true"></i></a>
			</td></tr>
		</table>
		<?php
		}
		?></div><?php

	}else{
		?>
		<div class="cutsContainer">
		<?php

		$customerQueryResult = prepareExecuteQuery("SELECT * FROM `customers`");

		while($customer = mysqli_fetch_array($customerQueryResult)){

			$customer_id = $customer['id'];
			$resultsCheckPicksheets = prepareExecuteQuery("SELECT id FROM pickerSheets WHERE customer_id=?",'i',[$customer_id]);

			$existingPicksheetsCount = mysqli_num_rows($resultsCheckPicksheets);
		?>
		<table width="100%">
			<tr><td align="center" class="pos">
				<a href="javascript:;" class="intake">
						<table width="100%" border="0">
							<tr>
								<td width="100" align="left">ID: <?php echo $customer['id']; ?></td>
								<td align="center" style="font-size: 18px;"><?php echo $customer['businessname']; ?></td>
								<td width="100" align="right"></td>
							</tr>
						</table>
					</a>
					<a href="manageCustomers.php?id=<?php echo $customer['id']; ?>"  <?php if($user['user_type'] == 'A'){ ?> style="right:-35px;" <?php } ?>id="delete_intake"><i class="fa fa-pencil"style="padding-right:0px;" aria-hidden="true"></i></a>
					<a href="javascript:;" onclick="deleteRow(<?php echo $customer['id']; ?>,<?php echo $existingPicksheetsCount; ?>)" style="right:-70px;height:40px;padding-top:6px;" id="delete_intake"><i class="fa fa-trash" style="padding-right:5px;" aria-hidden="true"></i></a>
			</td></tr>
		</table>
		<?php
		}
		?></div><?php
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
