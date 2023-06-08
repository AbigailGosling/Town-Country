<?php

	require(__DIR__.'/../functions.php');

	$name = request()->input('searchterm');
	
	if($name != '' && strlen($name) > 1){
	
		?>
		<div class="cutsContainer">
		<?php
		
		$cutX = "SELECT * FROM `brands` WHERE `name` LIKE ?";
		$cutY = prepareExecuteQuery($cutX,'s',['%'.$name.'%']);
		
		while($cutRow = mysqli_fetch_array($cutY)){
		?>
		<table width="100%">
			<tr><td align="center" class="pos">
				 <a href="javascript:;" class="intake">
						<table width="100%" border="0">
							<tr>
								<td width="100" align="left">ID: <?php echo $cutRow['id']; ?></td>
								<td align="center" style="font-size: 18px;"><?php echo $cutRow['name']; ?></td>
								<td width="100" align="right"></td>
							</tr>
						</table>
					</a>
					<a href="manageBrands.php?id=<?php echo $cutRow['id']; ?>"  <?php if($user['user_type'] == 'A'){ ?> style="right:-35px;" <?php } ?>id="delete_intake"><i class="fa fa-pencil"style="padding-right:0px;" aria-hidden="true"></i></a>
			</td></tr>
		</table>
		<?php
		}
		?></div><?php
		
	}else{
		?>
		<div class="cutsContainer">
		<?php
		
		$cutX = "SELECT * FROM `brands`";
		$cutY = prepareExecuteQuery($cutX);
		
		while($cutRow = mysqli_fetch_array($cutY)){
		?>
		<table width="100%">
			<tr><td align="center" class="pos">
				<a href="javascript:;" class="intake">
						<table width="100%" border="0">
							<tr>
								<td width="100" align="left">ID: <?php echo $cutRow['id']; ?></td>
								<td align="center" style="font-size: 18px;"><?php echo $cutRow['name']; ?></td>
								<td width="100" align="right"></td>
							</tr>
						</table>
					</a>
 
					<a href="manageBrands.php?id=<?php echo $cutRow['id']; ?>"  <?php if($user['user_type'] == 'A'){ ?> style="right:-35px;" <?php } ?>id="delete_intake"><i class="fa fa-pencil"style="padding-right:0px;" aria-hidden="true"></i></a>
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