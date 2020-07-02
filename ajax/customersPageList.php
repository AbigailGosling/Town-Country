<?php

	require('../functions.php');

	$name = $_POST['searchterm'];
	
	if($name != '' && strlen($name) > 1){
	
		?>
		<div class="cutsContainer">
		<?php
		
		$cutX = "SELECT * FROM `customers` WHERE businessname LIKE '%$name%'";
		$cutY = mysqli_query($conn, $cutX);
		
		while($cutRow = mysqli_fetch_array($cutY)){
		?>
		<table width="100%">
			<tr><td align="center" class="pos">
				 <a href="javascript:;" class="intake">
						<table width="100%" border="0">
							<tr>
								<td width="100" align="left">ID: <?php echo $cutRow['id']; ?></td>
								<td align="center" style="font-size: 18px;"><?php echo $cutRow['businessname']; ?></td>
								<td width="100" align="right"></td>
							</tr>
						</table>
					</a>
					<a href="/manageCustomers.php?id=<?php echo $cutRow['id']; ?>"  <?php if($user['user_type'] == 'A'){ ?> style="right:-35px;" <?php } ?>id="delete_intake"><i class="fa fa-pencil"style="padding-right:0px;" aria-hidden="true"></i></a>
					<a href="javascript:;" onclick="deleteRow(<?php echo $row['id']; ?>)" style="right:-70px;height:40px;padding-top:6px;" id="delete_intake"><i class="fa fa-trash" style="padding-right:5px;" aria-hidden="true"></i></a>
			</td></tr>
		</table>
		<?php
		}
		?></div><?php
		
	}else{
		?>
		<div class="cutsContainer">
		<?php
		
		$cutX = "SELECT * FROM `customers`";
		$cutY = mysqli_query($conn, $cutX);
		
		while($cutRow = mysqli_fetch_array($cutY)){
		?>
		<table width="100%">
			<tr><td align="center" class="pos">
				<a href="javascript:;" class="intake">
						<table width="100%" border="0">
							<tr>
								<td width="100" align="left">ID: <?php echo $cutRow['id']; ?></td>
								<td align="center" style="font-size: 18px;"><?php echo $cutRow['businessname']; ?></td>
								<td width="100" align="right"></td>
							</tr>
						</table>
					</a>
					<a href="/manageCustomers.php?id=<?php echo $cutRow['id']; ?>"  <?php if($user['user_type'] == 'A'){ ?> style="right:-35px;" <?php } ?>id="delete_intake"><i class="fa fa-pencil"style="padding-right:0px;" aria-hidden="true"></i></a>
					<a href="javascript:;" onclick="deleteRow(<?php echo $row['id']; ?>)" style="right:-70px;height:40px;padding-top:6px;" id="delete_intake"><i class="fa fa-trash" style="padding-right:5px;" aria-hidden="true"></i></a>
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