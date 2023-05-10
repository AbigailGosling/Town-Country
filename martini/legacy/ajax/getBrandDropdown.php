<?php
	require(__DIR__.'/../functions.php');
	$name = request()->input('searchterm');
	$cutX = "SELECT * FROM `brands` WHERE `name` LIKE ?";
	$cutY = prepareExecuteQuery($cutX,'s',['%'.$name.'%']);
	$count = mysqli_num_rows($cutY);
	if($count > 0){
	$i=0;
	while($cutRow = mysqli_fetch_array($cutY)){
	$i++;
	?>
	<a href="javascript:;" class="intakeCutDropdown" id="brand_<?php echo $i; ?>" onclick="setBrand('<?php echo $cutRow['id']; ?>','<?php echo $cutRow['name']; ?>')"><?php echo $cutRow['name']; ?></a>
	<?php
	}
	?>
	<script type="text/javascript">
		$(document).ready(function(){
			// $('.speciesName').click(function(){
				// $(this).next('.cutsContainer').toggle();
				// console.log(1);
			// });
		});
		function setBrand(cut_id, text){
			console.log(cut_id);
			$('#brand_search_results').fadeOut();
			$('#brand_id').val(cut_id);
			$('#brand_search').val(text);
		}
	</script>
	<?php
		if ($count == 1 && strlen($name) > 3){
		?>
		<script type="text/javascript">
			$('#brand_1').click();
		</script>
		<?php
		}
	}else{
	?>
	<a href="javascript:;" class="intakeCutDropdown" style="border: 1px #f00f00 solid;color:#f00f00;">You must select a vaild cut!</a>
	<?php
	}
?>
