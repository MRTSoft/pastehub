<?php
	# Debug: Enable error reporting
	# TODO Check session before calling session_start()
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	if(!isset($_SESSION)) 
    { 
        session_start(); 
    }
	require_once(dirname(__FILE__) . '/api/storage_module.php');
	//See if we need to clean
	$should_clean = check_should_clean_files();
	require_once(dirname(__FILE__) . '/config/languages.php');
	if (!isset($_SESSION['language'])){
		$language = $lanDB['en'];
	} else {
		$language = $lanDB[$_SESSION['language']];
	}
	if ($should_clean) {
		ob_start();
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title>PasteHub - <?php echo $language['page_title'];//Proiect TI?></title>
	<link rel="stylesheet" type="text/css" href="css/style-main.css">
	<link rel="stylesheet" type="text/css" href="css/style-responsive.css">
	<script type="text/javascript" src="js/script.js"></script>
	<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<?php include_once("header.php"); ?>

<section id="upload-form" class="row">
	<span class="col-1 filler">&nbsp;</span>
	<form action="api/put.php" method="POST" accept-charset="UTF-8" class="col-10">
		<span class="row">
			<input class="col-12" type="text" name="title" placeholder="<?php echo $language['file_title']; ?>">
		</span>
		<span class="row">
		<textarea class="col-12" name="content" rows="20"
		placeholder="<?php echo $language['file_content']; ?>" required></textarea>
		</span>
		<span class="row">
			<input class="col-12" type="submit" value="<?php echo $language['submit_button']; ?>">
			<!-- 
			<input class="col-6" type="button" name="preview" value="<?php echo $language['preview_button']; ?>">
		-->
		</span>
	</form>
	<span class="col-1 filler">&nbsp;</span>
</section>

<?php include_once(dirname(__FILE__) . '/footer.php') ?>

</body>
</html>

<?php
if ($should_clean){
	session_destroy();//recycle session - we need to do this
	$output = ob_get_clean();
	ignore_user_abort(true);
	set_time_limit(0);
	header("Connection: close");
	header("Content-Length: ".strlen($output));
	header("Content-Encoding: none");
	perform_maintenance();
	echo $output;
	flush();
}
?>
