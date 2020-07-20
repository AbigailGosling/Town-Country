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

	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
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

	<a href="logout.php" id="logout">LOGOUT</a>

</div>

<main>

	<div id="intakelist">

		<?php

			if($_GET['id'] != ''){

				

				$id = $_GET['id'];

				

				$x = "SELECT * FROM cuts WHERE id = '$id'";

				$yy = mysqli_query($conn, $x);

				

				$data = mysqli_fetch_array($yy);

			}

		?>

		<form class="container" method="POST" action="<?php if($_GET['id'] != ''){ echo '/scripts/updateCut.php'; } else { echo '/scripts/addCut.php'; } ?>">

		<table width="100%" border="0" cellpadding="0" cellspacing="0">

			<tr><td align="center"><h1 class="int"><?php if($_GET['id'] != ''){ echo 'UPDATE'; } else { echo 'ADD'; } ?> CUTS</h1></td></tr>

			<tr>
				<td>
					<label>Species</label>

					<select name="species_id" id="SearchSpecies">
                    <?php
                    	$x = "SELECT * FROM species";
                        $y = mysqli_query($conn, $x);
                        
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
                        <option sid="<?php echo $rand; ?>" class="header" value="<?php echo $rand; ?>" selected>Select cut..</option>
                        <?php
                            $x = "SELECT * FROM `cutgroups`";
                            $y = mysqli_query($conn, $x);
                            
                            $i=0;
                            while($row = mysqli_fetch_array($y)){
                                
                                
                                $thisid = $row['species_id'];
                                $y2 = mysqli_query($conn,"SELECT * FROM species WHERE id='$thisid'");
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

					<?php

					if($_GET['id'] != ''){

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

			<input type="text" id="instantSearch" placeholder="Search.." style="width:260px;height:28px;padding-left:10px;">

			<div id="cutAjax">

			<?php
                

				$speciesX = "SELECT * FROM `species`";
				$speciesY = mysqli_query($conn, $speciesX);

				while($speciesRow = mysqli_fetch_array($speciesY)){
 
					$speciesID = $speciesRow['id']; 
                    
                    $cutsArray = Array();
                    $cutX = "SELECT * FROM `cuts` WHERE species_id = '$speciesID' ORDER BY name ASC";
                    $cutY = mysqli_query($conn, $cutX);

                    while($cutRow = mysqli_fetch_array($cutY)){ array_push($cutsArray, $cutRow); }
                    ?>

					<table width="100%" class="speciesName">
						<tr><td align="left" class="pos">
							<h2 style="color:#FFF;margin-bottom:0px;border-bottom:1px dashed #FFF;cursor:pointer;padding-bottom:5px;"><?php echo $speciesRow['name']; ?></h2>
						</td></tr>
					</table>

					<div class="cutsContainer" style="display:none;">
					<?php
                        
 
					    $cutX = "SELECT * FROM `cuts` WHERE species_id = '$speciesID' ORDER by name ASC";
					    $cutY = mysqli_query($conn, $cutX);

					    while($cutRow = mysqli_fetch_array($cutY)){
                        
                        /*
                        $cutid = $cutRow['id'];
                        $y = mysqli_query($conn, "SELECT id from `product` WHERE cut_id='$cutid'");
                        $count = mysqli_num_rows($y);
                        */
                        
					?>

					<table style="width: 100%;display: table;">

						<tr><td align="center" class="pos">

					

							<a href="javascript:;" class="intake" <?php if($cutRow['cutgroup_id'] == ''){ echo 'style="border:2px solid red;"'; } ?>>&nbsp;<?php echo $cutRow['name']; ?></a>

							<a href="/manageCuts.php?id=<?php echo $cutRow['id']; ?>" style="right:-35px;height: 29px;" id="delete_intake"><i class="fa fa-pencil" style="padding-right:0px;" aria-hidden="true"></i></a>

							<?php if($user['user_type'] == 'A'){ ?>
							    <a href="#deletePopup<?php echo $cutRow['id']; ?>" id="delete_intake" style="right:-75px;" data-lity><i class="fa fa-times" aria-hidden="true"></i></a>
							<?php } ?>
						</td></tr>

					</table>
					<div id="deletePopup<?php echo $cutRow['id']; ?>" class="lity-hide" style="background:#fff;padding:20px;text-align:center;max-width:490px;">
                        <h2>Confirm</h2>
                        <p>Where would you like to reassign the existing products?</p>

                        <form method="POST" action="scripts/reassignproductcuts.php">
                            <input type="hidden" value="<?php echo $cutRow['id']; ?>" name="before_cutid">
                            <select style="width:100%;height:35px;" name="after_cutid" required>
                                <option value="" disabled selected>Please select a cut..</option>
                                <?php
                                    foreach ($cutsArray as $cut) {
                                        if($cut['id'] != $cutRow['id']){
                                            ?><option value="<?php echo $cut['id']; ?>"><?php echo $cut['name']; ?></option><?php        
                                        }
                                    }
                                ?>
                            </select>

                            <input type="submit" value="Reassign products & delete <?php echo $cutRow['name']; ?>" style="width:100%;height:35px;color:#fff;margin-top:20px;background:#3faddd;outline:none;border:0px;font-weight:bold;">
                        </form>
                    </div>
                    <?php
                    }
					?></div><?php

				}

			?>

			</div>

	</div>

</main>

<script type="text/javascript">

	$(document).ready(function(){

    $('#SearchCutgroups option.s'+$('#SearchSpecies').first().val()).show();

    $('#SearchSpecies').change(function(){
        var thisval = $(this).val();
        $('#SearchCutgroups option.allsoption').hide();
        $('#SearchCutgroups option.header').show();
        $('#SearchCutgroups option.s'+thisval).show();
        $('#SearchCutgroups').val($('#SearchCutgroups option.header').first().attr('sid'));

				// iOS fix - display:none doesn't work on select options
				$('#SearchCutgroups option.allsoption').unwrap('span');
        $('#SearchCutgroups option.allsoption').wrap('<span/>');
        $('#SearchCutgroups option.s'+thisval).unwrap();
				
        var id = $(this).val();
    });


		

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

				xhttp.open("POST", "/ajax/cutPageList.php", true);

				xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

				xhttp.send("searchterm=" + val);

			

		});

		

		$('.speciesName').click(function(){

			$(this).next('.cutsContainer').toggle();

			console.log(1);

		});

	});

	

	function deleteRow(id){

		if(confirm('Are you sure you want to delete this?')){

			window.location.href = "/scripts/deleteCut.php?id=" + id;

			// console.log(id);

		}

	}

	

</script>

<div id="btm"></div>

</body>

</html>