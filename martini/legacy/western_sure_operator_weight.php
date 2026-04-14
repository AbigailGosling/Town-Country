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
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
<script src="https://code.jquery.com/ui/jquery-ui-git.js"></script><script src="https://malsup.github.io/jquery.form.js"></script>
<script>
	function mainForm(){
		$('#mainForm').ajaxSubmit({headers:{'X-CSRF-TOKEN': "<?php echo csrf_token();?>"},success:mainFormSucess});
	}
	function mainFormSucess(){
		location.reload();
	}
</script>
</head>
<body class="menu">
<div id="top">
	<a href="menu.php" id="menu">MENU</a>
	<a href="logout" id="logout">LOGOUT</a>
</div>
<main>
    <h1 class="int">SYSTEM SETTINGS</h1>
    <br/><br/>
	<div id="menu_wrasp">
		<form id="mainForm" method="POST">
			<div class="formElement flex">
				<div>
				<?php
                    $apply_update = count(request()->all())!=0;
					$resultsColumn3 = prepareExecuteQuery("SELECT * FROM `system_settings`");

					while($systemsetting = mysqli_fetch_array($resultsColumn3)){
                        if ($apply_update){
                            $postedValue = request()->input($systemsetting['key_name'], null);
                            if ($systemsetting['var_type'] === 'boolean')
                            {
                                $postedValue = $postedValue == 1 ? 1 : 0;
                            }
                            if ($postedValue !== null)
                            {
                                $sql = "UPDATE `system_settings` SET `key_value` = ? WHERE `key_name` = ?";
                                prepareExecuteQuery($sql,'ss',[$postedValue,$systemsetting['key_name']]);
                                $systemsetting['key_value'] = $postedValue;
                                $update_done = true;
                            }
                        }
					?>
					<div class="formElement" <?php echo $systemsetting['hidden'] ? 'style="display:none;"' : ''; ?>>
                        <label><?php echo $systemsetting['description']??$systemsetting['key_name']; ?></label>
						<?php if ($systemsetting['var_type'] === 'boolean'): ?>
                            <input type="checkbox" name="<?php echo $systemsetting['key_name']; ?>" value="1" <?php echo $systemsetting['key_value'] == "1" ? 'checked' : ''; ?>>
						<?php else: ?>
                            <input required type="text" name="<?php echo $systemsetting['key_name']; ?>" value="<?php echo $systemsetting['key_value']; ?>">
						<?php endif; ?>
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
			<input type="button" onclick="mainForm()" class="formbtn" value="Update Settings">
		</form>
	</div>
</main>
</body>
</html>
