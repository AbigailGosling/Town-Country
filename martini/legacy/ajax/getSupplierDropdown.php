<?php

	require(__DIR__.'/../functions.php');

	$name = request('searchterm');
	$species_id = request('species_id');
	
	$cutX = "SELECT * FROM `supplier` WHERE `name` LIKE ?";
	$cutY = prepareExecuteQuery($cutX,'s',['%'.$name.'%']);
	$count = mysqli_num_rows($cutY);
	
	if($count > 0){
		while($cutRow = mysqli_fetch_array($cutY)){
		?>
		<a href="javascript:;" class="intakeCutDropdown" onclick="setCut('<?php echo $cutRow['id']; ?>','<?php echo $cutRow['name']; ?>')"><?php echo $cutRow['name']; ?></a>
		<?php
		}
	}else{
		?>
		<a href="javascript:;" class="intakeCutDropdown" style="border: 1px #f00f00 solid;color:#f00f00;">You must select a vaild supplier!</a>
		<?php
	}
?>

<script type="text/javascript">
$(document).ready(function(){
	$('.speciesName').click(function(){
		$(this).next('.cutsContainer').toggle();
		console.log(1);
	});
});


function setCut(cut_id, text){
	console.log(cut_id);
	$('#supplier_search_results').fadeOut();
	$('#supplier_id').val(cut_id);
	$('#supplier_search').val(text);
}
</script>