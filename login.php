<?php
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	if(!isset($_SESSION)){
		session_start();
	}
	include_once(dirname(__FILE__) . "/api/user_module.php");
	require_once(dirname(__FILE__) . "/config/languages.php");
	if (!isset($_SESSION['language'])){
		$language = $lanDB['en'];
	} else {
		$language = $lanDB[$_SESSION['language']];
	}
	//Hide the log in button area
	$hide_login_area = TRUE;
	if ($_SERVER['REQUEST_METHOD'] == 'POST' && 
		  isset($_POST['intention']) && 
		  $_POST['intention'] == 'login')
	{
		if (isset($_POST['email']) && isset($_POST['pwd'])){
			$login_data = array();
			$login_data['email'] = $_POST['email'];
			$login_data['password'] = $_POST['pwd'];
			if (login_user($login_data)){
				header("Location: index.php");
				die();
			}
			else {
				$_SESSION['err_msg'] .= $language['login_failed'];
			}
		}
	}

	if ($_SERVER['REQUEST_METHOD'] == 'POST' && 
		  isset($_POST['intention']) && 
		  $_POST['intention'] == 'signup')
	{
		$valid_data = TRUE;
		do {
			if (empty($_POST['uname'])){
				$valid_data = FALSE;
				$_SESSION['info_msg'] .= "Empty user. <br/>";
				break;
			}

			if (empty($_POST['email']) || empty($_POST['email2'])){
				$valid_data = FALSE;
				$_SESSION['info_msg'] .= "Empty mails. <br/>";
				break;
			}

			if (empty($_POST['pwd']) || empty($_POST['pwd2'])){
				$valid_data = FALSE;
				$_SESSION['info_msg'] .= "Empty pwd. <br/>";
				break;
			}
			if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) || 
				($_POST['email'] != $_POST['email2']))
			{
				$valid_data = FALSE;
				$_SESSION['info_msg'] .= "Bad mail address format or mismatch. <br>";
				break;
			}
			if ($_POST['pwd'] != $_POST['pwd2']){
				$valid_data = FALSE;
				$_SESSION['info_msg'] .= "Mismatch pwd.<br/>";
				break;
			}
		}while(FALSE);
		if ($valid_data){
			$user_data = array();
			$user_data['email'] = $_POST['email'];
			$user_data['password'] = $_POST['pwd'];
			$user_data['name'] = $_POST['uname'];
			try {
				create_user($user_data);
				$_SESSION['info_msg'] .= $language['signup_success'];
			}
			catch (Exception $e){
				$_SESSION['err_msg'] .= $language['signup_failed'] . $e->getCode();
			}
			
		}
		else {
			$_SESSION['err_msg'] .= $language['signup_failed'];
		}
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title>PasteHub - Log In</title>
	<script type="text/javascript" src="js/script.js"></script>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="css/style-main.css">
	<link rel="stylesheet" type="text/css" href="css/style-responsive.css">
</head>
<body>
	<?php 
	include_once(dirname(__FILE__) . "/header.php"); 
	unset($hide_login_area);
	?>
	<?php if (isset($_SESSION['err_msg'])) {
	?>
	<!-- Warning message place -->
	<section class="row">
		<span class="col-1 filler">&nbsp;</span>
		<span class="col-10 alert">
				<span class="closebtn" 
				onclick="this.parentElement.parentElement.style.display='none';">
					&times;
				</span>
				<?php 
					echo $_SESSION['err_msg']; 
					unset($_SESSION['err_msg']); 
				?>
		</span>
		<span class="col-1 filler">&nbsp;</span>	
	</section>
	<?php }
	if (isset($_SESSION['info_msg'])){ ?>
	<!-- Info message place -->
	<section class="row">
		<span class="col-1 filler">&nbsp;</span>
		<span class="col-10 info">
				<span class="closebtn" 
				onclick="this.parentElement.parentElement.style.display='none';">
					&times;
				</span>
				<?php 
					echo $_SESSION['info_msg']; 
					unset($_SESSION['info_msg']); 
				?>
		</span>
		<span class="col-1 filler">&nbsp;</span>	
	</section>
	<?php } ?>

	<section class="row">
	<script type="text/javascript" src="js/script.js"></script>
	
	<form name="login_form" action="login.php" method="POST" class="col-6"  
	onsubmit="return validate_login();">
		
		<!-- Form title -->
		<span class="row">
			<h2 class="col-12 center-text"> <?php echo $language['login_action'];  ?> </h2>
		</span>
		
		<!-- email field -->
		<span class="row">
			 <input type="text" name="email" class="col-12" placeholder="E-mail" required>
		</span>

		<!-- password field -->
		<span class="row">
			 <input type="Password" name="pwd" class="col-12" placeholder="<?php 
			 	echo $language['password']; ?>" required>
		</span>

		<!-- Submit button -->
		<span class="row">
			<input type="submit" value="<?php echo $language['login_action']; ?>" class="col-12">
			<input type="hidden" name="intention" value="login">
		</span>
	</form>

	<!-- 
		 * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
		 * * * * * * * * Sign up form  * * * * * * * * * * * * * *
		 * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	-->
	<form name="signup_form" action="login.php" method="POST" class="col-6" 
	onsubmit="return validate_signup()">
		<!-- Form title -->
		<span class="row">
			<h2 class="col-12 center-text"> <?php echo $language['signup_action'];  ?> </h2>
		</span>
		
		<!-- name field -->
		<span class="row">
			 <input type="text" name="uname" class="col-12" placeholder="<?php
			 	echo $language['name'];
			 ?>" required>
		</span>

		<!-- email field -->
		<span class="row">
			 <input type="text" name="email" class="col-12" placeholder="E-mail" required>
		</span>

		<!-- repeat email field -->
		<span class="row">
			 <input type="text" name="email2" class="col-12" placeholder="<?php
			 	echo $language['repeat_mail'];
			 ?>" required>
		</span>

		<!-- password field -->
		<span class="row">
			 <input type="password" name="pwd" class="col-12" placeholder="<?php 
			 	echo $language['password']; ?>" required>
		</span>

		<!-- repeat password field -->
		<span class="row">
			 <input type="password" name="pwd2" class="col-12" placeholder="<?php 
			 	echo $language['repeat_password']; ?>" required>
		</span>

		<!-- Submit button -->
		<span class="row">
			<input type="submit" value="<?php echo $language['signup_action']; ?>" class="col-12">
			<input type="hidden" name="intention" value="signup">
		</span>
	</form>
	
	</section>
	<?php include_once(dirname(__FILE__) . '/footer.php') ?>
</body>
</html>


