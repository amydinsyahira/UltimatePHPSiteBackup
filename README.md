# Ultimate PHP Site Backup v2.0

An extremely powerful, robust, yet simple tool for website owners which allows you to backup websites and databases in seconds, automatically, so you never have to worry about them again.

The software itself is written in PHP, and runs on your web server. Simply upload it to your server, configure a number of files or **(remote or local)** MySQL databases which you need backed up, and set a button. UPSB will take care of the rest, saving those files to a zip, classifiying it by date, all in the background without intervention, according to a custom schedule. You can specify different schedules for different sets of files/dbs. **Backups can now be stored on remote FTP servers, or sent as email attachments. (new in v2.0)**

Features:

- Intuitive interface which makes it so easy that my grandma could do it. Just select files and databases by clicking a few checkboxes.
- Will **not** slow down your server&#39;s normal operation, even while backing up thousands of files and sizable databases. (See Performance Testing in the FAQ)
- Ability to create different backup sets. A &quot;backup set&quot; is a collection of files and databases, which run according to a predefined schedule. This is extremely useful for shared hosting servers, where many different websites are running. You can specify some websites to be backed up hourly (eg. a busy blog), and some to backed up weekly (eg. a static client site).
- Can upload backups to remote FTP servers, or send as email attachments _(new in v2.0)_
- Can backup remote MySQL databases _(new in v2.0)_
- Can be configured to run in the background (using cron jobs).
- Well designed system makes it easy to restore your files/databases in case of an emergency. Download a structured zip file, upload any files you lost, and use the structured backup .sql file to restore any parts of the database you wish.

Who would need this?

- Website owners needing a quick backup solution.
- Freelance developers with lots of small/medium projects.
- Any developer looking to save time and hassle with project disaster prevention/recovery
- A person who doesn&#39;t have too much technical knowledge/skills

#

# Extremely Quick Installation (for Linux/Unix servers)

Step 1: Edit config.ini.php with a text editor to reflect your db settings. You need to set a couple of other settings as well. Stop editing at the line which tells you that you don&#39;t need to edit further.

Step 2: Upload all files to your server and visit install.php in your browser for automatic installation. For example: mysite.com/ultimatephpsitebackup/install.php

Step 3: (This step is required if you want scheduled backups to work) Configure a cron job (Linux) or scheduled task (Windows) using cPanel, or any other method. You need to set cron.php to run atleast once every 30 minutes. See below (or search the internet and/or contact your host), if you don&#39;t know how to set cron jobs.

**If you didn&#39;t see any errors during installation, you are done!...Goto login.php and login with the default password, &quot;upsbadmin&quot;.**

Additional Steps For Windows Servers (or if a cron job was not created during installation)

Step 4: (Windows servers running IIS only. If you have Apache, you should be fine.) Make sure that your backup folders and sensitive files is not accessible to ANYONE. See [http://www.hosting.com/support/dedicated/IIS/passwordprotect/](http://www.hosting.com/support/dedicated/IIS/passwordprotect/)

**That&#39;s it! Everything should be ready to roll...**

# Quick Start Tutorial (if you can&#39;t figure it out)

UPSB backs up your files and databases to a zip file, in the background, according to the schedule you assign.

To get UPSB to do backups, you need to create a Backup Set. This is basically a group of files and db&#39;s.

Step 1: Login

Step 2: Click the &quot;Add Backup Set&quot; button

Step 3: Enter a name or alias for this set (you will use it to refer to the backup, that is all)

Step 4: Select the files you want to backup. Simply hit the check box beside a directory to backup that directory.

Step 5: Select the databases you want to backup (use CTRL or CMD to select multiple) Select remote databases by entering their connection settings using the defined syntax. You can select as many as you want.

Step 6: Enter the schedule (how often you want this backup set run in the background)

Step 7: Choose whether you want to be notified, if you want it to be copied to your remote FTP server (as defined in the settings), or if you want it sent as email attachments and submit!

To run a backup set manually (and create a backup right now) click the &quot;run&quot; button in the list of backup sets.

You can also edit or delete a backup set once it is created.

You can download backups created by the software by clicking the backup set&#39;s name (or double clicking the row).

#

# Support

Instead of a support forum, I use reddit. I created a subreddit [http://www.reddit.com/r/upsb/](http://www.reddit.com/r/upsb/) for you to post feature requests, calls for help, etc.. You need to first create a reddit account (which you can do in seconds), and then start posting stuff to the UPSB subreddit.

To contact me directly, email me at support@phpsitebackup.com .This is only for urgent requests, or if you could not find help on reddit.

# Documentation

Server Requirements

- MySQL and PHP5 or higher

- PHP ZipArchive() function. Practically all servers already have this. Contact your host or check php.ini if you are not sure.

- _Optional:_ PHP system() or exec() need to be working, and cURL needs to be installed (most Linux servers have these built-in). **This is NOT a full requirement**. If system(), exec(), or cURL do not work, you simply have to manually create a cron job after installation. No big deal.

Additionally, a modern browser is required for the admin to navigate UPSB. IE6 has not been tested.

Known Issues in v2.0

- On certain MySQl versions, this script will not backup VIEWS in MySQL databases. Tables will be backed up fine, but VIEWS will not be. A MySQL error will be generated in your log file. This is due to a Bug #20482 in MySQL. Also see here: [http://www.bigresource.com/Tracker/Track-mysql-Wz2QjELR/](http://www.bigresource.com/Tracker/Track-mysql-Wz2QjELR/)

Help Topics

For an updated FAQ, please visit: [http://codecanyon.net/item/ultimate-php-site-backup-v10/faq/96705](http://codecanyon.net/item/ultimate-php-site-backup-v10/faq/96705)

- My backup is running with errors

Step 1: Check your log file to see which files or folders are having a problem backing up

Step 2: Rectify the problem by seeing if the file is too big, or some other reason is causing it to fail.