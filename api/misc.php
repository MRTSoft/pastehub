<?php
	if(!isset($_SESSION)) 
    { 
        session_start(); 
    }
	require_once(dirname(__FILE__) . "/storage_module.php");
	if ($_SERVER['REQUEST_METHOD'] == 'POST'){
		if (isset($_POST['language'])){
			$_SESSION['language'] = $_POST['language'];
			die("");
		}
		if (isset($_POST['remove_file'])){
			if (isset($_SESSION['uid'])){
				$file_id = $_POST['remove_file'];
				$user_id = $_SESSION['uid'];
				db_remove_file($user_id, $file_id);
				if (get_file_path_by_id($file_id) != FALSE &&
					  file_owned_by_user($user_id, $file_id))
				{
					remove_file($file_id);
					remove_file_from_user($user_id, $file_id);
				}
			}
		}
	}

	
/* DO NOT ACTIVATE THIS - unless you know what you're doing
if ($_SERVER['REQUEST_METHOD'] == 'GET'){
	// MAINTENANCE ONLY!!!
	// WARNING!!! ~~ HIGH ~~ server load!
	if (isset($_GET['make_dirs'])){
		echo "Creating dir structure";
		create_storage_dirs('C:/storage/');

	}
}
*/

?>
