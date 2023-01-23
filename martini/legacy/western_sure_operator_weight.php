<?php
    include_once('functions.php');
    $update_done = false;
?>
<!doctype html>
<html class="int">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Town &amp; Country</title>
<link href="css/style.css" rel="stylesheet" type="text/css">
<link href="css/main.css" rel="stylesheet" type="text/css">
<link href="css/font-awesome.css" rel="stylesheet" type="text/css">

</head>
<body class="menu">
<div id="top">
	<a href="menu.php" id="menu">MENU</a>
	<a href="logout.php" id="logout">LOGOUT</a>
</div>
<main>
    <h1 class="int">SYSTEM SETTINGS</h1>	
    <br/><br/>
	<div id="menu_wrasp">
		<form method="POST">
			<div class="formElement flex">
				<div>
				<?php
					$resultsColumn3 = prepareExecuteQuery("SELECT * FROM `system_settings`");

					while($page = mysqli_fetch_array($resultsColumn3)){
                        if (request($page['key_name']) !== null)
                        {
                            $sql = "UPDATE `system_settings` SET `key_value` = ? WHERE `key_name` = ?";
                            prepareExecuteQuery($sql,'ss',[request($page['key_name']),$page['key_name']]);
                            $page['key_value'] = request($page['key_name']);
                            $update_done = true;
                        }
					?>
					<div class="formElement">
                        <label><?php echo $page['key_name']; ?></label>
						<input required type="text" name="<?php echo $page['key_name']; ?>" value="<?php echo $page['key_value']; ?>">						
					</div>
					<?php
					}
                    if ($update_done)
                    {
                 ?>
                 <script>alert("Done");</script>
                 <?php
                    }
				?>
				</div>
			</div>
			<input type="submit" class="formbtn" value="Update Settings">
		</form>        
	</div>	
</main> 
</body>
</html>