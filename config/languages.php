<?php
	$lanDB = array();
	require_once(dirname(__FILE__) . '/romanian.php');
	$lanDB['ro'] = $romanian;
	require_once(dirname(__FILE__) . '/english.php');
	$lanDB['en'] = $english;
?>