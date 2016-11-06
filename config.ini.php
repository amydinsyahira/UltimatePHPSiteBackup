; To view or edit this config file, you need to have access to the server's filesystem (via FTP or such).
; <?php /* Do not change or remove this line. It prevents people from viewing this file from a browser.


; Ultimate PHP Site Backup (UPSB) Configuration File
; The database information you enter below is NOT related to the databases you want to backup.

; This database is the database that this software will use for its own data. It is required to run this software.
; Please enter the host, username, password, and name of the database you want to use.

[Database Settings]

DB_HOST = localhost
DB_USERNAME = mysqlusername
DB_PASSWORD = "password"
DB_NAME = dbname

; Change this to a random bunch of characters. You do not need to remember it or change it once created. Just leave it after you randomize.
SALT = SALT_398jfnasf38

[Email Settings]

;Your email address (where notifications will be sent).
ADMIN_EMAIL = support@phpsitebackup.com



;----------------------------------------------------------------------------------------
;----------------------------------------------------------------------------------------
;STOP: You do not need to edit anything past this point, unless you want to tinker around.
;----------------------------------------------------------------------------------------
;----------------------------------------------------------------------------------------






;If this setting is true, the software will use the default PHP mail() function.
USE_PHPMAIL = true

; Currently, SMTP email settings are not supported, unfortunately

[Backup Folder Settings]


; This is the location where you want your backup files stored (relative to index.php). It is STRONGLY recomended you keep this at the default.
; Otherwise, be sure to copy this folder to somwhere else on your server, with the same sub-folders, for the script to work.

LOCAL_BC_STORAGE = backup_storage

; This is the maximum number of backups that are stored for each backup set. 
; After this number, the oldest backups will be removed to make room for new ones. 
;(Applies to local storage ONLY)

MAX_BACKUPS_STORED = 25


; Maximum number of files allowed in a single zip (recommended to keep this setting below 5000, unless you are sure your server can handle it)

MAX_NUMFILES = 5000;


[Software Lockdown Mode]

; If the software is in lockdown mode, and you are a legit admin, change this value to "true" to unlock the software again.
; You will still need to know your password after unlocking the software. This simply allows you to have unlimited tries in guessing.
; EXTREMELY IMPORTANT:
; Remember to change this value back to "false" again as soon as you get back in, otherwise your software will be susceptible to brute forcing.

DISABLE_LOCKDOWN = false



; End of config file! 


; /* Do not change or remove this line. It prevents people from viewing this file from a browser. */?>