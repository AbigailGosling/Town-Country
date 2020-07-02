<?php
	require('../functions.php');
	$name = $_POST['searchterm'];
	$species_id = $_POST['species_id'];
	// $cutXtemp = "SELECT * FROM `cuts` WHERE species_id = '$speciesID' AND name LIKE '%$name%'";
	// $cutYtemp = mysqli_query($conn, $cutXtemp);
	$cutX = "SELECT * FROM `cuts` WHERE name LIKE '%$name%' && species_id = '$species_id'";
	$cutY = mysqli_query($conn, $cutX);
	$count = mysqli_num_rows($cutY);
	if($count > 0){
	$i=0;
	while($cutRow = mysqli_fetch_array($cutY)){
	$i++;
	$thisid = $cutRow['id'];
	$thisname = $cutRow['name'];
	?>
	<a href="javascript:;" class="intakeCutDropdown" id="cut_<?php echo $i; ?>" onclick="setCut('<?php echo $thisid; ?>','<?php echo str_replace("'","\'",$thisname); ?>')"><?php echo $cutRow['name']; ?></a>
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
			$('#cut_search_results').fadeOut();
			$('#cut_id').val(cut_id);
			$('#cut_search').val(text);
		}
	</script>
	<?php
		if ($count == 1 && strlen($name) > 3){
		?>
		<script type="text/javascript">
			$('#cut_1').click();
		</script>
		<?php
		}
	}else{
	?>
	<a href="javascript:;" class="intakeCutDropdown" style="border: 1px #f00f00 solid;color:#f00f00;">You must select a vaild cut!</a>
	<?php
	}
?>
