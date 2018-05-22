<?php
require_once(dirname(__FILE__) . "/../config/globals.php");
require_once(dirname(__FILE__) . "/../restricted/config.php");

//TODO connect to database

$singleton_conn = null;

function db_get_connection(){
	global $singleton_conn;
	global $db_host;
	global $db_user;
	global $db_password;
	global $db_name;
	global $db_port;
	if (!$singleton_conn){
		$singleton_conn = new mysqli($db_host, $db_user, $db_password, $db_name, $db_port);
		if ($singleton_conn->connect_errno) {
			return null;
		}
		$singleton_conn->set_charset('utf8');
	}
	return $singleton_conn;
}

$rel_map = array('read' => 1, 'delete' => 2, 'edit' => 4, 'owner' => 7);
$public_user = 'public@pastehub.atwebpages.com';

function db_add_file($id, $path, $expire_in_days, $metadata, $uid = null){
	global $db_prefix;
	$conn = db_get_connection();
	if (is_null($conn))
	{
		return FALSE;
	}
	$insert = $conn->prepare("INSERT INTO {$db_prefix}FILES (id, location, expire_on) VALUES (?,?,DATE_ADD(SYSDATE(), INTERVAL ? DAY))");
	if (!$insert){
		var_dump($insert);
		echo '<pre>';
		var_dump($conn);
		echo "</pre>";
	}
	$insert->bind_param("sss", $id, $path, $expire_in_days);
	$insert->execute();
	global $rel_map;
	
	db_add_relation($id, $uid, $rel_map['owner']);
	db_add_metadata($id, $metadata);
	return TRUE;
}

function db_add_relation($file_id, $user_id, $relation_mask){
	$conn = db_get_connection();
	global $db_prefix;
	global $public_user;
	if (is_null($conn)){
		return FALSE;
	}
	if (is_null($user_id)){
		$user_id = md5($public_user);
	}
	$insert = $conn->prepare("INSERT INTO {$db_prefix}RELATIONS(id, user_id, file_id, code) VALUES (NULL, ?, ?, ?)");
	$insert->bind_param("ssi", $user_id, $file_id, $relation_mask);
	$insert->execute();
	error_log($user_id);
	error_log($insert->error);
	return TRUE;
}

function db_add_metadata($file_id, $metadata){
	//INSERT METADATA entry
	$conn = db_get_connection();
	global $db_prefix;
	if(is_null($conn)){
		throw new Exception("Database connection failed!");
	}
	$q = $conn->prepare("INSERT INTO {$db_prefix}METADATA(id, file_id) VALUES (NULL, ?);");
	$q->bind_param("s", $file_id);
	$q->execute();
	$metadata_id = $q->insert_id;
	$q->close();
	error_log("META id for file {$file_id} is {$metadata_id}");
	$q2 = $conn->prepare("INSERT INTO {$db_prefix}KEYS VALUES (NULL, ?, ?, ?);");
	if (!$q2){
		echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
	}
	$q2->bind_param("sss", $metadata_id, $key, $value);
	foreach ($metadata as $key => $value) {
		$q2->execute();
	}
}

function db_get_user_login_data($user_email){
	$conn = db_get_connection();
	global $db_prefix;
	if(is_null($conn)){
		throw new Exception("Database connection failed!");
	}
	$q = $conn->prepare("SELECT id, email, name, password FROM {$db_prefix}USERS WHERE email = ?;");
	$q->bind_param("s", $user_email);
	$q->execute();
	
	$q->bind_result($r_id, $r_email, $r_name, $r_pwd);
	$i = 0;
	$res = array();
	while($q->fetch()){
		$res[$i] = array(
			"id" => $r_id,
			"email" => $r_email,
			"name" => $r_name,
			"password" => $r_pwd
			);
	}
	return $res;
}

function db_create_user($id, $e_mail, $name, $pwd){
	$conn = db_get_connection();
	if(is_null($conn)){
		throw new Exception("Database connection failed!");
	}
	global $db_prefix;

	$insert = $conn->prepare("INSERT INTO {$db_prefix}USERS(id, email, name, password) VALUES (?, ?, ?, ?)");
	$insert->bind_param("ssss", $id, $e_mail, $name, $pwd);
	$insert->execute();

}



function db_get_user_permissions($user, $file){
	$conn = db_get_connection();
	if(is_null($conn)){
		throw new Exception("Database connection failed!");
	}
	global $db_prefix;
	$q = $conn->prepare("SELECT code FROM {$db_prefix}RELATIONS WHERE user_id = ?  AND file_id = ? ORDER BY code DESC");
	$q->bind_param("ss", $user, $file);
	$q->execute();
	$q->bind_result($atomic_perm);
	$i = 0;
	$perm = 0;
	while($q->fetch()){
		$perm = $perm | $atomic_perm;
	}
	return $perm;
}


/// id - The id of the required file
/// $uid - The id of the user that tries to access this
function db_get_file_path_by_id($id, $uid){
	//NOTE: this also checks if the uid has access to the file
	$conn = db_get_connection();
	if(is_null($conn)){
		throw new Exception("Database connection failed!");
	}
	$file_path = NULL;
	$private_permissions = db_get_user_permissions($uid, $id);
	
	global $public_user;
	$pub_user = md5($public_user);
	$public_permissions = db_get_user_permissions($pub_user, $id);
	
	$has_permission = ($private_permissions | $public_permissions) & 1;
	if ($has_permission == 0){
		//No read permission
		error_log("Unauthorized access from $uid on $id");
		throw new Exception("Forbidden", 403);
	}
	global $db_prefix;
	$q = $conn->prepare("SELECT location FROM {$db_prefix}FILES WHERE id = ?;");
	$q->bind_param("s", $id);
	$q->execute();
	$q->bind_result($file_path);
	$q->fetch();
	return $file_path;
}

function db_remove_file($user_id, $file_id){
	$conn = db_get_connection();
	if(is_null($conn)){
		throw new Exception("Database connection failed!");
	}
	$private_permissions = db_get_user_permissions($user_id, $file_id);
	
	// 7 - 421
	$has_permission = $private_permissions;
	global $rel_map;
	if ($has_permission != $rel_map['owner']){
		//No read permission
		error_log("Unauthorized access from $user_id on $file_id");
		throw new Exception("Forbidden", 403);
	}
	global $db_prefix;
	$q = $conn->prepare("DELETE FROM {$db_prefix}FILES WHERE id = ?;");
	$q->bind_param("s", $file_id);
	$q->execute();
}

function db_get_owned_files($user_id){
	$files = array();
	$conn = db_get_connection();
	if(is_null($conn)){
		throw new Exception("Database connection failed!");
	}
	global $db_prefix;
	$q = $conn->prepare("SELECT file_id FROM {$db_prefix}RELATIONS WHERE user_id = ?;");
	$q->bind_param("s", $user_id);
	$q->execute();

	$q->bind_result($owned_file);
	$i = 0;
	while($q->fetch()){
		$files[$i] = $owned_file;
		$i++;
	}
	return $files;
}

function db_get_permission_type($user,$fid){
	$result = array('public' => FALSE, 'private' => FALSE, 'custom' => '');
	$conn = db_get_connection();
	if(is_null($conn)){
		throw new Exception("Database connection failed!");
	}
	global $db_prefix;

	$private_permissions = db_get_user_permissions($user, $fid);
	
	global $public_user;
	$pub_user = md5($public_user);
	$public_permissions = db_get_user_permissions($pub_user, $fid);

	if ($public_permissions != 0){
		$result['public'] = TRUE;
	}
	else {
		$q = $conn->prepare("SELECT DISTINCT u.email FROM {$db_prefix}RELATIONS r, {$db_prefix}USERS u WHERE u.id = r.user_id AND file_id = ? AND user_id != ?;");
		$q->bind_param("ss", $fid, $user);
		$q->execute();
		$q->bind_result($email);
		while($q->fetch()){
			$result['custom'] .= $email . "; ";
		}
	}
	
	if ($result['public'] == FALSE and strlen($result['custom']) == 0){
		$result['private'] = TRUE;
	}
	return $result;
}

function db_get_user_id($user_email){
	//This will also check the database
	$conn = db_get_connection();
	if(is_null($conn)){
		throw new Exception("Database connection failed!");
	}
	global $db_prefix;
	$q = $conn->prepare("SELECT id FROM {$db_prefix}USERS WHERE email = ?;");
	$q->bind_param("s", $user_email);
	$q->execute();
	$id = FALSE;
	$q->bind_result($id);
	while ($q->fetch()) {
		# Get the id
	}
	return $id;
}

function db_update_permissions($targetFile, $user, $newPerm, $newPermUsers){
	global $use_db_storage;
	if (!$use_db_storage){
		return;
	}
	$conn = db_get_connection();
	if(is_null($conn)){
		throw new Exception("Database connection failed!");
	}
	global $db_prefix;
	global $rel_map;
	global $public_user;
	$publicUser = md5($public_user);
	$oldPerm = db_get_permission_type($user, $targetFile);
	
	if ($newPerm == "public" and $oldPerm['public'] == FALSE)
	{
		//Delete old access
		$q = $conn->prepare("DELETE FROM {$db_prefix}RELATIONS WHERE file_id = ? AND user_id != ?;");
		$q->bind_param("ss", $targetFile, $user);
		$q->execute();
		$q->close();
		//Set public permission
		db_add_relation($targetFile, $publicUser, $rel_map['read']);
	}

	if ($newPerm == "private" and $oldPerm['private'] == FALSE) {
		$q = $conn->prepare("DELETE FROM {$db_prefix}RELATIONS WHERE file_id = ? AND user_id != ?;");
		$q->bind_param("ss", $targetFile, $user);
		$q->execute();
		$q->close();
		//Owner permissions should be present
	}

	if ($newPerm == "custom"){

		//Delete old access
		$q = $conn->prepare("DELETE FROM {$db_prefix}RELATIONS WHERE file_id = ? AND user_id != ?;");
		$q->bind_param("ss", $targetFile, $user);
		$q->execute();
		$q->close();


		//Grant new access to users
		$users = explode(";", $newPermUsers);
		foreach ($users as $key => $val) {
			$users[$key] = trim($val);
			//Search for the user
			$uid = db_get_user_id($users[$key]);
			if ($uid != FALSE)
			{
				db_add_relation($targetFile, $uid, $rel_map['read']);
			}
		}
	}
}

function db_update_user_info($user, $newName){
	global $use_db_storage;
	if (!$use_db_storage){
		return;
	}
	$conn = db_get_connection();
	if(is_null($conn)){
		throw new Exception("Database connection failed!");
	}
	global $db_prefix;
	$q = $conn->prepare("UPDATE {$db_prefix}USERS SET name = ? WHERE id = ?");
	$q->bind_param("ss", $newName, $user);
	$q->execute();
	error_log($q->error);
}
?>
