<html>
<head><link href="models/styles/style.css" rel="stylesheet" type="text/css" media="screen" /></head>
<body>
<center>
<div id="outer-wrapper">
<?php
$ini = "config.ini.php";
$settings = parse_ini_file($ini);

if (!$settings) {
	die('Could not parse INI file config.ini.php.');
}

$con = mysql_connect($settings[DB_HOST],$settings[DB_USERNAME],$settings[DB_PASSWORD]);
if (!$con) {
	die('Could not connect: ' . mysql_error());
}

if (!mysql_select_db($settings[DB_NAME], $con)) die ('Connected succesfully, but could not use database: '.$settings[DB_NAME]);

$r = mysql_query("SELECT * FROM admin WHERE username = 'installed'");

if ($r) {
	$r = mysql_fetch_assoc($r);
}

if ($r['admin_pw'] == "true") {
	echo '<h1>Ultimate PHP Site Backup is already installed.</h1>To reinstall or upgrade, you need to delete the "admin" table in your database (UPSB\'s db). Then re-upload install.php (because it is auto-deleted when you login). <h2>Warning to upgraders</h2>Doing an upgrade deletes your old settings, and uses a different db structure (if you are upgrading from v1.0). Please do not restore an old db once you have re-installed. However, you can remember or copy your old settings to text file, and re-enter new backup sets when done.';
	die;
}

$installsql[] = "DROP TABLE IF EXISTS `admin`";
$installsql[] = "DROP TABLE IF EXISTS `bsets`";
$installsql[] = "DROP TABLE IF EXISTS `runs`";

$installsql[] = "
CREATE TABLE IF NOT EXISTS `admin` (
  `username` varchar(50) NOT NULL DEFAULT 'admin',
  `admin_pw` varchar(1000) NOT NULL,
  UNIQUE KEY `username` (`username`),
  KEY `admin_pw` (`admin_pw`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";

$installsql[] = "
CREATE TABLE IF NOT EXISTS `bsets` (
  `id` varchar(25) NOT NULL,
  `name` varchar(25) NOT NULL,
  `files` longtext,
  `dbs` longtext,
  `schedule` varchar(255) NOT NULL,
  `notify` tinyint(1) DEFAULT '0',
  `email_backup` tinyint(1) NOT NULL DEFAULT '0',
  `ftp_upload` tinyint(4) NOT NULL DEFAULT '0',
  `store_local` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";

$installsql[] = "
CREATE TABLE IF NOT EXISTS `runs` (
  `id` varchar(25) NOT NULL,
  `bset` varchar(25) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `zipfile` varchar(255) NOT NULL,
  `logfile` varchar(255) NOT NULL,
  `success` tinyint(1) NOT NULL,
  UNIQUE KEY `id_2` (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
";

foreach ($installsql as $q) {
	if (!mysql_query($q)) {
		echo "Failed to set up database. ".mysql_error();
		die;
	}
}

$hash = sha1("upsbadmin".$settings[SALT]);

$installsql2 = "
INSERT INTO `admin` (`username`, `admin_pw`) VALUES
('admin', '$hash'),
('installed', 'true'),
('ftpconfigstring', '');";

if (!mysql_query($installsql2)) {
	echo "<h1>Failed to insert data into tables. Please make sure your config.ini.php settings are correct, and you have appropriate permissions. Retry installation.</h1>";
}

mysql_close($con);

echo "
<h1>If you didn't see any errors above me, Ultimate PHP Site Backup has successfully installed!</h1>
<h2>Remaining Steps to complete installation</h2>
<p>(This step is required if you want scheduled backups to work) Configure a cron job (Linux) or scheduled task (Windows) using cPanel, or any other method. You need to set cron.php to run atleast once every 30 minutes. See below (or search the internet and/or contact your host), if you don't know how to set cron jobs.</p>
<h3>You may now <a href=\"login.php\">login</a> with the default password: upsbadmin </h3>";
?>
</div>
</center>
</body>
</html>