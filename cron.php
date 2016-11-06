<?php
//The manage class loads all other necessary classes as well, such as db and smarty
require_once('ctrls/manage.class.php');
$manage = new manage;

ob_start(); //Start recording for log file
$now = time();

echo "CRON_LAST_RUN = $now";
echo "\n; Cron.php last run on ".date("r",$now);

echo "\n\n ;Running cron.php...";

$bsets = $manage->db->getBcSList();

foreach ($bsets as $set) {
    $s = $set['schedule'];
    $lastrun = strtotime($set['lastruntime']);
    
    if ($s == "30min") $timediff = 1800;
    elseif ($s == "hourly") $timediff = 3600;
    elseif ($s == "daily") $timediff = 86400;
    elseif ($s == "48hrs") $timediff = 172800;
    elseif ($s == "72hrs") $timediff = 259200;
    elseif ($s == "weekly") $timediff = 604800;
    elseif ($s == "monthly") $timediff = 2505600;
    else {echo "\n;Blank schedule for $set[name]."; $timediff = false;}
    
    //Calculate time difference since last run to see if it is necessary to run now
    if ($timediff) {
        if ($now - $lastrun >= $timediff) {
            $manage->runBcS($set[id]);
            echo "\n;Ran backup set $set[name]...(schedule: $s | last run ".$manage->getRelativeDate($lastrun).")";
        }
        else echo "\n;Do not need to run backup set $set[name] as per schedule: $s | last run ".$manage->getRelativeDate($lastrun);
    }
    
    
    $runs = $manage->db->getBcSRuns($set[id]);
    $num = count($runs);

    //Delete old backups
    while ($num > $manage->db->settings[MAX_BACKUPS_STORED]) {
        $oldest_run = end($runs);
        echo "\n; --Deleting old backup of $set[name] created ".$manage->getRelativeDate($oldest_run[time]);
        $manage->db->deleteRunEntry($oldest_run[id]);
        $num--;
    }
}

echo "\n\n Done running cron.php.";

$log = ob_get_contents();

ob_end_clean(); //End recording for log file.

$fh = @fopen("backup_logs/cronlog.txt", "w");
if ($fh){
    if (!fwrite($fh, $log)) {
        echo "Failed to write log file.";
    }
    else {
        if ($manage->is_logged_in()) echo nl2br($log);
        else echo "Cron.php ran. Wrote output to logfile.";
    }
    fclose($fh);
}

unset ($_SESSION['gmsgs']);

?>
