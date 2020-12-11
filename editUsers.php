<?php
    include_once('functions.php');
    
    if($_GET['delid'] != ''){
    

        $delid = mysqli_real_escape_string($conn, $_GET['delid']);


        $x = "DELETE FROM `users` WHERE id='$delid'";
        $y = mysqli_query($conn, $x); 

        ?> <script> window.location.href = '/editUsers.php'; </script> <?php
    }

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
    <h1 class="int">CREATE USER</h1>	
    <br/><br/>
	
	<div id="menu_wrasp">
		<?php

			if($_GET['id'] != ''){
				
				$id = $_GET['id'];

				$x = "SELECT * FROM users WHERE id = '$id'";
				$yy = mysqli_query($conn, $x);
				$data = mysqli_fetch_array($yy);

				$page_ids = explode(',', $data['pages']);
			}
		?>
		<form method="POST" action="<?php if($_GET['id'] != ''){ echo '/scripts/updateUser.php'; } else { echo '/scripts/addUser.php'; } ?>">
			<input type="hidden" value="<?php echo $_GET['id']; ?>" name="id">
			
			<div class="formElement">
				<label>Name</label>
				<input type="text" name="name" class="inputtext" value="<?php echo $data['name']; ?>">
			</div>
			
			<div class="formElement">
				<label>Email Address</label>
				<input type="text" name="email" class="inputtext" value="<?php echo $data['email']; ?>">
			</div>
			
			<div class="formElement">
				<label>Password</label>
				<input type="text" class="inputtext" name="password">
			</div>
			
			<div class="formElement flex">
				<div style="width:33%;">
				<h4>Sales & Purchasing</h4>
				<?php
					$resultsColumn1 = mysqli_query($conn, "SELECT * FROM `page_permissions` WHERE `column` = 1");

					while($page = mysqli_fetch_array($resultsColumn1)){
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
					$resultsColumn2 = mysqli_query($conn, "SELECT * FROM `page_permissions` WHERE `column` = 2");

					while($page = mysqli_fetch_array($resultsColumn2)){
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
					$resultsColumn3 = mysqli_query($conn, "SELECT * FROM `page_permissions` WHERE `column` = 3");

					while($page = mysqli_fetch_array($resultsColumn3)){
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
				if($_GET['id'] != ''){
				?><input type="submit" class="formbtn" value="Update User"><?php
				}else{
				?><input type="submit" class="formbtn" value="Add User"><?php
				}
			?>
		</form>
 		
		
		<br/><Br/><br/>
		<h1 class="int">USERS LIST</h1>	
		<br/>
		<?php
		
			session_start();
			
			$userid = $_SESSION['USER'];
			
 			$x = "SELECT * FROM `users` ORDER BY id DESC";
			$y = mysqli_query($conn, $x);
			
			while($row = mysqli_fetch_array($y)){
				
            ?>
            <div class="menuItem">
                <div class="text" style="text-transform: none;"><?php echo $row['name']; ?> [<?php echo $row['email']; ?>]</div>
                <div class="actions">
                    <a href="/editUsers.php?id=<?php echo $row['id']; ?>" class="icon"><i class="fa fa-pencil" style="padding-right:4px;" aria-hidden="true"></i></a>
                    <a href="javascript:;" onclick="if(confirm('Are you sure you want to delete this?')){ window.location.href= '/editUsers.php?delid=<?php echo $row['id']; ?>'; }" class="icon"><i class="fa fa-close" style="padding-right:4px;" aria-hidden="true"></i></a>
                </div>
            </div>
            <?php
			}
        ?>
         
        
	</div>	
</main> 
</body>
</html>