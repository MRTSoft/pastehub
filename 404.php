<?php
	if(!isset($_SESSION)) 
    { 
        session_start(); 
    }
	require_once(dirname(__FILE__) . '/config/languages.php');
	if (!isset($_SESSION['language'])){
		$language = $lanDB['en'];
	} else {
		$language = $lanDB[$_SESSION['language']];
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo $language['title_404']; ?></title>
	<link rel="stylesheet" type="text/css" href="css/style-main.css">
	<link rel="stylesheet" type="text/css" href="css/style-responsive.css">
	<script type="text/javascript" src="js/script.js"></script>
	<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
	<?php include_once("header.php"); ?>

<style type="text/css">
.caption {
	position: absolute;
	left: 0;
	top: 50%;
	width: 100%;
	text-align: center;
	color: #000;
	font: 400 15px/1.8 "Lato", sans-serif;
}

.caption .border {
	background-color: #111;
	color: #fff;
	padding: 18px;
	font-size: 25px;
	letter-spacing: 7px;
}

.border a {
	color: yellow;
}

body, html {
  height: 100%;
  margin: 0;
  color: #777;
}

</style>
<article class="caption">
			<span class="border" style="font-size: 35px;">
				<?php echo $language['title_404']; ?>
			</span> <br/>
			<span class="border">
				<?php echo $language['explain_404']; ?>
				<a href="index.php"><?php echo $language['back_home']; ?></a>
			</span>
</article>
	<?php include_once("footer.php"); ?>;
</body>
</html>
