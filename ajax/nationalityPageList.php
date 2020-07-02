<?php

	require('../functions.php');

	$name = $_POST['searchterm'];
	
	if($name != '' && strlen($name) > 1){
	
	 ?>
		<div class="cutsContainer">
		<?php
		
		$cutX = "SELECT * FROM `nationality` WHERE name LIKE '%$name%'";
		$cutY = mysqli_query($conn, $cutX);
		
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
			</td></tr>
		</table>
		<?php
		}
		?></div><?php
		
	}else{
		?>
		<div class="cutsContainer">
		<?php
		
		$cutX = "SELECT * FROM `nationality`";
		$cutY = mysqli_query($conn, $cutX);
		
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