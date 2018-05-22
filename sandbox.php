<?php
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	if(!isset($_SESSION)) 
    { 
        session_start(); 
    }
	//include_once(dirname(__FILE__) . "/api/user_module.php");
	//include_once(dirname(__FILE__) . "/api/storage_module.php");
	include_once(dirname(__FILE__) . "/api/db_module.php");
	
?>
<?php
/*
ob_start();
 
// Generage HTML page here
generate_full_html_page();
 

cron_task1();
cron_task2();
*/

?>
<!DOCTYPE html>
<html>
<head>
	<title>A page</title>
</head>
<body>
<pre>

<?php 
$users = "a; b;c; d ; e";
$r = explode(";", $users);
foreach ($r as $key => $val) {
	$r[$key] = trim($val);
}
var_dump($r);

?>
</pre>
<h1>
<a href="index.php">Home</a>
</h1>
</body>
</html>

