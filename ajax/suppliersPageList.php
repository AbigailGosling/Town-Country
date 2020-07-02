<?php

	require('../functions.php');

	$name = $_POST['searchterm'];
	
	if($name != '' && strlen($name) > 1){
	
		?>
		<div class="cutsContainer">
		<?php
		
		$cutX = "SELECT * FROM `supplier` WHERE name LIKE '%$name%' ORDER BY name ASC";
		$cutY = mysqli_query($conn, $cutX);
		
		while($cutRow = mysqli_fetch_array($cutY)){
		?>
		<table width="100%">
			<tr><td align="center" class="pos">
				<a href="intake.php?id=<?php echo $cutRow['id']; ?>" class="intake" style="margin-top:0px;">
					<table width="100%" border="0">
						<tr>
							<td width="100" align="left">ID: <?php echo $cutRow['id']; ?></td>
							<td align="center" style="font-size: 18px;"><?php echo $cutRow['name']; ?></td>
							<td width="100" align="right">
								<a href="/manageSuppliers.php?id=<?php echo $cutRow['id']; ?>" style="right:-35px;height:40px;padding-top:6px;top:0px;" id="delete_intake"><i class="fa fa-pencil" style="padding-right:4px;" aria-hidden="true"></i></a>
							</td>
						</tr>
					</table>
				</a>
   
			</td></tr>
		</table>
		<?php
		}
		?></div><?php
		
	}else{
		?>
		<div class="cutsContainer">
		<?php
		
		$cutX = "SELECT * FROM `supplier` ORDER BY name ASC";
		$cutY = mysqli_query($conn, $cutX);
		
		while($cutRow = mysqli_fetch_array($cutY)){
		?>
		<table width="100%">
			<tr><td align="center" class="pos">
				<a href="intake.php?id=<?php echo $cutRow['id']; ?>" class="intake" style="margin-top:0px;">
					<table width="100%" border="0">
						<tr>
							<td width="100" align="left">ID: <?php echo $cutRow['id']; ?></td>
							<td align="center" style="font-size: 18px;"><?php echo $cutRow['name']; ?></td>
							<td width="100" align="right">
								<a href="/manageSuppliers.php?id=<?php echo $cutRow['id']; ?>" style="right:-35px;height:40px;padding-top:6px;top:0px;" id="delete_intake"><i class="fa fa-pencil" style="padding-right:4px;" aria-hidden="true"></i></a>
							</td>
						</tr>
					</table>
				</a>
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