<?php
    include_once('functions.php');
    
    if(request()->input('delid') != ''){
    

        $delid = request()->input('delid');


        $x = "DELETE FROM `users` WHERE id=?";
        $y = prepareExecuteQuery($x,'i',[$delid]); 

        ?> <script> window.location.href = 'editUsers.php'; </script> <?php
    }

?>
<!doctype html>
<html class="int">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
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
<title>Town &amp; Country</title>
<link href="css/style.css" rel="stylesheet" type="text/css">
<link href="css/main.css" rel="stylesheet" type="text/css">
<link href="css/font-awesome.css" rel="stylesheet" type="text/css">

</head>
<body class="menu">
<div id="top">
	<a href="menu.php" id="menu">MENU</a>
	<a href="logout" id="logout">LOGOUT</a>
</div>
<main>
    <h1 class="int">CREATE USER</h1>	
    <br/><br/>
	
	<div id="menu_wrasp">
		<?php

			if(request()->input('id') != ''){
				
				$id = request()->input('id');

				$x = "SELECT * FROM users WHERE id = ?";
				$yy = prepareExecuteQuery($x,'i',[$id]);
				$data = $yy->fetch_assoc();

				$page_ids = explode(',', $data['pages']);
			}
			else
			{
				$page_ids = array();
			}
		?>
		<form id="mainForm" method="POST" action="<?php if(request()->input('id') != ''){ echo 'scripts/updateUser.php'; } else { echo 'scripts/addUser.php'; } ?>">
			<input type="hidden" value="<?php echo request()->input('id'); ?>" name="id">
			
			<div class="formElement">
				<label>Name</label>
				<input type="text" name="name" class="inputtext" value="<?php echo $data['name']; ?>" required>
			</div>
			
			<div class="formElement">
				<label>Email Address</label>
				<input type="email" name="email" class="inputtext" value="<?php echo $data['email']; ?>" required>
			</div>
			
			<div class="formElement">
				<label>Password</label>
				<input type="text" class="inputtext" name="password" <?php if(request()->input('id') == ''){ echo 'required'; } ?>>
			</div>

			<div class="formElement">
				<label>Show Intake Overview Table Prices</label>
				<select name="view_intake_prices">
					<option value="0" <?php if($data['view_intake_prices'] == 0){ echo 'selected'; } ?>>No</option>
					<option value="1" <?php if($data['view_intake_prices'] == 1){ echo 'selected'; } ?>>Yes</option>
				</select>
			</div>

			<div class="formElement">
				<label>Ability to change salesman on create sale</label>
				<select name="allow_override_salesman">
					<option value="0" <?php if($data['allow_override_salesman'] == 0){ echo 'selected'; } ?>>No</option>
					<option value="1" <?php if($data['allow_override_salesman'] == 1){ echo 'selected'; } ?>>Yes</option>
				</select>
			</div>

			<div class="formElement">
				<label>User Type</label>
				<select name="user_type">
					<option value="M" <?php if($data['user_type'] == 'M'){ echo 'selected'; } ?>>User</option>
					<option value="A" <?php if($data['user_type'] == 'A'){ echo 'selected'; } ?>>Admin</option>
				</select>
			</div>
			
			<div class="formElement flex">
				<div style="width:33%;">
				<h4>Sales & Purchasing</h4>
				<?php
					$resultsColumn1 = prepareExecuteQuery("SELECT * FROM `page_permissions` WHERE `column` = 1");

					while($page = $resultsColumn1->fetch_assoc()){
					?>
					<div class="checkbox">
						<input type="checkbox" id="page<?php echo $page['id']; ?>" name="pages[]" value="<?php echo $page['id']; ?>" <?php if(in_array($page['id'], $page_ids)){ echo 'checked'; } ?>>
						<label for="page<?php echo $page['id']; ?>"><?php echo ucfirst(strip_tags($page['name'])); ?></label><br>
					</div>
					<?php
					}
				?>
				</div>

				<div style="width:33%;">
				<h4>Goods in/out</h4>
				<?php
					$resultsColumn2 = prepareExecuteQuery("SELECT * FROM `page_permissions` WHERE `column` = 2");

					while($page = $resultsColumn2->fetch_assoc()){
					?>
					<div class="checkbox">
						<input type="checkbox" id="page<?php echo $page['id']; ?>" name="pages[]" value="<?php echo $page['id']; ?>" <?php if(in_array($page['id'], $page_ids)){ echo 'checked'; } ?>>
						<label for="page<?php echo $page['id']; ?>"><?php echo ucfirst(strip_tags($page['name'])); ?></label><br>
					</div>
					<?php
					}
				?>
				</div>

				<div style="width:33%;">
				<h4>Admin. Tools</h4>
				<?php
					$resultsColumn3 = prepareExecuteQuery("SELECT * FROM `page_permissions` WHERE `column` = 3");

					while($page = $resultsColumn3->fetch_assoc()){
					?>
					<div class="checkbox">
						<input type="checkbox" id="page<?php echo $page['id']; ?>" name="pages[]" value="<?php echo $page['id']; ?>" <?php if(in_array($page['id'], $page_ids)){ echo 'checked'; } ?>>
						<label for="page<?php echo $page['id']; ?>"><?php echo ucfirst(strip_tags($page['name'])); ?></label><br>
					</div>
					<?php
					}
				?>
				</div>
			</div>

			
			<?php
				if(request()->input('id') != ''){
				?><input type="button" onclick="mainForm()" class="formbtn" value="Update User"><?php
				}else{
				?><input type="button" onclick="mainForm()" class="formbtn" value="Add User"><?php
				}
			?>
		</form>
 		
		
		<br/><Br/><br/>
		<h1 class="int">USERS LIST</h1>	
		<br/>
		<?php
		
			session_start();session_write_close();
			
			$userid = $_SESSION['USER'];
			
 			$x = "SELECT * FROM `users` ORDER BY id DESC";
			$y = prepareExecuteQuery($x);
			
			while($row = $y->fetch_assoc()){
				
            ?>
            <div class="menuItem">
                <div class="text" style="text-transform: none;"><?php echo $row['name']; ?> [<?php echo $row['email']; ?>]</div>
                <div class="actions">
                    <a href="editUsers.php?id=<?php echo $row['id']; ?>" class="icon"><i class="fa fa-pencil" style="padding-right:4px;" aria-hidden="true"></i></a>
                    <a href="javascript:;" onclick="if(confirm('Are you sure you want to delete this?')){ window.location.href= 'editUsers.php?delid=<?php echo $row['id']; ?>'; }" class="icon"><i class="fa fa-close" style="padding-right:4px;" aria-hidden="true"></i></a>
                </div>
            </div>
            <?php
			}
        ?>
         
        
	</div>
</main> 
</body>
</html>