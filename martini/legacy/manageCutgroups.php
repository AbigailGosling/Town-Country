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

	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>

	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

	<script>

		$(function(){

			$("#datepicker").datepicker();

		});

	</script>

</head>
<?php
    if(request('msg') != ''){
        ?>
    <script> alert('<?php echo request('msg'); ?>'); </script>
        <?php
    }
?>
<body class="menu">

<div id="top">

	<a href="menu.php" id="menu">MENU</a>

	<a href="logout.php" id="logout">LOGOUT</a>

</div>

<main>

	<div id="intakelist">

		<?php

			if(request('id') != ''){

				

				$id = request('id');

				

				$x = "SELECT * FROM cutgroups WHERE id = ?";

				$yy = prepareExecuteQuery($x,'i',[$id]);

				

				$data = mysqli_fetch_array($yy);

			}

		?>

		<form method="POST" action="<?php if(request('id') != ''){ echo 'scripts/updateCutgroup.php'; } else { echo 'scripts/addCutgroup.php'; } ?>">

		<table width="100%" border="0" cellpadding="0" cellspacing="0">

			<tr><td align="center"><h1 class="int"><?php if(request('id') != ''){ echo 'UPDATE'; } else { echo 'ADD'; } ?> CUT GROUP</h1></td></tr>
 
            <tr>
				<td>
					<label>Species</label>

					<select name="species_id">
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

					<label>Name</label>

					<input type="text" name="id" value="<?php echo $data['id']; ?>" style="display:none;">

					<input type="text" name="name" value="<?php echo $data['name']; ?>">

				</td>

			</tr>
			

			<tr>

				<td>

					<?php

					if(request('id') != ''){

					?><input type="submit" value="Update"><?php

					}else{

					?><input type="submit" value="Add"><?php

					}

					?>

				</td>

			</tr>

		</table>

		</form>

		<h1 class="int">Cut Group LIST</h1>
			<div id="cutAjax">


                <div class="cutsContainer">
					<?php
                        $cutX = "SELECT * FROM `cutgroups`";
					    $cutY = prepareExecuteQuery($cutX); 

					    while($cutRow = mysqli_fetch_array($cutY)){
				        $thisid = $cutRow['species_id'];
                        $y2 = prepareExecuteQuery("SELECT * FROM species WHERE id=?",'i',[$thisid]);
                        $species = mysqli_fetch_array($y2);
                        
					?>

					<table style="width: 100%;display: table;">

						<tr><td align="center" class="pos">
							<a href="javascript:;" class="intake"><?php echo $cutRow['name']; ?> (<?php echo $species['name']; ?>)</a>
							<a href="manageCutgroups.php?id=<?php echo $cutRow['id']; ?>" style="right:-35px;height: 29px;" id="delete_intake"><i class="fa fa-pencil" style="padding-right:0px;" aria-hidden="true"></i></a>
							<?php if($user['user_type'] == 'A'){ ?>
							    <a href="javascript:;" onclick="deleteRow('<?php echo $cutRow['id'];?>')" id="delete_intake" style="right:-75px;"><i class="fa fa-times" aria-hidden="true"></i></a>
							<?php } ?>
						</td></tr>
					</table>
					<?php 
					}
					?></div>
			</div>

	</div>

</main>

<script type="text/javascript">

	$(document).ready(function(){
 
 
	});

	function deleteRow(id){
		if(confirm('Are you sure you want to delete this?')){
            window.location.href = "/scripts/deleteCutgroup.php?id=" + id;
		}
	}

</script>

<div id="btm"></div>

</body>

</html>