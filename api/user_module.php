<?php
	if(!isset($_SESSION))
    {
        session_start();
    }
	include_once(dirname(__FILE__) . "/../config/globals.php");
	include_once(dirname(__FILE__) . "/db_module.php");
	define ('USER_ID_LENGTH', 6); // up to 2*10^9 users

	//include special config file
	require_once($private_config_values_location . 'config.php');

//	User files contain basic account details and a list of uploaded files
//	Each user has a folder with his id and inside that folder a file names <ID>.json
//  User folders will be grouped by the first 2 chars in their ID


function get_user_path($user_id){
	global $user_account_location;
	$base = $user_account_location;
	$path = $base . substr($user_id, 0, 2) . '/' . $user_id . '/';
	return $path;
}

//WARNING does not check if the file actually exists. Call user_exist to perform that check
function get_user_file($user_id){
	$base = get_user_path($user_id);
	$user_file = $base . $user_id . '.json';
	return $user_file;
}

function user_exist($user_id){
	$user_file = get_user_file($user_id);
	return (is_file($user_file));
}

function get_user_id($user_email){
	$internal_user_id = md5($user_email);
	return $internal_user_id;
}

//Create a user based on the supplied user_data
// user_data array must have
// -- email
// -- password (in cleartext!)
// User data can have aditional fields
function create_user($user_data){
	
	//Create user on filesystem
	$internal_user_id = get_user_id($user_data['email']);
	if (user_exist($internal_user_id)){
		$mail = $user_data['email'];
		throw new Exception("User exists: $internal_user_id ($mail)", 1);		
	}
	$user_path = get_user_path($internal_user_id);
	mkdir($user_path, 0755, true);
	$user_file = fopen(get_user_file($internal_user_id), 'w');
	if(!$user_file){
		throw new Exception("User file doesn't exist!", 2);	
	}
	//encode password
	$initial_pwd = $user_data["password"];
	$pwd = password_hash($initial_pwd, PASSWORD_DEFAULT);
	$user_data["password"] = $pwd;
	fwrite($user_file, json_encode($user_data));
	fclose($user_file);
	
	// Create user in database
	$id = get_user_id($user_data['email']);
	$e_mail = $user_data['email'];
	$name = $user_data['name'];
	$pwd = password_hash($initial_pwd, PASSWORD_DEFAULT);
	try {
		db_create_user($id, $e_mail, $name, $pwd);
	}
	catch (Exception $e){
		$_SESSION['err-msg'] .= $e->getMessage() . "<br>";
	}
}


//Log in a user that has the specified email and password
function login_user($user_login_data){
	
	//DB based log-in
	$login_ok = FALSE;
	$s_uid = NULL;
	$s_name = NULL;
	$s_email = NULL;
	
	try {
		$matching_usr = db_get_user_login_data($user_login_data['email']);
		if (count($matching_usr) == 1){
			$pwd_hash = $matching_usr[0]['password'];
			$pwd = $user_login_data['password'];
			
			if (password_verify($pwd, $pwd_hash))
			{
				$login_ok = TRUE;
				$s_uid = $matching_usr[0]['id'];
				$s_name = $matching_usr[0]['name'];
				$s_email = $matching_usr[0]['email'];
			}
		}
	}
	catch (Exception $e){
		$_SESSION['err_msg'] .= $e->getMessage() . "<br>";
		$login_ok = FALSE;
	}
	
	if ($login_ok == FALSE){
		$_SESSION['err_msg'] .= "Failed to log in. Wrong username or password.\n<br>";
	}
	else {
		$_SESSION['uid'] = $s_uid;
		$_SESSION['uname'] = htmlspecialchars($s_name);
		$_SESSION['email'] = $s_email;
	}
	return $login_ok;
	
	//File based log-in
	$internal_user_id = get_user_id($user_login_data['email']);
	if (user_exist($internal_user_id)){
		$user_file = get_user_file($internal_user_id);
		$data = file_get_contents($user_file);
		$data = json_decode($data);
		$password_hash = $data->password;
		if (password_verify($user_login_data['password'], $password_hash)){
			$_SESSION['uid'] = $internal_user_id;
			$_SESSION['uname'] = htmlspecialchars($data->name);
			$_SESSION['email'] = $user_login_data['email'];
			return TRUE;
		} else {
			unset($_SESSION['uid']);
			unset($_SESSION['uname']);
		}
	}
	
	$_SESSION['err_msg'] .= "Failed to log in. Wrong username or password.\n<br>";
	return FALSE;
}

function add_file_to_user($user_id, $file_id){
	if (!user_exist($user_id)){
		return;
	}
	$path = get_user_path($user_id);
	$path .= 'files/';
	if (!is_dir($path)){
		mkdir($path, 0755, true);
	}
	$path .= $file_id;
	file_put_contents($path, $file_id);
}

function remove_file_from_user($user_id, $file_id){
	if (!user_exist($user_id)){
		return;
	}
	$path = get_user_path($user_id);
	$path .= 'files/';
	$file = $path . $file_id;
	if (is_file($file)){
		unlink($file);
	}
}

function file_owned_by_user($user_id, $file_id){
	if (!user_exist($user_id)){
		return FALSE;
	}
	$path = get_user_path($user_id);
	$path .= 'files/';
	$file = $path . $file_id;
	return $file;	
	//return is_file($file);
}
?>
