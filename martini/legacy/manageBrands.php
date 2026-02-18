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

	<script src="https://code.jquery.com/jquery-1.12.4.js"></script><script src="https://malsup.github.io/jquery.form.js"></script>

	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

	<script>

	$( function() {

		$( "#datepicker" ).datepicker();

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



				$x = "SELECT * FROM `brands` WHERE `deleted` = 0 AND `id` = ?";

				$yy = prepareExecuteQuery($x,'i',[$id]);



				$data = mysqli_fetch_array($yy);

			}

		?>

		<form id="mainForm" method="POST" action="<?php if(request()->input('id') != ''){ echo 'scripts/updateBrand.php'; } else { echo 'scripts/addBrand.php'; } ?>">

		<table width="100%" border="0" cellpadding="0" cellspacing="0">

			<tr><td align="center"><h1 class="int"><?php if(request()->input('id') != ''){ echo 'UPDATE'; } else { echo 'ADD'; } ?> BRAND</h1></td></tr>

			<tr>

				<td colspan="2">

					<label>Name</label>

					<input type="text" name="id" value="<?php echo $data['id']; ?>" style="display:none;">

					<input type="text" name="name" value="<?php echo $data['name']; ?>">

				</td>

			</tr>

			<tr>

				<td>

					<?php

					if(request()->input('id') != ''){

					?><input type="button" onclick="mainForm()" value="Update Brand"><?php

					}else{

					?><input type="button" onclick="mainForm()" value="Add Brand"><?php

					}

					?>

				</td>
                <td>
                    <?php if(request()->input('id') != ''){	?>
                    <input type="button" value="<?php echo ($data['deleted'] == 1)?"Restore":"Delete"; ?>" onclick="if(confirm('Are you sure you want to <?php echo ($data['deleted'] == 1)?'restore':'delete'; ?> this?')){ window.location.href = 'scripts/<?php echo ($data['deleted'] == 1)?'restore':'delete'; ?>Brand.php?id=<?php echo $data['id']; ?>'; }"/>
                    <?php } ?>
                </td>
			</tr>

		</table>

		</form>

		<h1 class="int">BRAND LIST</h1>

		<input type="text" id="instantSearch" placeholder="Search.." style="width:260px;height:28px;padding-left:10px;" enterkeyhint="go">



		<div id="cutAjax">

		<?php

			$x = "SELECT * FROM `brands` WHERE `deleted` = 0 ORDER BY `name` ASC";

			$y = prepareExecuteQuery($x);

			while($row = mysqli_fetch_array($y)){

		?>

		<table width="100%" border="0" cellpadding="0" cellspacing="0">



				<tr><td align="center" class="pos">

					<a href="javascript:;" class="intake">
						<table width="100%" border="0">
							<tr>
								<td width="100" align="left">ID: <?php echo $row['id']; ?></td>
								<td align="center" style="font-size: 18px;"><?php echo $row['name']; ?></td>
								<td width="100" align="right"></td>
							</tr>
						</table>
					</a>

					<a href="manageBrands.php?id=<?php echo $row['id']; ?>"  <?php if($user['user_type'] == 'A'){ ?> style="right:-35px;" <?php } ?>id="delete_intake"><i class="fa fa-pencil"style="padding-right:0px;" aria-hidden="true"></i></a>

				</td></tr>

		</table>

		<?php

			}

		?>

		</div>

	</div>

</main>

<script type="text/javascript">
function mainForm(){
	$('#mainForm').ajaxSubmit({headers:{'X-CSRF-TOKEN': "<?php echo csrf_token();?>"},success:mainFormSucess});
}
function mainFormSucess(){
	location.reload();
}
	$(document).ready(function(){



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

				xhttp.open("POST", "ajax/brandPageList.php", true);

				xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				xhttp.setRequestHeader('X-CSRF-TOKEN', "<?php echo csrf_token();?>");
				xhttp.send("searchterm=" + val);



		});



		$('.speciesName').click(function(){

			$(this).next('.cutsContainer').toggle();

			console.log(1);

		});

	});



	function deleteRow(id){

		if(confirm('Are you sure you want to delete this?')){

			window.location.href = "scripts/deleteBrand.php?id=" + id;

			// console.log(id);

		}

	}



</script>

<div id="btm"></div>

</body>

</html>
