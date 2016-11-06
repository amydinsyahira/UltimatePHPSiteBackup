{include file="header.tpl" title=foo}
{include file="messages.tpl"}


<span style="float:right;">
	<strong>Version:</strong> {$smarty.const.CUR_VERSION} <br/>
	<a href="do.php?checkversion">Check for new version</a>
</span>

{include file="newBcS_form.tpl"}

<p>
	<h1>Backup sets</h1>
	<h2>Click backup set name to see backups</h2>
	<div id="msgs"></div>
	<div id="bset_table">
		{include file="bset_table.tpl"}
	</div>
</p>
<script type="text/javascript" src="models/js/footer.js"></script>

{html_image file='models/images/logout_btn.gif' alt='Logout' href='do.php?logout=true' border = none;}

<a onclick="toggleConfig();" >
    <img name="config" src="models/images/editconfig_btn.gif" border="none" alt="Edit settings" class="pointer"/>
</a>

<div id="config">
	<form name="pwconfig" action="do.php?pwconfig=edit" method="POST">
		<h2>Change password:</h2>
    
		Enter current administrator password: <input type="password" class="textinput" name="currentpassword" value="" /><br/>
		Enter NEW administrator password: <input type="password" class="textinput" name="newpassword" value="" /><br/>
		Confirm NEW administrator password: <input type="password" class="textinput" name="confirmpassword" value="" /><br/>
		<br/><input type="submit" value="Update Password" />
	</form>
	<form name="ftpconfig" action="do.php?ftpconfig=edit" method="POST">
		<h2>FTP Upload Settings</h2>
		<div id="msgs_ftp"></div>
		<p>FTP settings are stored using this syntax: ftp://ftp.domain.com/uploadfolder/--&Login--ftp_login:password</p>
		{if $settings.FTP_CONFIG neq ""}<p><strong style="color:blue">Your current FTP settings:</strong> {$settings.FTP_CONFIG}</p>
		{else}<p><strong>Currently you do NOT have any FTP settings configured.</strong></p>{/if}
		FTP Host: <input type="text" class="textinput" name="ftphost" size="20" value="" /><br/>
		FTP Username: <input type="text" class="textinput" name="ftpusername" size="20" value="" /><br/>
		FTP Password: <input type="password" class="textinput" name="ftppass" size="20" value="" /><br/>
		FTP Upload Directory: <input type="text" class="textinput" name="ftpdir" size="20" value="" /><br/>
		<br/><input type="button" value="Update FTP Settings" onclick="testFTP();return false;" />
	</form>
	<h2>Other settings</h2>
	<p>To edit all other settings, such as database settings and backup folder settings, goto the Ultimate PHP Site Backup folder, and edit config.ini.php with a text editor.</p>
	<p><strong>Email address:</strong> {$settings.ADMIN_EMAIL}</p>
	<p><strong>Local backup folder:</strong> {$settings.LOCAL_BC_STORAGE}</p>
	<p><strong>PHP mail() function:</strong> {$settings.USE_PHPMAIL}</p>
</div>

{include file="footer.tpl"}
