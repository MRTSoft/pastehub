<!-- Common page header -->
<header class="row">
	<span class="row filler"><span class="col-12 filler">&nbsp;</span></span>
	<span class="row">
		<span class="col-1 filler">&nbsp;</span>
		<h1 class="col-5"><a href="index.php">PasteHub</a></h1>
		<h2 class="right-text col-5"><?php echo $language['subtitle'];?></h2>
		<span class="col-1 filler">&nbsp;</span>
	</span>
</header>
<section id="user-bar" class="row">
	<span class="col-1 filler">&nbsp;</span>
	<?php if (!isset($hide_login_area)) { ?>
	<?php if (!isset($_SESSION['uid'])) { ?>
	<p class="col-3"><a href="login.php">Log in</a></p>
	<?php
	} else { 
	?>
	<p class="col-3"><a href="dashboard.php"><?php echo $_SESSION['uname']; ?></a>
	<a href="#" onclick="logout();">Log out</a></p>
	<?php } }
	else { ?>
	<span class="col-3 filler">&nbsp;</span>
	<?php } ?>
	<span class="col-4 filler">&nbsp;</span>
	<p class="col-3 right-text">
	<?php echo $language['available_languages'];
	$shouldPipe = false;
	foreach ($lanDB as $lanAbbrev => $dict) { ?>
		<?php if ($shouldPipe) {
			echo " | ";
		} else { 
			$shouldPipe = TRUE;
		} ?>
		<span class="link" onclick="changeLanguage('<?php echo $lanAbbrev ?>')" >
			<?php echo strtoupper($lanAbbrev); ?>		
		</span>
	<?php
	}?>
	</p>
	<span class="col-1 filler">&nbsp;</span>
</section>