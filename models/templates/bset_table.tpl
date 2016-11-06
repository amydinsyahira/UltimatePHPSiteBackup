<center>
<table cellspacing="0" cellpadding="0" id="bset_table" width="600px">
	<thead>
		<th>Name</th>
		<th>Schedule</th>
		<th>Actions</th>
	</thead>

	<tbody>
		{section name=set loop=$backup_sets}
			<tr id ="{$backup_sets[set].id}" ondblclick="getBcSRuns('{$backup_sets[set].id}');return false;" class="bset_table_row">
				<td><a class="bcsname" onclick="getBcSRuns('{$backup_sets[set].id}');return false;">{$backup_sets[set].name}</a></td>
				<td>
					{$backup_sets[set].schedule}
					{if $backup_sets[set].notify eq 1}<img src="models/images/email_go.png" title="Email notification enabled"/>{/if}
					{if $backup_sets[set].email_backup eq 1}<img src="models/images/mail-attachment.png" title="Backups sent as email attachments"/>{/if}
					{if $backup_sets[set].ftp_upload eq 1}<img src="models/images/network.png" title="Store on remote FTP server"/>{/if}
					{if $backup_sets[set].store_local eq 0}<img src="models/images/nolocal.png" title="Not stored on local drive"/>{/if}
				</td>
				<td class="action_btns">
					<img class="rollover" id="{$backup_sets[set].id}_run_btn" src="models/images/run_btn.png" onclick="doRunBcS('{$backup_sets[set].id}', '{$backup_sets[set].name}');return false;" alt="Run"/>
					<img class="rollover" src="models/images/edit_btn.png" onclick="openEditBcS('{$backup_sets[set].id}', '{$backup_sets[set].name}', '{$backup_sets[set].schedule}', '{$backup_sets[set].notify}', '{$backup_sets[set].email_backup}', '{$backup_sets[set].ftp_upload}', '{$backup_sets[set].store_local}');return false;" alt="Edit"/>
					<img class="rollover" src="models/images/del_btn.png" onclick="doDelBcS('{$backup_sets[set].id}', '{$backup_sets[set].name}');return false;" alt="Delete"/>
				</td>
			</tr>
			<tr>
				<td id ="{$backup_sets[set].id}_runs" class="bsetruns" colspan="3"></td>
			</tr>
		{/section}
	</tbody>
</table>
</center>