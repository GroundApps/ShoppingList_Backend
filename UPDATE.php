<?php
ob_implicit_flush(true);
ob_end_flush();

include "CONSTANTS.php";
if(ISSET($_GET['debug'])){
	error_reporting(~0); ini_set('display_errors', 1);
	echo "<title>DEBUG</title>";
}
if(ISSET($_POST['update'])){
	update($_POST['zipURL'], $_POST['newVersion']);
} else {
	status();	
}

function echoHead(){
echo <<<HEAD
 <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	</head>
HEAD;
}
function echoStyle(){
<<<STYLE
	<style>
	#message {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
	}
	#inner-message {
    margin: 0 auto;
	}
	</style>
STYLE;
echo <<<BOOTSTRAP
<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
BOOTSTRAP;
}

function echoPageStart(){
echo <<<PAGE_START
	<!DOCTYPE html><html>
PAGE_START;
}

function echoPageEnd(){
echo <<<PAGE_END
	</html>
PAGE_END;
}

function echoUpdateAvailable($curVersion, $newVersion, $updateText, $zipURL, $disabled){
	echo <<<STATUS
	<center>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Update Available - ShoppingList Backend</h3>
			</div>
			<div class="panel-body">
				<b>Release Note: $updateText</b><br><br>
				Download URL: $zipURL
			</div>
			<form class="form-inline" action="UPDATE.php" method="post">
				<input type="hidden" name="zipURL" value="$zipURL">
				<input type="hidden" name="newVersion" value="$newVersion">
				<button id="updateButton" class="btn btn-lg btn-info" type="submit" name="update" $disabled>Update to version $newVersion</button>
			</form>
		</div>
	</center>
STATUS;
}

function echoAllGood(){
	echo <<<ALLGOOD
	<center>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">ShoppingList Backend</h3>
			</div>
			<div class="panel-body">
				<b>Congratulations!</b><br>
				Everything is up to date.
			</div>
		</div>
	</center>
ALLGOOD;
}

function echoChecklist($newVersion, $zipURL){
echo <<<CHECKLIST
<center>
	<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Updating backend to version: $newVersion</h3>
			</div>
			<div class="panel-body">
					Downloading from: $zipURL<br><br>
					<input id="cb_download" type="checkbox" disabled="">
					<label for="cb_download" id="progressDownload">Download (0%)</label><br>
					<input id="cb_extract" type="checkbox" disabled="">
					<label for="cb_extract"> Extract </label><br>
					<input id="cb_backup" type="checkbox" disabled="">
					<label for="cb_backup"> Create Backup </label><br>
					<input id="cb_filemove" type="checkbox" disabled="">
					<label for="cb_filemove" id="progressCopy"> Copy Files</label><br>
					<input id="cb_databaseupdate" type="checkbox" disabled="">
					<label id="databaseupdateLabel" for="cb_databaseupdate"> Update Database</label><br>
			</div>
		</div>
	</div>
</center>
CHECKLIST;
}

function echoJavascript(){
echo <<<JS
	<script type="text/javascript">
	var progressDownloadElement = document.getElementById('progressDownload')
	var progressElement = document.getElementById('progressDownload')
	var downloadElement = document.getElementById('cb_download')
	var extractElement = document.getElementById('cb_extract')
	var backupElement = document.getElementById('cb_backup')
	var filemoveElement = document.getElementById('cb_filemove')
	var databaseElement = document.getElementById('cb_databaseupdate')
	var databaseLabel = document.getElementById('databaseupdateLabel')
	
	function updateProgress(percentage) {
		progressElement.innerHTML = 'Download (' + percentage + '%' + ')';
	}
	function updateDownload(status) {
		downloadElement.checked = status;
	}
	function updateExtract(status) {
		extractElement.checked = status;
	}
	function updateBackup(status) {
		backupElement.checked = status;
	}
	function updateFilemove(status) {
		filemoveElement.checked = status;
	}
	function updateDatabase(status) {
		databaseElement.checked = status;
	}
	function updateDatabaseNotNecessary() {
		databaseLabel.style.setProperty("text-decoration", "line-through");
	}
	
	</script>
JS;
}

function status(){
	$githubData = getGithubData();
	echoHead();
	echoPageStart();
	echoStyle();
	echoJavascript();
	if($githubData['version'] == 0){
		echoError('Could not connect to GitHub API.');
		die();
	}
	if($githubData['version'] == BACKEND_VERSION){
		$updateDisabled = permissionCheck();
		echoUpdateAvailable(BACKEND_VERSION, $githubData['version'], $githubData['releasenote'], $githubData['zipurl'], $updateDisabled);
	} else {
		echoAllGood();
	}
	echoPageEnd();
}

function update($zipURL, $newVersion){
	echoPageStart();
	echoHead();
	echoStyle();
	echoChecklist($newVersion, $zipURL);
	echoJavascript();
	$zipFILE = downloadUpdate($zipURL);
	unzipDownloadedUpdate($zipFILE);
	backup();
	copyNewFiles();
	updateDatabase();
	echoPageEnd();
}

function echoSuccess($message){
echo <<<SUCCESS
	<div id="message">
		<div style="padding: 5px;" >
			<div id="inner-message" class="alert alert-success" role="alert">
				<strong>Yeah!</strong>
				$message
			</div>
		</div>
	</div>
SUCCESS;
}

function echoError($errorMessage){
echo <<<ERROR
	<div id="message">
		<div style="padding: 5px;" >
			<div id="inner-message" class="alert alert-danger" role="alert">
				<strong>Oh no!</strong>
				An error occured:<br>
				$errorMessage
			</div>
		</div>
	</div>
ERROR;

}

function permissionCheck(){
	if (!is_writable(__DIR__)){
		$user = get_current_user();
		$dir = __DIR__;
		echoError('No write permission for user "'.$user.'" for directory "'. $dir.'"');
		return "disabled";
	}
}

function downloadUpdate($zipurl){
	$ch = curl_init() or die('Sorry cURL is not installed!');
	$zipfile = __DIR__.'/update_'.$tag.'.zip';
	$fp = fopen ($zipfile, 'w+');
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $zipurl);
	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch,CURLOPT_NOPROGRESS,false);
	curl_setopt($ch,CURLOPT_PROGRESSFUNCTION,'progress'); 
	curl_exec($ch);
	curl_close($ch);
	echo '<script>updateDownload(true);</script>';
	return $zipfile;
	if(!file_exists($zipfile)){
		echoError('Could not download file from GitHub!');
	}
}

function unzipDownloadedUpdate($zipfile){
	$zip = new ZipArchive;
	$res = $zip->open($zipfile);
	if ($res === TRUE) {
		$zip->extractTo(__DIR__);
		$zip->close();
		echo '<script>updateExtract(true);</script>';
		unlink($zipfile);
	} else {
		die ('Could not extract file!');
	}
}

function backup(){
	$backupFolder = __DIR__.'/backup/';
	delDir($backupFolder);
	if(!mkdir($backupFolder, 0770)){
			echoError('Could not create backup folder!');
			exit;
	}
		$iteratorBackup = new DirectoryIterator(__DIR__);
		foreach ($iteratorBackup as $backupFile) {
			$backupFileName = $backupFile->getFilename();
			//REMOVE CHECK FOR update.sql FOR RELEASE, CURRENTLY USED BECAUSE ZIP FROM GITHUB HAS NONE AND IT WOULD BE DELETED
			if ($backupFileName != '.' && $backupFileName != '..' && $backupFileName != 'config.php' && $backupFileName != 'update.sql' && $backupFileName != '.htaccess' && !$backupFile->isDir() && $backupFileName[0] != '.'){
				if(!rename($backupFile->getPathname(), $backupFolder.$backupFile->getFilename())){
					die ('Could not backup files!');
				}
			}
		}
	echo '<script>updateBackup(true);</script>';
}

function copyNewFiles(){
	$iterator = new DirectoryIterator(__DIR__);
		foreach ($iterator as $fileinfo) {
			if ($fileinfo->isDir() && strlen(strstr($fileinfo->getFilename(),"GroundApps-ShoppingList_Backend"))>0) {	
				$iteratorUpdateDir = new DirectoryIterator($fileinfo->getPathname());
				foreach ($iteratorUpdateDir as $updateFile) {
					$updateFileName = $updateFile->getFilename();
					if ($updateFileName != '.' && $updateFileName != '..' && $updateFileName != 'config.php' && $updateFileName != '.htaccess'){
						rename($updateFile->getPathname(), __DIR__ .'/'.$updateFileName);
						echo '<script>updateFilemove("'.$updateFileName.'");</script>';
					}
				}
				delDir($fileinfo->getPathname());
			}
		}
	echo '<script>updateFilemove(true);</script>';
}

function getGithubData(){
	$ch = curl_init() or die('Sorry cURL is not installed!');
	$url = 'https://api.github.com/repos/GroundApps/ShoppingList_Backend/releases/latest';
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL,$url);
	$agent = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.0';
	curl_setopt($ch,CURLOPT_HTTPHEADER,array('User-Agent: '.$agent));
	$result = curl_exec($ch);
	curl_close($ch);
	$obj = json_decode($result);
	$tag = $obj->tag_name;
	$zipurl = $obj->zipball_url;
	$releasenote = $obj->body;
	//$tag = "v1.1";
	$zipurl = 'https://api.github.com/repos/GroundApps/ShoppingList_Backend/zipball/v1';
	return array(
		"version" => substr($tag,1),
		"zipurl" => $zipurl,
		"releasenote" => $releasenote,
	);
}

function updateDatabase(){
	include('config.php');
	//IMPLEMENT DATABASE ALTERATION
	// WITH PDO
	$updateFILE = __DIR__."/update.sql";
	if(!file_exists($updateFILE)){
		echo '<script>updateDatabaseNotNecessary();</script>';
	} else {
		$lines = file($updateFILE);
		foreach ($lines as $line){
			echo $line.'<br>';
			//SQL FOR EACH LINE IN update.sql
		}
	echo '<script>updateDatabase(true);</script>';
	}
	echoSuccess('Update done! Please check if everything works and then delete the backup folder.');
}

function progress($clientp,$dltotal,$dlnow,$ultotal,$ulnow){
	if ($dltotal > 0){
		echo '<script>updateProgress('.round(($dlnow * 100) / $dltotal).');</script>';
	}
		return(0);
	} 

function delDir($dir) {
   if (is_dir($dir)) {
     $objects = scandir($dir);
     foreach ($objects as $object) {
       if ($object != "." && $object != "..") {
         if (filetype($dir."/".$object) == "dir") delDir($dir."/".$object); else unlink($dir."/".$object);
       }
     }
     reset($objects);
     rmdir($dir);
   }
}

?>
