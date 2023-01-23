<?php
	include_once('legacy/functions.php');
	
	session_start();
	
	if(isset($_SESSION['USER'])){
		header('location:legacy/menu.php');
	}
	 
		// echo $password = sha1($mysqli->real_escape_string( 'password'));
?>
<!doctype html>
<html class="int">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Town &amp; Country</title>
<link href="legacy/css/style.css" rel="stylesheet" type="text/css">
</head>
<body>
<main>
	<h1>Town &amp; Country</h1>
	<div id="login">
		<h2>Welcome</h2>
		<form method="post" id="loginform" action="legacy/script_login.php">
			<input type="text" name="email" id="email" placeholder="Email">
			<input type="password" name="password" id="password" placeholder="Password">
			<div id="remember">
				<span>Stayed Signed In</span>
				<input type="checkbox" name="remember">
			</div>
			<input type="submit" name="btnsubmit" value="Sign In">
		</form>
	</div>
</main>
<div id="btm"></div>
</body>
</html>
