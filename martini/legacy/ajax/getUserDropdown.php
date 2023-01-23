<?php

	require(__DIR__.'/../functions.php');

	$name = request('searchterm');
	
	$cutX = "SELECT * FROM `users`";
	$cutY = prepareExecuteQuery($cutX);
	
	while($cutRow = mysqli_fetch_array($cutY)){
	?>
	<a href="javascript:;" class="intakeCutDropdown" onclick="setCut('<?php echo $cutRow['id']; ?>','<?php echo $cutRow['name']; ?>')"><?php echo $cutRow['name']; ?></a>
	<?php
	}
?>

<script type="text/javascript">
$(document).ready(function(){
	$('.speciesName').click(function(){
		$(this).next('.cutsContainer').toggle();
	});
});


function setCut(cut_id, text){
	console.log(cut_id);
	$('#supplier_search_results').fadeOut();
	$('#supplier_id').val(cut_id);
	$('#supplier_search').val(text);
}
</script>