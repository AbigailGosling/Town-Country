<?php

	require(__DIR__.'/../functions.php');

	$name = request()->input('searchterm');

	if($name != '' && strlen($name) > 1){
		$speciesX = "SELECT * FROM `species`";
		$speciesY = prepareExecuteQuery($speciesX);

		while($speciesRow = mysqli_fetch_array($speciesY)){
			$speciesID = $speciesRow['id'];


			$cutXtemp = "SELECT * FROM `cuts` WHERE `disabled` = 0 AND `species_id` = ? AND `name` LIKE ?";
			$cutYtemp = prepareExecuteQuery($cutXtemp,'is',[$speciesID,'%'.$name.'%']);

			if(mysqli_num_rows($cutYtemp) > 0){
			?>
			<table width="100%" class="speciesName">
				<tr><td align="left" class="pos">
					<h2 style="color:#FFF;margin-bottom:0px;border-bottom:1px dashed #FFF;cursor:pointer;padding-bottom:5px;"><?php echo $speciesRow['name']; ?></h2>
				</td></tr>
			</table>
			<?php } ?>
			<div class="cutsContainer">
			<?php

			while($cutRow = mysqli_fetch_array($cutYtemp)){
			?>
			<table width="100%">
				<tr><td align="center" class="pos">
					<a href="#" class="intake"><?php echo $cutRow['name']; ?></a>
					<a href="manageCuts.php?id=<?php echo $cutRow['id']; ?>"  <?php if($user['user_type'] == 'A'){ ?> style="right:35px;" <?php } ?> id="delete_intake"><i class="fa fa-pencil" aria-hidden="true"></i></a>
					<?php if($user['user_type'] == 'A'){ ?>
					<a href="scripts/deleteCut.php?id=<?php echo $cutRow['id']; ?>" id="delete_intake"><i class="fa fa-times" aria-hidden="true"></i></a>
					<?php } ?>
				</td></tr>
			</table>
			<?php
			}
			?></div><?php
		}

	}else{
		$speciesX = "SELECT * FROM `species`";
		$speciesY = prepareExecuteQuery($speciesX);
		while($speciesRow = mysqli_fetch_array($speciesY)){
			$speciesID = $speciesRow['id'];
			?>
			<table width="100%" class="speciesName">
				<tr><td align="left" class="pos">
					<h2 style="color:#FFF;margin-bottom:0px;border-bottom:1px dashed #FFF;cursor:pointer;padding-bottom:5px;"><?php echo $speciesRow['name']; ?></h2>
				</td></tr>
			</table>
			<div class="cutsContainer" style="display:none;">
			<?php
			$cutX = "SELECT * FROM `cuts` WHERE `disabled` = 0 AND `species_id` = ?";
			$cutY = prepareExecuteQuery($cutX,'i',[$speciesID]);

			while($cutRow = mysqli_fetch_array($cutY)){
			?>
			<table width="100%">
				<tr><td align="center" class="pos">

					<a href="#" class="intake"><?php echo $cutRow['name']; ?></a>
					<a href="manageCuts.php?id=<?php echo $cutRow['id']; ?>"  <?php if($user['user_type'] == 'A'){ ?> style="right:-35px;" <?php } ?> id="delete_intake"><i class="fa fa-pencil" aria-hidden="true"></i></a>
					<?php if($user['user_type'] == 'A'){ ?>
					<a href="scripts/deleteCut.php?id=<?php echo $cutRow['id']; ?>" id="delete_intake" style="right:-75px;"><i class="fa fa-times" aria-hidden="true"></i></a>
					<?php } ?>
				</td></tr>
			</table>
			<?php
			}
			?></div><?php
		}
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
