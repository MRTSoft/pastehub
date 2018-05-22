<?php
if(!isset($_SESSION))
{
	session_start();
}
require_once(dirname(__FILE__) . "/../config/globals.php");
require_once(dirname(__FILE__) . '/id_module.php');
require_once(dirname(__FILE__) . '/storage_module.php');


//Returns the metadata in array format. Storage should choose how to save it (JSON)
function construct_meta(){
	//Constructs the metadata for the file
	$meta = array();
	$title = $_POST['title'];
	$meta['title'] = $title;
	$meta['expire_date'] = 

	$date = new DateTime();
	$now = new DateTime("now");
	$valability = 0;
	if (!isset($_POST['valability'])){
		global $default_expire_time_days;
		$days =  $default_expire_time_days;
		
	} else {
		$days = (int)$_POST['valability'];
	}
	$format = "P" . (string)$days . "D";
	$valability = new DateInterval($format);
	$now->add($valability);
	$meta['expire_date'] = $now->getTimestamp();
	$meta['valability'] = $days;
	if (isset($_SESSION['uid'])){
		$meta['owner'] = $_SESSION['uid'];
	} else {
		$meta['owner'] = null;
	}
	return $meta;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
	$file_id = get_unique_id();
	$meta_data = construct_meta();
	$content = $_POST['content'];
	try {
		if (isset($_SESSION['uid'])){
			save_file($file_id, $meta_data, $content, $_SESSION['uid']);
		}
		else {
			// saving for Anonymous user
			save_file($file_id, $meta_data, $content);
		}
	}
	catch (Exception $e){
		$_SESSION['error_message'] .= $e->getMessage() . "<br/>";
	}
	//Redirect user to render page
	$url = '../file.php?id=' . $file_id . '&show_link';
	header("Location: $url");
}
?>
