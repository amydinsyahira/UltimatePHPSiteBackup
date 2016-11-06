<?php
/*
 * Ultimate PHP Site Backup
 *
 * This php page accepts user input, and then calls functions outputs results.
 * It is used by the AJAX loader present on index.php
 */

//The manage class loads all other necessary classes as well, such as db and smarty
require_once('ctrls/manage.class.php');
$manage = new manage;

//User just submitted request to login
if(isset($_GET['login'])) {
	$manage->login($_POST['train']);

	if ($manage->is_logged_in()) {
		
		$manage->checkLatestVersion();
		
		if (!empty($_GET['goto'])) {
			header("Location: $_GET[goto]");
			echo'<script type="text/javascript"><!-- window.location = "'.$_GET[goto].'" //--></script>';
			die;
		}
		else {
			header("Location: index.php");
			echo'<script type="text/javascript"><!-- window.location = "index.php" //--></script>';
			die;
		}
	}
	else {
		$_SESSION['ermsgs'][] = 'There was an error logging you in.';
		header('Location: login.php');
		die;
	}
}

if(isset($_GET['logout'])) {
	$manage->logout();

	if (!isset($_SESSION['logged_in'])) {
		$_SESSION['gmsgs'][] = 'You have succesfully logged out.';
	}
	else $_SESSION['ermsgs'][] = 'There was an error logging you out.';

	header('Location: login.php');
	die;
}


//If not logged in, redirect to login.php
$manage->checkLoginAndDirect();

//Request to check for new version
if (isset($_GET['checkversion'])){
	if ($manage->checkLatestVersion()) $_SESSION['gmsgs'][] = "Ultimate PHP Site Backup is up to date!";
	header("Location: index.php");
	die;
}


//Test database connection
if (isset($_GET['testdb'])){
	
	if (get_magic_quotes_gpc()) $_GET['testdb'] = stripslashes($_GET['testdb']);
	
	$db = $manage->extractMysqlSettings($_GET['testdb'], true);
	
	if (is_array($db)){
		if ($manage->db->testMysqlSettings($db[0], $db[1], $db[2], $db[3], true)){
			$_SESSION['gmsgs'][] = "Connected to '$db[0]', using database '$db[3]' successfully.";
		}
	}
	
	$manage->displayMessagesWithSmarty();
	die;
}

//If the user just submitted a request to run a backup set
if (isset($_GET['runbcs'])) {
	if (!get_magic_quotes_gpc()) {
		$id = addslashes($_GET['runbcs']);
	}
	else $id = $_GET['runbcs'];

	$manage->runBcS($id);

	$manage->displayMessagesWithSmarty();
}

//If the user just submitted a form for a new backup set (no AJAX)
if (isset($_GET['addbcs'])) {
	$manage->addBcS($_POST['name'], $_POST['fileList'], $_POST['dbs'], $_POST['schedule'], $_POST['notify'], $_POST['email_backup'], $_POST['ftp_upload'], $_POST['store_local']);
	header('Location: index.php');
	die;
}

//If the user just submitted a request to edit a backup set (no AJAX)
if (isset($_GET['editbcs'])) {
	if ($manage->db->editBcSEntry($_POST['id'], $_POST['name'], $_POST['schedule'], $_POST['notify'], $_POST['email_backup'], $_POST['ftp_upload'], $_POST['store_local'])) {
		$_SESSION['gmsgs'][] = 'Edited backup set succesfully.';
	}
	else $_SESSION['ermsgs'][] = 'Error editing backup set.';
	header('Location: index.php');
	die;
}

//If the user just submitted a request to run a backup set
if (isset($_GET['delbcs'])) {
	if (!get_magic_quotes_gpc()) {
		$id = addslashes($_GET['delbcs']);
	}
	else $id = $_GET['delbcs'];

	if($manage->db->deleteBcS($id)) {
		$_SESSION['gmsgs'][] = "Backup set removed. You can still download the zips that were previously created for that set - just go to the local backup storage folder.";
	}
	else $_SESSION['ermsgs'][] = "Error removing backup set. ".mysql_error();

	$manage->displayMessagesWithSmarty();
}

//User wants to get a list of backups of particular backup set
if (isset($_GET['bcsruns'])) {
	$runs = $manage->db->getBcSRuns($_GET['bcsruns']);
	$BcS = $manage->db->getBcSInfo($_GET['bcsruns']);
	
	if ($BcS['email_backup'] == 1) echo "<h3><img src=\"models/images/mail-attachment.png\" title=\"Backups sent as email attachments\"/>Email attachments sent to ".$manage->db->settings[ADMIN_EMAIL]."</h3>";
	if ($BcS['ftp_upload'] == 1) echo "<h3><img src=\"models/images/network.png\" title=\"Store on remote FTP server\"/>Remote FTP Server upload: Enabled</h3>";
	if ($BcS['store_local'] == 0) echo "<h3><img src=\"models/images/nolocal.png\" title=\"Not stored on local drive\"/>Your backups are NOT stored locally.</h3>";
	
	if (empty($runs) || !$runs) echo "This backup set has never been executed. Try running it now.";
	else {
		echo "<div id=\"$_GET[bcsruns]_msgs\"></div>";
		echo "<h2>$BcS[name] Backups</h2>";
		foreach ($runs as $r) {
			echo "<span id=\"$r[id]\" class=\"backupfile\"><br/>".$manage->getRelativeDate(strtotime($r[time]),'fulldate')." | <a href=\"do.php?download=$r[zipfile]\" target=\"_blank\">Download</a> | <a href=\"do.php?goto=$r[logfile]\" target=\"_blank\">View log</a> | <a class=\"pointer\" onclick=\"doDelRun('$r[id]','$r[bset]');return false;\">Delete</a></span>";
		}
	}
	
	
	echo "<p>";
	if (!empty($BcS[files])) echo "<b>Files:</b><br/>$BcS[files]<br/><br/>";
	if (!empty($BcS[dbs])) {
		echo "<b>Databases</b><br/>";
		foreach (explode(",", $BcS[dbs]) as $db) {
			$dbar = $manage->extractMysqlSettings($db);
			if (is_array($dbar)){
				echo $dbar[3]." (located on mysql server: $dbar[0])<br/>";
			}
			else echo $db.",";
		}
		echo "<br/>";
	}
	echo "</p>";
}

//If the user just submitted a request to run a backup set
if (isset($_GET['delrun'])) {

	$id = $_GET['delrun'];

	if($manage->db->deleteRunEntry($id)) {
		$_SESSION['msgs'][] = "Backup removed.";
	}
	else $_SESSION['ermsgs'][] = "Error removing backup. ".mysql_error();

	$manage->displayMessagesWithSmarty();
}

//If the user submitted a request to update password
if (isset($_GET['pwconfig'])) {
	if (!DEMO) {
		if ($manage->login($_POST[currentpassword])){
			if ($_POST[newpassword] === $_POST[confirmpassword]){
				if($manage->db->changePassword($_POST[currentpassword],$_POST[newpassword])){
					$_SESSION['gmsgs'][] = "Password changed.";
					$manage->login($_POST[newpassword]);
				}
			}
			else $_SESSION['ermsgs'][] = "The new passwords you typed did not match. Please try again.";
		}
		else $_SESSION['ermsgs'][] = "Please type your current password correctly.";

		header("Location: index.php");
		die;
	}
	else {
		$_SESSION['ermsgs'][] = "Password changes have been disabled for this demo version.";
		header("Location: index.php");
		die;
	}
}

//Ajax request to test and update FTP settings
if (isset($_GET['ftpconfig'])) {
	$ftpconfigstring = "ftp://$_POST[ftphost]/$_POST[ftpdir]--&Login--$_POST[ftpusername]:$_POST[ftppass]";
	
	if ($manage->uploadViaFTP("testFtp.txt","testFtp.txt",$ftpconfigstring)) {
		$_SESSION['gmsgs'][] = "Successfully connected using your FTP settings. You may want to check for the file 'testFtp.txt' in the FTP folder you specified to make sure it worked.";
		
		if (!$manage->db->changeFTPConfig($ftpconfigstring)) $_SESSION['ermsgs'][] = "Failed to update your FTP settings for some reason";
	}
	else $_SESSION['ermsgs'][] = "Failed to connect by FTP using the settings you specified.";
	
	$manage->displayMessagesWithSmarty();
	die;
}

//If the user just submitted a request to download a backup zip
if (isset($_GET['download'])) {
	if (DEMO) {
		$_SESSION['ermsgs'][] = "Downloading backups have been disabled for this demo. Please purchase UPSB";
		header("Location: index.php");
		die;
	}
	else {
		if (!get_magic_quotes_gpc()) {
			$file_name = addslashes($_GET['download']);
		}
		else $file_name = $_GET['download'];


		// make sure it's a file before doing anything!
		if(is_file($file_name)) {

			// required for IE
			if(ini_get('zlib.output_compression')) { ini_set('zlib.output_compression', 'Off');	}

			// get the file mime type using the file extension
			switch(strtolower(substr(strrchr($file_name,'.'),1))) {
				case 'pdf': $mime = 'application/pdf'; break;
				case 'zip': $mime = 'application/zip'; break;
				case 'jpeg':
				case 'jpg': $mime = 'image/jpg'; break;
				default: $mime = 'application/force-download';
			}
			header('Pragma: public'); 	// required
			header('Expires: 0');		// no cache
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Last-Modified: '.gmdate ('D, d M Y H:i:s', filemtime ($file_name)).' GMT');
			header('Cache-Control: private',false);
			header('Content-Type: '.$mime);
			header('Content-Disposition: attachment; filename="'.basename($file_name).'"');
			header('Content-Transfer-Encoding: binary');
			header('Content-Length: '.filesize($file_name));	// provide file size
			header('Connection: close');
			readfile($file_name);		// push it out
			exit();
		}
		else echo "No such file exists to download. It may be stored on your remote FTP server, or sent as an email attachment, based on your settings.";
	}
}

if (isset($_GET['goto'])) {
	header("Location: $_GET[goto]");
}
?>
