<a onclick="toggleAddBcS();">
	<img class="pointer" border="none" src="models/images/addbcs_btn.gif" width="220" height="57" alt="Add Backup Set"/>
</a>

<div id="addBcS">
	<form name="addBcS" action="do.php?addbcs" method="POST" id="bcsform">
		<fieldset>Backup Set Name: <input type="text" class="textinput" name="name" value="" /></fieldset>

		<fieldset>
			<legend>Files (optional)</legend>
			<br/>This scripts location: {$smarty.server.SCRIPT_FILENAME} <br/>
			<br/>Optional: Select the files you want to backup.
			<div id="filetree" class="fileSelector"></div>
		</fieldset>

		<fieldset>
			<legend>Databases (optional)</legend>
			<p>Select the LOCAL databases you want to backup (or select none). Use CTRL or CMD to select multiple.</p>
			<select name="dbs[]" size="5" multiple="multiple">
				<option selected="true" value="-none-">---none---</option>
				{section name=dbs loop=$dbArray}
					<option value="{$dbArray[dbs]}">{$dbArray[dbs]}</option>
				{/section}
			</select>

			<h2>Remote Databases</h2>
			<div id="msgs_db"></div>
			<p>Enter remote databases using this syntax: <span style="color:#246DB5">'mysqlhost':'username':'password':'dbname'</span></p>
			<p>Please TEST to make sure that your db settings are working (click the "Test this db" button)</p>
			<input name="dbs[]" class="dbinput" type="text" size="65" value="'mysqlhost':'username':'password':'dbname'" /><span class="btntext" onclick="testRemoteDb($(this));return false;">Test this DB</span>
			<a style="display:block" class="btntext" onclick="addRemoteDbField($(this));return false;" id="addremotedb">Click to add another db...</a>
		</fieldset>
		
		<fieldset>
			<legend>Schedule</legend>
			<select name="schedule">
				<option value="30min">No auto-backup</option>
				<option value="30min">Every 30 Minutes</option>
				<option value="hourly">Hourly</option>
				<option value="daily" selected="yes">Daily</option>
				<option value="48hrs">Every 48 Hours</option>
				<option value="72hrs">Every 72 Hours</option>
				<option value="weekly">Weekly</option>
				<option value="monthly">Monthly</option>
			</select>
			 (how often should we create a backup?)
		</fieldset>

		<fieldset>
			<legend>Additional options</legend>
			<img src="models/images/email_go.png" title="Email notification enabled"/><input type="checkbox" name="notify" value="1" /> Send email notification on each execution? (not recommended for frequent backups)<br/>
			<img src="models/images/mail-attachment.png" title="Backups sent as email attachments"/><input type="checkbox" name="email_backup" value="1" /> Send zip <strong>email attachment</strong> on each backup? (not recommended for large file backups)<br/>
			<img src="models/images/network.png" title="Store on remote FTP server"/><input type="checkbox" name="ftp_upload" value="1" /> Upload backup zips to remote FTP server? (configure FTP settings below) <br/>
			<img src="models/images/harddrive.png" title="Local storage"/><input type="checkbox" name="store_local" value="1" checked="true" /> Keep a copy of backup zip in local storage? (recommended) <br/>
		</fieldset>

		<br/><input type="submit" value="Submit" name="submit" />
	</form>
</div>

<div id="editBcS" style="display:none">
	<h2>Edit Backup Set</h2>
	<p>Note: You cannot edit the files or database list of a backup set. You will have to delete and create a new one. You can only edit the schedule and name of a backup set.</p>

	<form name="editBcS_form" method="POST" id="editBcS_form" action="do.php">
		<input type="hidden" name="id" id="editBcS_form_id" value=""/>

		Backup Set Name: <input class="textinput" type="text" name="name" id="editBcS_form_name" value="" /><br/>

		<br/>
		Schedule
		<select name="schedule" id="editBcS_form_schedule">
			<option value="30min">No auto-backup</option>
			<option value="30min">Every 30 Minutes</option>
			<option value="hourly">Hourly</option>
			<option value="daily">Daily</option>
			<option value="48hrs">Every 48 Hours</option>
			<option value="72hrs">Every 72 Hours</option>
			<option value="weekly">Weekly</option>
			<option value="monthly">Monthly</option>
		</select>
		<br/><br/>

		<img src="models/images/email_go.png" title="Email notification enabled"/><input id="editBcS_form_notify" type="checkbox" name="notify" value="1" /> Send email notification on each execution? (not recommended for frequent backups)<br/>
		<img src="models/images/mail-attachment.png" title="Backups sent as email attachments"/><input id="editBcS_form_email_backup" type="checkbox" name="email_backup" value="1" /> Send zip <strong>email attachment</strong> on each backup? (not recommended for large file backups)<br/>
		<img src="models/images/network.png" title="Store on remote FTP server"/><input id="editBcS_form_ftp_upload" type="checkbox" name="ftp_upload" value="1" /> Upload backup zips to remote FTP server? (configure FTP settings below) <br/>
		<input id="editBcS_form_store_local" type="checkbox" name="store_local" value="1"/> Keep a copy of backup zip in local storage? (recommended) <br/>

		<br/><input type="submit" value="Submit" name="submit" /><input type="button" value="Cancel" name="cancel" onclick="hideEditBcS();return false;" />
	</form>
</div>