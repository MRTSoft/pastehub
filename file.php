<?php
	//Presentation and rendering of the retreived file
	if(!isset($_SESSION)) 
    { 
        session_start(); 
    }

	require_once(dirname(__FILE__) . '/config/languages.php');
	require_once(dirname(__FILE__) . '/api/storage_module.php');
	require_once(dirname(__FILE__) . '/api/id_module.php');

function return_not_found(){
	http_response_code(404);
	global $language;
	global $lanDB;
	include(dirname(__FILE__) . "/404.php");
	die();
}	
	
	if (!isset($_SESSION['language'])){
		$language = $lanDB['en'];
	} else {
		$language = $lanDB[$_SESSION['language']];
	}


	//Load the file
	$file = null;
	try{
		if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
			$id = $_GET['id'];
			$path = NULL;
			$uid = NULL;
			global $use_db_storage;
			$uid = NULL;
			if (isset($_SESSION['uid'])){
				$uid = $_SESSION['uid'];
			}
			
			if (!is_valid_id($id)){
				return_not_found();
			}
			
			if (isset($use_db_storage) and $use_db_storage){
				$path = db_get_file_path_by_id($id, $uid); 
			} else {
				$path = get_file_path_by_id($id);
			}
			if (is_null($path)){
				return_not_found();
			}
			$file = json_decode('{}');
			$file->content = get_file_content_by_id($id);
			$file->meta = get_file_metadata_by_id($id);
		}
		else {
			return_not_found();
		}
	} catch (Exception $e){
		return_not_found();
		error_log($e);
	}
?>

<!DOCTYPE html>
<html>
<head>
	<title><?php echo htmlspecialchars($file->meta->title) ?></title>
	<link rel="stylesheet" type="text/css" href="css/style-main.css">
	<link rel="stylesheet" type="text/css" href="css/style-responsive.css">
	<script type="text/javascript" src="js/script.js"></script>
	<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
	<?php include_once("header.php") ?>

<?php if (isset($_GET['show_link'])){ ?>
	<!-- Info message place -->
	<section class="row">
		<span class="col-1 filler">&nbsp;</span>
		<span class="col-10 info row">
				<span class="closebtn" 
				onclick="this.parentElement.parentElement.style.display='none';">
					&times;
				</span>
				<label class="col-2" style="margin:10px; padding: 5px; font-weight: bold;">
					<?php echo $language['your_link']; ?>
				</label>
				<input type="text" readonly="readonly" value="<?php 
					echo "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . "?";
					echo "id=$id";
				?>" onclick="select()" style="text-align:center; background-color:lightgray; padding: 4px; border: 1px solid #F4F5F7; background-color: #FAFBFC; color: #172B4D;" class="col-2">
		</span>
		<span class="col-1 filler">&nbsp;</span>	
	</section>
	<?php } ?>

<section class="row">
	<span class="col-2 filler">&nbsp;</span>
	<h1 class="col-4"><?php echo $file->meta->title; ?></h1>
	
	<?php
	$display_delete_btn = FALSE;
	if (isset($_SESSION['uid'])){
		if (isset($file->meta->owner) && !is_null($file->meta->owner)){
			if ($file->meta->owner === $_SESSION['uid']){
				$display_delete_btn = TRUE;
			}
		}
	}
	if ($display_delete_btn){ ?>
		<button class="col-1 alert" onclick="tryRemoveFile('<?php echo $id; ?>')">
			<h3>
				<?php echo $language['delete_action']; ?>
			</h3>
		</button>
	<?php 
	} else { ?>
		<span class="col-1 filler">&nbsp;</span>
	<?php 
	}	?>

	
	<a href="./api/get.php?id=<?php echo $id; ?>">
		<button class="col-1 success">
			<h3>TXT</h3>
		</button>
	</a>
	<a href="./api/get.php?id=<?php echo $id; ?>&type=json">
		<button class="col-1 success">
			<h3>JSON</h3>
		</button>
	</a>
	<a href="./api/get.php?id=<?php echo $id; ?>&type=xml">
		<button class="col-1 success">
			<h3>XML</h3>
		</button>
	</a>
	<span class="col-2 filler">&nbsp;</span>
</section>
<section class="row">
	<span class="col-1 filler">&nbsp;</span>
<table id="content" class="col-10 no-hover">
	<tr class="row">
		<td class="col-05 center-text">
			<pre class="line-numbers">
<?php	$lines = substr_count( $file->content, "\n");
	for($i = 1; $i <= $lines+1; ++$i){?>
<a name="ln<?php echo $i; ?>" href="#ln<?php echo $i; ?>"><?php echo $i; ?></a><?php 
echo '<br/>';} ?>
			</pre>
		</td>
		<td class="col-115">
<pre><?php echo htmlspecialchars($file->content) ?></pre>
		</td>
	</tr>
</table>
	<span class="col-1 filler">&nbsp;</span>
</section>


<section class="row">
	<span class="col-2 filler">&nbsp;</span>
	<article class="row col-8">
		<span class="row">
			<h1 class="col-12 center-text">Metadata</h1>
		</span>
	<table class="row col-12">
		<thead>
			<tr class="row col-12 table-row">
				<th class="meta-key col-4">Meta key</th>
				<th class="meta-value col-8">Meta value</th>
			</tr>
		</thead>
		<?php 
		foreach ($file->meta as $key => $value) {
	?>
		<tr class="row col-12">
			<td class="meta-key col-4 table-col2"><?php echo htmlspecialchars($key); ?></td>
			<td class="meta-value col-8 table-col2">
				<?php 
				do{
					if ($key == 'expire_date'){
						$date = new DateTime();
						$date->setTimestamp($value);
						echo $date->format("d/m/Y");
						break;
					}
					echo htmlspecialchars($value);
				} while (FALSE); 
				?>		
			</td>
		</tr>
	<?php	} ?>
	</table>
	
	</article>
	<span class="col-2 filler">&nbsp;</span>
</section>

<?php include_once("footer.php") ?>

</body>
</html>
