<?php
	include('functions.php');
?>

<!doctype html>

<html class="int">

<head>

	<meta charset="utf-8">

	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>Town &amp; Country</title>

	<link href="css/style.css" rel="stylesheet" type="text/css">



	<link href="css/font-awesome.css" rel="stylesheet" type="text/css">
	<link href="css/lity.css" rel="stylesheet" type="text/css">

	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

	<script src="https://code.jquery.com/jquery-1.12.4.js"></script><script src="https://malsup.github.io/jquery.form.js"></script> 
	<script src="js/lity.js"></script>

	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script>

		$(function(){

			$("#datepicker").datepicker();

		});

	</script>

</head>

<body class="menu">

<div id="top">

	<a href="menu.php" id="menu">MENU</a>

	<a href="logout" id="logout">LOGOUT</a>

</div>

<main>
	
	<div id="intakelist">

		<?php

			if(request()->input('id') != ''){

				

				$id = request()->input('id');

				

				$x = "SELECT * FROM cuts WHERE id = ?";

				$yy = prepareExecuteQuery($x,'i',[$id]);

				

				$data = mysqli_fetch_array($yy);

			}

		?>

		<form id="mainForm" class="container" method="POST" action="<?php if(request()->input('id') != ''){ echo 'scripts/updateCut.php'; } else { echo 'scripts/addCut.php'; } ?>">
		<input type="hidden" name="_token" value="<?php echo csrf_token();?>">
		<table width="100%" border="0" cellpadding="0" cellspacing="0">

			<tr><td align="center"><h1 class="int"><?php if(request()->input('id') != ''){ echo 'UPDATE'; } else { echo 'ADD'; } ?> CUTS</h1></td></tr>

			<tr>
				<td>
					<label>Species</label>

					<select name="species_id" id="SearchSpecies">
					<option value="" disabled selected>Select species..</option>
					
                    <?php
                    	$x = "SELECT * FROM species";
                        $y = prepareExecuteQuery($x);
                        
						while($row = mysqli_fetch_array($y)){
                        ?>
                        <option value="<?php echo $row['id']; ?>" <?php if($row['id'] == $data['species_id']){ echo 'selected'; } ?>><?php echo $row['name']; ?></option>
                        <?php
						}
					?>
					</select>
				</td>
			</tr>
            <tr>
				<td>
					<label>Cut Group</label>
					<select name="cutgroup_id" id="SearchCutgroups">
						<option sid="<?php echo $rand; ?>" class="header" value="<?php echo $rand; ?>">Select cut..</option>
                        <?php
                            $x = "SELECT * FROM `cutgroups`";
                            $y = prepareExecuteQuery($x);
                            
                            $i=0;
                            while($row = mysqli_fetch_array($y)){
                                
                                
                                $thisid = $row['species_id'];
                                $y2 = prepareExecuteQuery("SELECT * FROM species WHERE id=?",'i',[$thisid]);
                                $species = mysqli_fetch_array($y2);
                                $rand = 'z' . rand(6000,12212);
                                    ?><option style="display:none;" sid="<?php echo $row['id']; ?>" class="allsoption s<?php echo $species['id']; ?>" value="<?php echo $row['id']; ?>"<?php if($data['cutgroup_id'] == $row['id']){ echo 'selected'; } ?>><?php echo $row['name']; ?></option><?php
                                }
                        ?>
					</select>
				</td>
			</tr>
			<tr>

				<td>

					<label>Name</label>

					<input type="text" name="id" value="<?php echo $data['id']; ?>" style="display:none;">

					<input type="text" name="name" value="<?php echo $data['name']; ?>">

				</td>

			</tr>
			<tr>

				<td>

					<label>Warning Threshold (in days)</label>

					<input type="text" name="warning" onkeypress="validate(event)" value="<?php echo $data['warning']; ?>">

				</td>

				</tr>

				<tr>

				<td>

					<label>Danger Threshold (in days)</label>

					<input type="text" name="danger" onkeypress="validate(event)" value="<?php echo $data['danger']; ?>">

				</td>

			</tr>

			<tr>

				<td>

					<?php

					if(request()->input('id') != ''){

					?><input type="submit" value="Update Cut"><?php

					}else{

					?><input type="submit" value="Add Cut"><?php

					}

					?>

				</td>

			</tr>
					
		</table>

		</form>

		<h1 class="int">Cut LIST</h1>

			<input type="text" id="instantSearch" placeholder="Search.." style="width:260px;height:28px;padding-left:10px;" enterkeyhint="go">

			<div id="cutAjax">

			<?php
                

				$speciesX = "SELECT * FROM `species`";
				$speciesY = prepareExecuteQuery($speciesX);

				while($speciesRow = mysqli_fetch_array($speciesY)){
 
					$speciesID = $speciesRow['id']; 
                    
                    $cutsArray = Array();
                    $cutX = "SELECT * FROM `cuts` WHERE species_id = ? ORDER BY name ASC";
                    $cutY = prepareExecuteQuery($cutX,'i',[$speciesID]);

                    while($cutRow = mysqli_fetch_array($cutY)){ array_push($cutsArray, $cutRow); }
                    ?>

					<table width="100%" class="speciesName" onclick="loadCutsForSpecies(<?php echo $speciesID; ?>);">
						<tr><td align="left" class="pos">
							<h2 style="color:#FFF;margin-bottom:0px;border-bottom:1px dashed #FFF;cursor:pointer;padding-bottom:5px;"><?php echo $speciesRow['name']; ?></h2>
						</td></tr>
					</table>

					<div class="cutsContainer group<?php echo $speciesID; ?>" style="display:none;">
					 
					</div>
					<?php

				}

			?>

			</div>

	</div>

</main>

<script type="text/javascript">

	function validate(evt) {
  var theEvent = evt || window.event;

  // Handle paste
  if (theEvent.type === 'paste') {
      key = event.clipboardData.getData('text/plain');
  } else {
  // Handle key press
      var key = theEvent.keyCode || theEvent.which;
      key = String.fromCharCode(key);
  }
  var regex = /[0-9]|\./;
  if( !regex.test(key) ) {
    theEvent.returnValue = false;
    if(theEvent.preventDefault) theEvent.preventDefault();
  }
}

	$(document).ready(function(){

    $('#SearchCutgroups option.s'+$('#SearchSpecies').first().val()).show();
	
	function toggleOptions(){

		var thisval = $("#SearchSpecies").val();
		var selectedCG = $("#SearchCutgroups").val();
        $('#SearchCutgroups option.allsoption').hide();
        $('#SearchCutgroups option.header').show();
        $('#SearchCutgroups option.s'+thisval).show();
        $('#SearchCutgroups').val($('#SearchCutgroups option.header').first().attr('sid'));

		// iOS fix - display:none doesn't work on select options
		$('#SearchCutgroups option.allsoption').each(function(index,el ){
			if($(el).parent().is('span')){
				$(el).unwrap('span');
			}
		})
	
		$('#SearchCutgroups option.allsoption').wrap('<span/>');
		$('#SearchCutgroups option.s'+thisval).unwrap();
		$('#SearchCutgroups option.s'+thisval+"[selected]").attr('selected','selected');
		$("#SearchCutgroups").val(selectedCG);		
        var id = $("#SearchSpecies").val();
	}
	$('#SearchSpecies').change(toggleOptions);
	

	toggleOptions();
		

		$('#instantSearch').keydown(function(){

			var val = $('#instantSearch').val();

			// $('#test2d').text(val);

			console.log(val);

			

				var xhttp = new XMLHttpRequest();

				xhttp.onreadystatechange = function() {

				if (this.readyState == 4 && this.status == 200) {

				  // document.getElementById("demo").innerHTML = this.responseText;

				  $('#cutAjax').html(this.responseText);

				}

				};

				xhttp.open("POST", "ajax/cutPageList.php", true);

				xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				xhttp.setRequestHeader('X-CSRF-TOKEN', "<?php echo csrf_token();?>");
				xhttp.send("searchterm=" + val);

			

		});
	});

	function loadCutsForSpecies(id){
		$('.cutsContainer').hide();
		$('.group' + id).toggle();

		var xhttp = new XMLHttpRequest();

		xhttp.onreadystatechange = function() {

			if(this.readyState == 4 && this.status == 200){
				$('.group' + id).html(this.responseText);
 			}

		};

		xhttp.open("POST", "ajax/cutsPageSpeciesDropdown.php", true);
		xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xhttp.setRequestHeader('X-CSRF-TOKEN', "<?php echo csrf_token();?>");
		xhttp.send("species_id=" + id);
	}
	

	function deleteRow(id){

		if(confirm('Are you sure you want to delete this?')){

			window.location.href = "/scripts/deleteCut.php?id=" + id;

			// console.log(id);

		}

	}

	

</script>

<div id="btm"></div>
<div style="visibility: hidden;"><i class="fa fa-pencil" style="padding-right:0px;" aria-hidden="true"></i><i class="fa fa-times" aria-hidden="true"></i></div>
</body>

</html>