<?php
	if(!isset($_SESSION)) 
    { 
        session_start(); 
    }
	include_once(dirname(__FILE__) . "/api/user_module.php");
	include_once(dirname(__FILE__) . "/api/storage_module.php");
	require_once(dirname(__FILE__) . "/config/languages.php");
	
	if (!isset($_SESSION['uid'])){
		header("Location: index.php");
		die('<meta http-equiv="refresh" content="0; url=index.php" />');
	}

	if (!isset($_SESSION['language'])){
		$language = $lanDB['en'];
	} else {
		$language = $lanDB[$_SESSION['language']];
	}

?>

<!DOCTYPE html>
<html>
<head>
	<title>User Dashboard</title>
	<script type="text/javascript" src="js/script.js"></script>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="css/style-main.css">
	<link rel="stylesheet" type="text/css" href="css/style-responsive.css">
</head>
<body>
	<?php 
		include_once(dirname(__FILE__) . "/header.php");
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
		<span class="col-2 filler">&nbsp;</span>
		<span class="col-8">
			<form method="POST">
				<input type="hidden" name="update-name">
				<p class="row">
					<h1 class="col-12 center-text">Update your name:</h1>
				</p>
				<p class="row">
					<input class="col-8" type="text" placeholder="New Name" name="new-name">
					<span class="col-1 filler">&nbsp;</span>
					<input  class="col-2" type="submit" name="submit" value="Rename" />
				</p>
			</form>
		</span>
		<span class="col-2 filler">&nbsp;</span>
	</section>
	<hr>

	<section class="row">
		<span class="col-1 filler">&nbsp;</span>
		<h1 class="col-10 center-text">Files</h1>
		<span class="col-1 filler">&nbsp;</span>
	</section>

	
	<section class="row">
		<span class="col-1 filler">&nbsp;</span>
		<table class="col-10">
			<!-- TODO Add language support for this -->
			<tr>
				<th> Link </th> <th> Options </th> <th> <?php echo $language['permissions']; ?> </th>
			</tr>
			<?php
	$user = $_SESSION['uid'];
	$path = get_user_path($user);
	$path .= 'files/';
	if ($_SERVER['REQUEST_METHOD'] == 'POST'){
		//Handle permission change
		if (isset($_POST['permission']) and isset($_POST['allowed-users']))
		{
			$newPerm = $_POST['permission'];
			$newPermUsers = $_POST['allowed-users'];
			$targetFile = $_POST['file-id'];
			db_update_permissions($targetFile, $user, $newPerm, $newPermUsers);
		} else {
			if (isset($_POST['update-name']) and isset($_POST['new-name'])){
				db_update_user_info($user, $_POST['new-name']);
				$_SESSION['uname'] = $_POST['new-name'];
			}
		}
	}
	//Check if the folder actually exist
	if (!is_dir($path)){
		if (!mkdir($path, 0777, TRUE)) {
            error_log("Failed to create the user's files folder. Path is $path");
        }
	}
	error_log($path);
	//$it = new FileSystemIterator($path, FilesystemIterator::SKIP_DOTS);
	$files = db_get_owned_files($user);//Array of file id's
	//foreach ($it as $fileInfo) {
	foreach ($files as $fid) {
			//$fid = $fileInfo->getFilename();
			?>
			<tr id="<?php echo $fid; ?>">
				<td class="center-text">
				<a href="file.php?id=<?php echo $fid; ?>"> <?php echo $fid; ?> </a></td> 
				<td class="center-text row">
					<button class="alert col-12" onclick="removeFile(this, '<?php echo $fid; ?>');">
						<?php echo $language['delete_action']; ?>
					</button>
				</td>
				<td class="center-text row" style="padding: 0;">
                    <!-- TODO Add language support-->
				<form method="POST" action="">
					<?php
						$perm = db_get_permission_type($user,$fid);
					?>
					<span class="col-2" style="padding: 0;">
						<input type="hidden" name="file-id" value="<?php echo $fid; ?>"/>
						<label>
							<input type="radio" value="public" name="permission" <?php if ($perm['public']) echo "checked"; ?>>
							<?php echo $language['permissions_public'] ?>
						</label>

					</span>
					<span class="col-2" style="padding: 0;">
						<label>
							<input type="radio" value="private" name="permission" <?php if ($perm['private']) echo "checked"; ?>> 
							<?php echo $language['permissions_private'] ?>
						</label>
					</span>
					<span class="col-2" style="padding: 0;">
						<label>
							<input type="radio" value="custom" name="permission" id="r3-<?php echo $fid; ?>" <?php if (strlen($perm['custom'])) echo "checked"; ?>> 
							<?php echo $language['permissions_custom'] ?>
						</label>
					</span>
					<span class="col-4" style="padding: 0;">
						<input type="text" name="allowed-users" placeholder="user1@mail.com; user2@mail.com ... " style="padding:0; width:90%;"
							value="<?php if (strlen($perm['custom'])) echo $perm['custom']; ?>" onclick="document.getElementById('r3-<?php echo $fid; ?>').checked = true;">
					</span>

					<span class="col-2" style="padding: 0;">
						<input type="submit" value="Set" style="padding: 5px; margin:0; width:90%" class="info">
					</span>
				</form>
				</td>
			</tr>

			<?php } ?>
		</table>
		<span class="col-1 filler">&nbsp;</span>
	</section>

	<style type="text/css">
		.alert {
			border-color: red;
			font-weight: bold;
			color: black;
			border-style: solid;
		}

		.alert:hover {
			background-color: red;
		}

		tr:hover {
			background-color: inherit;
		}

		tr:nth-child(even):hover {
    	background-color: #f2f2f2;
		}

		a {
			color: black;
		}

		button {
			cursor: pointer;
		}
	</style>

	<?php
		include_once(dirname(__FILE__) . "/footer.php");
	?>
</body>
</html>
