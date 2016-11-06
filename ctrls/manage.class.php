<?php
/*
Ultimate PHP Site Backup
Author: Isa Hassen
Project Start Date: Mar. 04, 2010

Class: Manage
Description: This class does pretty much anything the software needs to do.
*/

$root = dirname(dirname(__FILE__));

//Loads the database class
require_once "$root/ctrls/db.class.php";

//Loads the Smarty template engine and sets some settings
require_once "$root/ctrls/libs/smarty/Smarty.class.php";
$smarty = new Smarty;
$smarty->compile_check = true;
$smarty->debugging = false;
$smarty->template_dir = $root.'/models/templates';
$smarty->compile_dir = $root.'/models/templates_c';
$smarty->config_dir = $root.'/models/configs';

//Define global constants
define("CUR_VERSION",2.0);

class manage {

	var $db;

	public function __construct() {
		$this->db = new db;

		session_start();
	}

	/**
	 * This method checks some for some security issues and sets $_SESSION errors in case
	 * issues exist.
	 *
	 * @return $_SESSION errors
	 *
	 */
	public function checkSecurity() {

		if (sha1("upsbadmin".$this->db->settings[SALT]) == trim($this->db->settings[ADMIN_PW])){
			$_SESSION['msgs'][] = "Your password has not been changed from the default phrase. Please change your password now (click Settings).";
		}

		if ($this->db->settings[DISABLE_LOCKDOWN] == true || trim($this->db->settings[DISABLE_LOCKDOWN]) == "true") {
			$_SESSION['ermsgs'][] = "This software's lockdown mode is disabled. It is now susceptible to brute force attacks. Please enable lockdown again by editing config.ini.php";
		}

		if (file_exists($GLOBALS['root']."/install.php")) {
			@unlink ($GLOBALS['root']."/install.php");
			$_SESSION['gmsgs'][] = "Welcome to Ultimate PHP Site Backup. <br/>I applaud you for choosing my software, and I hope you find it useful!";
		}

		$cronlog = parse_ini_file($GLOBALS['root']."/backup_logs/cronlog.txt");

		//if cron.php is not getting executed atleast once every 35 minutes
		if ($cronlog['CRON_LAST_RUN'] < 2100) {
			$_SESSION['ermsgs'][] = "I have detected that <a href=\"cron.php\">cron.php</a> has not been executed in the past 30 minutes.";
			$_SESSION['ermsgs'][] = "You need to make sure that your server is configured to execute cron.php atleast once every 30 minutes - or else backups will not run according to schedules. See help for more info.";
		}
	}

	/**
	 * This is method login
	 *
	 * @param string $password This is the password you want to login with
	 * @return bool
	 *
	 */
	public function login($password) {
		if ($_SESSION['tries'] < 6 || $this->db->settings[DISABLE_LOCKDOWN] == true) {//Try to prevent brute forcing
			if (sha1(trim($password).$this->db->settings[SALT]) == trim($this->db->settings[ADMIN_PW])) {
				if (session_start()) {
					$_SESSION['logged_in'] = md5($this->db->settings[ADMIN_PW]); //used for checking if the user is logged in or not
					return true;
				}
			}
			else {
				return false;

				$_SESSION['msg'] = "Buddy, nice try, but wrong password.";

				//This is a crude little system here which determines the number of tries.
				if (isset($_SESSION['tries'])) {
					$_SESSION['tries']++;
				}
				else $_SESSION['tries'] = 1;
			}
		}
		else $_SESSION['msg'] = "You are trying to login too much. <b>THIS SOFTWARE IS NOW IN LOCKDOWN MODE. YOU CANNOT ENTER.</b> <br><br> If you are a legit admin, edit the config.ini file, scroll down to the bottom, and edit the appropriate settings to disable this lockdown.";
	}

	public function is_logged_in() {
		if ($_SESSION['logged_in'] === md5($this->db->settings[ADMIN_PW])) {
			return true;
		}
		else return false;
	}

	public function checkLoginAndDirect() {
		//If user is not logged in, directs to login.php
		if (!$this->is_logged_in()) {
			//Tries both PHP and JS redirects or dies
			header("Location: login.php?goAfterLogin=$_SERVER[REQUEST_URI]");
			echo'<script type="text/javascript"><!-- window.location = "login.php?go='.$_SERVER[REQUEST_URI].'" //--></script>';
			die ("I would love to serve you the current page, but unfortunately you are not logged in, my friend.");
		}
	}

	public function logout() {
		$_SESSION['logged_in'] = false;
		unset($_SESSION['logged_in']);
		session_destroy(); //Destroy all session vars
		session_start(); //Start new session (for storing info about new login)
		$this->checkLatestVersion();
	}
	
	public function checkLatestVersion(){
		$ch = @curl_init();
		$versionCheckURL = "http://www.phpsitebackup.com/latestversion.txt";
		
		if ($ch){
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_URL, $versionCheckURL);
			$latestver = curl_exec($ch);
			curl_close($ch);
			
			if (floatval($latestver) > floatval(CUR_VERSION)){
				$_SESSION['msgs'][] = "A newer version of UPSB ($latestver) is now available. Please <a href=\"http://codecanyon.net/item/ultimate-php-site-backup-v10/96705\" target=\"_blank\">update</a> your software.";
				return false;
			}
			else return true;
		}
		else $_SESSION['ermsgs'][] = "Failed to check for latest version.";
	}

	public function addBcS ($name, $fileArray, $dbArray, $schedule, $notify, $email_backup, $ftp_upload, $store_local) {
		//Adds a backup set entry in the database, creates necessary cron jobs

		$success = true; //Returns this boolean if succesful.

		if (empty($name)) {
			$success = false;
			$_SESSION['ermsgs'][] = "Please specify a name for this backup set.";
		}

		$name = htmlentities($name);
		
		$pos = strpos($name,"'");

		if ($pos) {$success = false; $_SESSION['ermsgs'][] = "Please do not use quotes (') in backup set name.";}

		if ($this->numFiles($fileArray) > $this->db->settings['MAX_NUMFILES']) {
			$success = false;
			$_SESSION['ermsgs'][] = "Backup size too large. You specified ".$this->numFiles($fileArray)." files, more than the allowed ".$this->db->settings['MAX_NUMFILES']." files. Try changing your backup settings in config.ini.php, or try splitting your backup set into parts.";
		}

		//Checks that each file or dir in the file array exists
		if (!empty($fileArray)) {
			foreach($fileArray as $f) {
				if (!file_exists($f)) {
					$success = false;
					$_SESSION['ermsgs'][] = "The location ".$f." does not exist.";
				}
				else {
					$files = $files.$f.",";
				}
			}
		}

		if (!empty($dbArray)) {
			foreach($dbArray as $d) {
				if (get_magic_quotes_gpc()) $d = stripslashes($d);
				
				if (!empty($d) && strcmp($d,"-none-") != 0 && strcmp($d,"'mysqlhost':'username':'password':'dbname'") != 0){
					
					$remotedb = $this->extractMysqlSettings($d, false);
					
					if (is_array($remotedb)){
						if (!$this->db->testMysqlSettings($remotedb[0],$remotedb[1],$remotedb[2],$remotedb[3])){
							$success = false;
							$_SESSION['ermsgs'][] = "(Please test your remote db's before adding them!) <br/> Could not connect to $d";
						}
						else $dbs = $dbs.$d.",";
					}
					else {
						$success = false;
						$_SESSION['ermsgs'][] = "Database $d does not exist.";
					}
				}
			}
		}
		
		if (empty($files) && empty($dbs)) {
			$success = false;
			$_SESSION['ermsgs'][] = "You need to specify atleast one file or database to backup.";
		}
		
		if(strlen($files) > 1000){
			$success = false;
			$_SESSION['ermsgs'][] = "The file paths you specified have too long names. Please try reducing the number of paths, or the lenth of path name.";
		}
		
		if (strlen($dbs) > 1000){
			$success = false;
			$_SESSION['ermsgs'][] = "Too many databases specified, or db names too long";
		}

		if ($success != false) {
			if(!get_magic_quotes_gpc()) {
				$name = mysql_escape_string($name);
			}
			else $dbs = mysql_escape_string($dbs);

			if ($notify == "1") {
				$notify = 1;
			}
			else $notify = 0;

			$r = $this->db->addBcSEntry (uniqid(), $name, $files, $dbs, $schedule, $notify, $email_backup, $ftp_upload, $store_local);

			if ($r) {
				$_SESSION['gmsgs'][] = "Backup set '$name' created.";
				return true;
			}
			else {
				$_SESSION['ermsgs'][] = "Failed to add backup set entry to database.";
				return false;
			}
		}
		else {
			return false;
		}
	}// End addBcS function

	/**
	 * This method executes a backup set by backing up all files and databases, and storing backups in appropriate locations,
	 * while performing necessary notifications and stuff.
	 *   
	     * Validates all necessary files to backup
	     * Starts output buffering (to save to log file) and sets some variables
	     * Dumps all databases to text files (sql dumps)
	     * Grabs all files and pops them in backup zip
	     * Uploads file to remote FTP server if necessary
	     * Notifies user by email (with attachment) if user wants.
	     * Records run entry into database
	     * Saves log file and cleans up
	 *
	 * @param mixed $id backup set ID
	 * @return mixed Returns file name if succesful, otherwise returns FALSE.
	 *
	 */
	public function runBcS ($id) {

		$success = true;
		$BcS = $this->db->getBcSInfo($id);

		/************Verification of files and db lists************/

		if (!$BcS) {
			$success = false;
			$_SESSION['ermsgs'][] = "Backup set does not exist with this id: '".$id."'";
		}

		$valid_files = Array();

		$unverified_files = explode(",", $BcS['files']);
		$dbsArray = explode(",", $BcS['dbs']);

		foreach ($unverified_files as $uf) {
			if (file_exists($uf)) {
				$valid_files[] = $uf;
			}
		}

		if ($this->numFiles($valid_files) > $this->db->settings['MAX_NUMFILES']) {
			$success = false;
			$_SESSION['ermsgs'][] = "Backup size too large. You specified ".$this->numFiles($valid_files)." files, more than the allowed ".$this->db->settings['MAX_NUMFILES']." files. Try splitting your backup set into parts. See help for more info.";
		}

		/************Ok, everything verified. Let's go!************/

		if ($success === true) {//If everything is all right up to this point


			/******************Initializing some shit**********************/
			
			ob_start(); //Start saving all echoed data from now to be saved to a log file later
			$startTime = time(); //Start the timer (to calculate how long the zipping took later on)
			$currentDate = date("_M_j_Y_G-i-s"); //To be used in the file names
			$dbdumps[] = array(); //A list of db dumps created (so they can be deleted after)
			$bcsname = preg_replace("/[^a-zA-Z0-9 -]/", "", $BcS['name']); //Formats name of backup set to a respectable name
			$bcsname = str_replace(" ", "-", $bcsname);

			echo "------------------------------------------------<br/>";
			echo "-----Starting to run backup set $BcS[name] <br/>";


			/************Dump all databases to SQL files*****************/
			
			$dbcounter = 0;

			foreach ($dbsArray as $db) {
				
				if (!empty($db) && strcmp($db,"-none-") != 0 && strcmp($db,"'mysqlhost':'username':'password':'dbname'") != 0) {
					
					if (get_magic_quotes_gpc()) $db = stripslashes($db);
					
					$remotedb = $this->extractMysqlSettings($db, false);
					
					if (is_array($remotedb)){
						
						echo "<br/> Backing up database $remotedb[3]";
						
						$nameOfThisDump = $remotedb[3]."--".$currentDate.".sql";
						$outputDump = $this->db->settings[LOCAL_BC_STORAGE]."/backup_dbs/".$nameOfThisDump;
						
						if ($this->db->backup_tables($remotedb[0],$remotedb[1],$remotedb[2],$remotedb[3], '*', $outputDump)) {

							$valid_files[] = $outputDump;
							echo " ..done saved $remotedb[3] in $outputDump";
						}
						else {
							echo "..Failed to save $remotedb[3] in $outputDump (connected to $remotedb[0], using username $remotedb[1]) ";
							$success = false;
						}
						$dbdump[] = $outputDump;
					}
					else {
						$success = false;
						echo "<div class=\"solid-error\">DATABASE $db does not exist (or could not connect)!</db>";
					}
					
				}
			}

			/************Zip entire backupset and created files************/

			$nameOfFile = $bcsname.$currentDate.".zip";
			$outputZip = $this->db->settings[LOCAL_BC_STORAGE]."/".$nameOfFile;

			if ($this->zipEntireBackupSet($valid_files, $outputZip)) {
				echo '<div class="solid-ok"><b>Succesfully zipped all files to '.$outputZip.'</div>';
			}else {
				echo "<div class=\"solid-error\">There was an error zipping some files to '$outputZip'. Check log above to see which files were causing problems. </div>";
				$success = false;
			}

			/************Some metrics************/

			$endTime = time();
			$runtime = intval($endTime) - intval($startTime);
			$runtime = $runtime / 60; //Time in minutes
			
			$logDest = "backup_logs/".$bcsname.$currentDate.".log.php";

			
			/************Emailing With Attachment************/
			
			//Notify the user by email
			if ($success == false) $this->notifyUserOnRun($BcS['name'], $outputZip, $logDest, $this->db->settings[ADMIN_EMAIL], $success);
			else {
				if (!$BcS['email_backup']){
					if ($BcS['notify']) {
						$this->notifyUserOnRun($BcS['name'], $outputZip, $logDest, $this->db->settings[ADMIN_EMAIL], $success);
					}
				}
				
				if ($BcS['email_backup']){
					if (!DEMO) $this->sendBackupEmail($bcsname,$outputZip, $logDest, $success);
				}
			}
			
			
			/***************Upload to remote FTP server********************/
			
			if ($BcS['ftp_upload']){
				if (!DEMO){
					if (empty($this->db->settings[FTP_CONFIG])) $_SESSION['ermsgs'][] = "No FTP settings are configured - could not upload backup to FTP server.";
					else {
						if ($this->uploadViaFTP($outputZip, $nameOfFile, $this->db->settings[FTP_CONFIG])) echo "<br/>FTP UPLOAD successful, using FTP config specified";
						else {
							$success = false; 
							echo "<div class=\"solid-error\">Failed to upload backup to FTP location.</div>";
						}
					}
				}
				else $_SESSION['msgs'][] = "Actual FTP uploading to remote servers has been disabled in the demo.";
			}
			
			
			/*************Write entry about this run to database************/
			
			if ($this->db->addRunEntry($BcS['id'], $endTime, $outputZip, $logDest, $success)) echo "<br/>Added entry to database.";
			else echo "<br/>Error adding entry to database ".mysql_error();
			
			
			/*********Cleans up files if necessary*****************/
			
			if ($BcS['store_local'] == 0) {
				if (unlink(realpath($outputZip))) echo "<br/>Deleted local backup file as asked";
				else echo "<br/>Failed to delete local backup file";
			}
			
			if (!empty($dbdumb)){
				foreach ($dbdump as $db){
					@unlink($db);
				}
			}
			
			/**********Finishing off reports**********/
			
			echo "<br/> Saved files to $outputZip ";
			echo "<br/> Total creation time: $runtime minutes";
			echo "<br/> Operation finished on ".date("M_j_Y_G-i-s");
			echo "<br/>-----FINISHED RUNNING BACKUP SET $BcS[name] <br/>";
			echo "------------------------------------------------<br/>";
			
			
			/***************Write Log File******************/
			
			$this->writeLogFile($logDest, ob_get_contents()); //Stores output to log
			ob_end_clean(); //Close and clean the object buffer
			
			
			/***************Return values**********************/
			if ($success !== false) {
				$_SESSION['gmsgs'][] = "Success! Backed up '$BcS[name]' fully! Your backup is saved in $outputZip. You can view the log file by <a href=\"$logDest\" target=\"_blank\">clicking here.</a>";
			}
			else {
				$_SESSION['ermsgs'][] = "There was an error backing up '$BcS[name]' fully! Your backup is PARTIALLY saved in $outputZip. Some files may not be saved. Please find out what is causing the problem, check the log file by <a href=\"$logDest\" target=\"_blank\">clicking here.</a>.";
			}
			
			//Whew. What a function!
		}
	}

	/**
	 * This is method zipEntireBackupSet
	 *
	 * @param mixed $sourceFilesArray An array of files/folders
	 * @param mixed $dest is where you want the ZIP file
	 * @return bool
	 *
	 */
	private function zipEntireBackupSet ($sourceFilesArray, $dest) {

		$success = true;

		foreach ($sourceFilesArray as $source) {
			$zipArchive = new ZipArchive();

			if (!$zipArchive->open($dest, ZIPARCHIVE::CREATE)) {
				echo "<div class=\"solid-error\">Error opening zip file $dest</div>";
				$success = false;
			}
			elseif (file_exists($source)) {
				if (is_dir($source)) {

					$dirname = basename(str_replace('//', '/', $source.'/')).'/';

					echo "<br/>I'm gonna save '$source' in the directory '$dirname' in the zip file.";

					if ($this->addFolderToZip($source, $zipArchive, $dirname)) {
						echo "<br/><b>Zipped directory '$source' to zip in the '$dirname' directory succcesfully</b>";
					}
					else {
						$success = false;
					}
				}
				elseif(is_file($source)) {
					if($zipArchive->addFromString(basename($source), file_get_contents($source))) {
						echo "<br/><b>Zipped file '$source' to '$dest' succesfully.</b>";
					}
					else $success = false;
				}
			}

			$zipArchive->close();
		}
		return $success;
	}

	private function addFolderToZip($dir, $zipArchive, $zipdir = '') {
		// Function to recursively add a directory,
		// sub-directories and files to a zip archive
		//If you don't specify the third parameter (zipdir), the directory you're adding will be added at the root of the zip file.
		//D.Jann from PHP.net
		$success = true;

		if (is_dir($dir)) {
			if ($dh = opendir($dir)) {

				//Add the directory
				if($zipArchive->addEmptyDir($dir)) {
					echo "<br/>Created directory '$dir'.";
				}
				else {
					$success = false;
					echo "<br/><b>ERROR creating directory '$dir'";
				}

				// Loop through all the files
				while (($file = readdir($dh)) !== false) {

					//If it's a folder, run the function again!
					if(!is_file($dir . $file)) {
						// Skip parent and root directories
						if( ($file !== ".") && ($file !== "..")) {
							$this->addFolderToZip($dir . $file . "/", $zipArchive, $zipdir . $file . "/");
						}

					}else {
						// Add the files
						if ($zipArchive->addFile($dir . $file, $zipdir . $file)) {
							echo "<br/>Added file $dir$file...";
						}
						else {
							$success = false;
							echo "<br/><b>ERROR ADDING FILE '$dir$file' to '$zipdir$file'";
						}
					}
				}
			}
		}

		return $success;
	}
	
	/**************Misc Functions****************************/


	public function writeLogFile($dest, $content) {
		//Writes a log file, after being given the destination and the content to be logged

		$styling = '<?php
$root = dirname(dirname(__FILE__));

require_once ("$root/ctrls/manage.class.php");
$manage = new manage;

$manage->checkLoginAndDirect();
?>

<html><head><link href="../models/styles/style.css" rel="stylesheet" type="text/css" /></head>
    <body>
        ';

		$content = $styling.$content; //Adds log styling info to content

		if($fh = @fopen($dest, "a+") ) {
			if (!fwrite($fh, $content)) {
				return false;
			}
			else return true;
			fclose($fh);
		}
	}
	
	public function uploadViaFTP ($localFilePath, $filename, $configString){
		if (file_exists($localFilePath)) {
			
			//Extract information and format input
			$info = explode("--&Login--", $configString);
			$ftpConfigString = $info[0];
			$userpw = $info[1];
			$ftpConfigString = str_replace("ftp:/","ftp://",str_replace("//","/",$ftpConfigString."/")); 
			
			
			//$ftpConfigString = 'ftp://ftp.isahassen.com/public_html/isahassen/';//Debug
			
			$ch = curl_init();
			
			if ($ch){
				$fp = fopen($localFilePath, 'r');
				$localFilePath = realpath($localFilePath);
				
				curl_setopt($ch, CURLOPT_URL, $ftpConfigString.$filename);
				curl_setopt($ch, CURLOPT_USERPWD, $userpw);
				curl_setopt($ch, CURLOPT_UPLOAD, 1);
				curl_setopt($ch, CURLOPT_INFILE, $fp);
				curl_setopt($ch, CURLOPT_INFILESIZE, filesize($localFilePath));
				curl_exec ($ch);
				$error_no = curl_errno($ch);
				$errortxt = curl_error($ch);
				curl_close ($ch);
				
				if ($error_no == 0) {
					return true;
				} else {
					$_SESSION['ermsgs'][] = $errortxt."<br/>Tried: ".$ftpConfigString.$filename;
					return false;
				}
			}
			else {
				$_SESSION['ermsgs'][] = "CURL is not installed on your server. It is a requirement to be able to upload FTP files to remote servers.";
				return false;
			}
		} else {
			$_SESSION['ermsgs'][] = "File $localFilePath does not exist on local machine - cannot upload this file to remote FTP server";
			return false;
		}
	}
	
	public function extractMysqlSettings($configstring, $show_errors = false){

		if (substr($configstring, 0, 1) !== "'" && substr($configstring, -1, 1) !== "'"){
			if ($this->db->testMysqlSettings($this->db->settings[DB_HOST], $this->db->settings[DB_USERNAME], $this->db->settings[DB_PASSWORD], $configstring)){
				$configarray[0] = $this->db->settings[DB_HOST];
				$configarray[1] = $this->db->settings[DB_USERNAME];
				$configarray[2] = $this->db->settings[DB_PASSWORD];
				$configarray[3] = $configstring;
				
				return $configarray;
			}
			else {
				if($show_errors) $_SESSION['ermsgs'][] = "Incorrect syntax. Please use the correct syntax for entering remote DB settings.";
				return false;
			}
		}
		else {
			$configstring = substr($configstring, 1);
			$configstring = substr($configstring, 0, -1);
			
			$configarray = explode("':'",$configstring);
			
			if (count($configarray) === 4) return $configarray;
			else {
				if($show_errors) $_SESSION['ermsgs'][] = "Incorrect syntax. Please use the correct syntax for entering remote DB settings.";
				return false;
			}
		}
	}
	
	private function sendBackupEmail($bcsname, $attachfile, $logfile, $success){
		
		$mailto = $this->db->settings[ADMIN_EMAIL];
		$from_mail = $this->db->settings[ADMIN_EMAIL];
		$from_name = "UPSB";
		$replyto = "noreply@phpsitebackup.com";
		
		if ($success == true) $subject = "Backup Set Executed: $bcsname";
		else $subject = "Error doing backup: $bcsname";

		$message = "Hi,\n\n Your backup set $bcsname was run, and completed on ".date("M. j, Y - G:i:s e");
		if ($success == false) $message = $message."\n There were some problems running your backup set. Please check the log file immediately.";
		$message = $message."\n The backup file is attached.";
		$message = $message."\n You can view the log file here: ".$this->mapURL('do.php')."?goto=$logfile";
		$message = $message."\n\n\n This is an automated message sent by Ultimate PHP Site Backup. You can disable these messages by changing the settings for your backup set. ";
		
		$filename = end(explode("/",$attachfile));
		
		$this->mail_attachment($filename,$attachfile,$mailto,$from_mail,$from_name,$replyto,$subject,$message);
	}
	
	private function mail_attachment($filename, $path, $mailto, $from_mail, $from_name, $replyto, $subject, $message = "") {
		
		if ($message == ""){
			
		}
		
		$file = $path;
		$file_size = filesize($file);
		$handle = fopen($file, "r");
		$content = fread($handle, $file_size);
		fclose($handle);
		$content = chunk_split(base64_encode($content));
		$uid = md5(uniqid(time()));
		$name = basename($file);
		$header = "From: ".$from_name." <".$from_mail.">\r\n";
		$header .= "Reply-To: ".$replyto."\r\n";
		$header .= "MIME-Version: 1.0\r\n";
		$header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
		$header .= "This is a multi-part message in MIME format.\r\n";
		$header .= "--".$uid."\r\n";
		$header .= "Content-type:text/plain; charset=iso-8859-1\r\n";
		$header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
		$header .= $message."\r\n\r\n";
		$header .= "--".$uid."\r\n";
		$header .= "Content-Type: application/octet-stream; name=\"".$filename."\"\r\n"; // use different content types here
		$header .= "Content-Transfer-Encoding: base64\r\n";
		$header .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
		$header .= $content."\r\n\r\n";
		$header .= "--".$uid."--";
		if (mail($mailto, $subject, "", $header)) {
			return true;
		} else {
			return false;
		}
	}

	public function displayMessagesWithSmarty($display = "") {

		if (isset($_SESSION['msgs'])) {
			$msgs = Array();
			$msgs = $_SESSION['msgs'];
		}

		if (isset($_SESSION['gmsgs'])) {
			$gmsgs = Array();
			$gmsgs = $_SESSION['gmsgs'];
		}

		if (isset($_SESSION['ermsgs'])) {
			$ermsgs = Array();
			$ermsgs = $_SESSION['ermsgs'];
		}

		$GLOBALS['smarty']->assign("msgs", $msgs);
		$GLOBALS['smarty']->assign("good", $gmsgs);
		$GLOBALS['smarty']->assign("errors", $ermsgs);
		//End message passing

		unset($_SESSION['msgs']);
		unset($_SESSION['gmsgs']);
		unset($_SESSION['ermsgs']);

		if ($display != "display:false") {
			$GLOBALS['smarty']->display("messages.tpl");
		}
	}

	private function notifyUserOnRun ($bcsname, $backupfile, $logfile, $email, $success) {

		if ($success == true) $subject = "Your backup set was run: $bcsname";
		else $subject = "Error doing backup: $bcsname";

		$message = "Hi,\n\n Your backup set $bcsname was run, and completed on ".date("M. j, Y - G:i:s e");
		if ($success == false) $message = $message."\n There were some problems running your backup set. Please check the log file immediately.";
		$message = $message."\n You can download the backup file created here: ". $this->mapURL('do.php')."?download=$backupfile";
		$message = $message."\n You can view the log file here: ".$this->mapURL('do.php')."?goto=$logfile";
		$message = $message."\n\n\n This is an automated message sent by Ultimate PHP Site Backup. You can disable these messages by changing the settings for your backup set. ";

		if ($this->db->settings[USE_PHPMAIL] == true) {
			if (mail($email, $subject, $message)) echo "<br/> Email dispatched to $email";
			else echo "<div class=\"solid-error\">Could not dispatch email to $email</div>";
		}
		else {
			require("$GLOBALS[root]/ctrls/SMTP.class.php");
			$smtp = new SMTP($this->db->settings[SMTP_SERVER], $this->db->settings[SMTP_PORT], $this->db->settings[SMTP_USE_TLS]);
			$smtp->auth($this->db->settings[SMTP_USER], $this->db->settings[SMTP_PASS]);
			$smtp->mail_from($this->db->settings[SMTP_FROM]);
			$smtp->send($email, $subject, $message);
			echo $smtp->error();
		}
	}

	public function dirSize($directory) {
		$size = 0;

		if (is_dir($directory)) {
			foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
				$size+=$file->getSize();
			}
		}
		else if (is_file($directory)) {
			$size = filesize($directory);
		}
		return $size;
	}

	public function numFiles($locations) {
		$size = 0;

		if (!empty($locations)) {
			foreach ($locations as $directory) {

				if (is_dir($directory)) {
					foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
						$size++;
					}
				}
				else if (is_file($directory)) {
					$size++;
				}
			}
			return $size;
		}
	}

	public function mapURL($relPath) {
		$filePathName = realpath($relPath);
		$filePath = realpath(dirname($relPath));
		$basePath = realpath($_SERVER['DOCUMENT_ROOT']);

		// can not create URL for directory lower than DOCUMENT_ROOT
		if (strlen($basePath) > strlen($filePath)) {
			return '';
		}

		return 'http://' . $_SERVER['HTTP_HOST'] . substr($filePathName, strlen($basePath));
	}

	public function getRelativeDate ( $timestamp , $type = "" ) {

		if (!is_numeric($timestamp)) $timestamp = strtotime($timestamp);
		
		// calculate the diffrence
		$timediff = time () - $timestamp ;

		if ($timediff < 60) {
			$returndate = $timediff." seconds ago.";
		}

		elseif ($timediff < 3600) {
			if ($timediff < 120) {
				$returndate = "1 minute ago";
			}
			else {
				$returndate =  round($timediff / 60) . " minutes ago.";
			}
		}

		elseif ($timediff < 7200) {
			$returndate = "1 hour ago.";
		}
		elseif ($timediff < 259200) {
			$returndate = round($timediff / 3600, 1) . " hours ago.";
		}

		elseif ($timediff < 1209600) {
			$returndate = round($timediff / 86400, 1) . " days ago.";
		}

		else {
			$returndate = @date('n-j-Y', $timestamp);
			if($type=="fulldate") {
				$returndate = @date('n-j-y, H:i', $timestamp);
			}

			else if ($type=="time") {
				$returndate = @date('H:i', $timestamp);
			}
		}
		return $returndate;
	}

}
//End class

?>