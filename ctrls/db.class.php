<?php
/*
Ultimate PHP Site Backup
Author: Isa Hassen

Class: db
Description: This class does pretty much anything to do with databases.
*/

$root = dirname(dirname(__FILE__));

class db {


    public $settings,$con;


    public function __construct() {
        global $root;

        $ini = "$root/config.ini.php";
        $this->settings = parse_ini_file($ini);

        $this->con = mysql_connect($this->settings[DB_HOST],$this->settings[DB_USERNAME],$this->settings[DB_PASSWORD]);
        if (!$this->con) {
            die('Could not connect: ' . mysql_error());
        }

        if (!mysql_select_db($this->settings[DB_NAME], $this->con)) die ('Connected succesfully, but could not use database: '.$this->settings[DB_NAME]);

        $pw = mysql_fetch_assoc(mysql_query("SELECT admin_pw FROM admin WHERE username = 'admin'"));
		$ftp = mysql_fetch_assoc(mysql_query("SELECT admin_pw FROM admin WHERE username = 'ftpconfigstring'"));
        $this->settings[ADMIN_PW] = $pw['admin_pw'];
		$this->settings[FTP_CONFIG] = $ftp['admin_pw'];
		
		if (file_exists("$root/models/configs/demo.conf")){
			define("DEMO", true);
		}
		else define("DEMO", false);
    }

    private function query ($queryTxt) {
        mysql_select_db($this->settings[DB_NAME], $this->con);
        return mysql_query($queryTxt, $this->con);
    }
	
	public function testMysqlSettings($host, $username, $password, $db, $show_errors = false) {
		
		$testlink = @mysql_connect($host, $username, $password, true);
		$testdb = @mysql_select_db($db, $testlink);
		
		if (!$testlink) {
			if ($show_errors) $_SESSION['ermsgs'][] = "Failed to connect to '$host', with username '$username', and password '$password'.";
			return false;
		}
		elseif ($testdb) {
			mysql_close($testlink);
			return true;
		}
		else {
			mysql_close($testlink);
			if ($show_errors) $_SESSION['ermsgs'][] = "Connection successful; Failed to select database $db";
			return false;
		}
	}

    public function changePassword($oldpw, $newpw) {
        $newpw = sha1(trim($newpw).$this->settings[SALT]);
        $this->query("UPDATE admin SET admin_pw = '$newpw' WHERE username = 'admin'");
        if (mysql_affected_rows() === 1) {
            return true;
        }
        else {
            return false;
            $_SESSION['ermsgs'][] = "Problem changing password. ".mysql_error();
        }
    }
	
	public function changeFTPConfig($ftpconfigstring) {
		$this->query("UPDATE admin SET admin_pw = '$ftpconfigstring' WHERE username = 'ftpconfigstring'");
		if (mysql_affected_rows() === 1) {
			return true;
		}
		else {
			return false;
			$_SESSION['ermsgs'][] = "Problem FTP settings. ".mysql_error();
		}
	}

    public function getBcSList () {
        $BcS = Array();
        $result = $this->query("SELECT * FROM bsets");

        while ($row = mysql_fetch_assoc($result)) {
            $row['lastruntime'] = $this->getBcSRuns($row[id], "lastruntime");
            $BcS[] = $row;
        }

        return $BcS;
    }

    public function getBcSInfo($id) {
        $r = mysql_fetch_assoc($this->query("SELECT * FROM bsets WHERE id = '$id'"));

        if (!empty($r)) {
			
			if ($r['notify'] == "0") $r['notify'] = false;
			if ($r['email_backup'] == "0") $r['email_backup'] = false;
			
            return $r;
        }
        else return false;
    }

    public function addBcSEntry($id, $name, $files, $dbs, $schedule, $notify, $email_backup, $ftp_upload, $store_local) {
        return $this->query("INSERT INTO bsets(id,name,files,dbs,schedule,notify,email_backup,ftp_upload,store_local) VALUES('$id','$name','$files','$dbs','$schedule','$notify','$email_backup','$ftp_upload','$store_local')");
    }

    public function editBcSEntry($id, $name, $schedule, $notify, $email_backup, $ftp_upload, $store_local) {
        return $this->query("UPDATE bsets SET name = '$name', schedule = '$schedule', notify = '$notify', email_backup = '$email_backup', ftp_upload = '$ftp_upload', store_local = '$store_local' WHERE id = '$id'");
    }

    public function deleteBcS($id) {
        return $this->query("DELETE FROM bsets WHERE bsets.id = '$id' LIMIT 1");
    }

    public function getBcSRuns($id, $options = "") {
        $array = Array();
        $result = $this->query("SELECT * FROM runs WHERE bset = '$id' ORDER BY time ASC");

        while($r = mysql_fetch_assoc($result)) {
            $array[] = $r;
        }

        $returnarray = array_reverse($array, true);

        if (!empty($returnarray)) {
            if ($options == "lastruntime") {
                $lastrun = reset($returnarray);

                return $lastrun['time'];
            }
            else return $returnarray;
        }
        else return false;
    }

    public function addRunEntry($bset, $time, $zipfile, $logfile, $success) {
        $time = date("Y-m-d H:i:s", $time);
        $id = uniqid();

        return $this->query("INSERT INTO runs(id,bset,time,zipfile,logfile,success) VALUES('$id','$bset','$time','$zipfile','$logfile','$success')");
    }

    public function deleteRunEntry($id) {
        $success = true;
        $run = mysql_fetch_assoc($this->query("SELECT * FROM runs WHERE id = '$id'"));

        if (!$run) {$success = false; echo "Information about $id fail";}

        //Delete files and db entry
        if (!empty($run[zipfile])) {
            if (!@unlink($run[zipfile])) {
                $success = false;
                echo "Failed to delete zip.";
            }
        }else {$success = false; echo "No zip file found.";}

        if (!empty($run[logfile])) {
            if (!@unlink($run[logfile])) {
                $success = false; echo "Failed to delete log file.";
            }
        }else {$success = tru; echo "No log file found.";}

        if (!file_exists($run[zipfile]) && !file_exists($run[logfile])) {
            if ($this->query("DELETE FROM runs WHERE runs.id = '$id' LIMIT 1")) {
                $success = true;
            }
            else {$success = false; echo "Failed to remove entry from db.";}
        }
        else $success = false;

        return $success;
    }


    public function getDBList($host, $username, $pass) {

        $con = mysql_connect($host,$username,$pass, true);
        if (!$con) {
            return 'Could not connect: ' . mysql_error();
        }
        else {
            $dbArray = Array();
            $list = mysql_list_dbs();

            while ($row = mysql_fetch_row($list)) {
                if ($row[0] != "information_schema") {
                    $dbArray[] = $row[0];
                }
            }
			mysql_close($con);
            return $dbArray;
        }
    }

    public function backup_tables($host,$user,$pass,$name,$tables = '*',$destination) {

        $success = true;

        $link = mysql_connect($host,$user,$pass,true);

        if (!$link) {
            $success = false;
            echo 'Error connecting to database host. '.mysql_error();
        }
        elseif (mysql_select_db($name,$link)) {

        //get all of the tables
            if($tables == '*') {
                $tables = array();
				$views = array();
                $result = mysql_query('SHOW FULL TABLES');
                while($row = mysql_fetch_row($result)) {
					if ($row[1] == "BASE TABLE") $tables[] = $row[0];
					if ($row[1] == "VIEW") $views[] = $row[0];
                }
            }
            else {
                $tables = is_array($tables) ? $tables : explode(',',$tables);
            }

            //cycle through
            foreach($tables as $table) {
				echo "<br/>--Backing up table $table";
                $result = mysql_query('SELECT * FROM '.$table);
                $num_fields = mysql_num_fields($result);

                $row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '.$table));
                $return.= "\n\n".$row2[1].";\n\n";

                for ($i = 0; $i < $num_fields; $i++) {
                    while($row = mysql_fetch_row($result)) {
                        $return.= 'INSERT INTO '.$table.' VALUES(';
                        for($j=0; $j<$num_fields; $j++) {
                            $row[$j] = addslashes($row[$j]);
                            $row[$j] = ereg_replace("\n","\\n",$row[$j]);
                            if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
                            if ($j<($num_fields-1)) { $return.= ','; }
                        }
                        $return.= ");\n";
						
						if (strlen($return) > 1048576){
							$this->addToFile($destination, $return);
							unset ($return);
							$return = "";
						}
                    }
                }
                $return.="\n\n\n";
            }
			foreach ($views as $view){
				echo "<br/>--Backing up structure for VIEW $view";
				$row2 = mysql_fetch_row(mysql_query('SHOW CREATE VIEW '.$view));
				echo mysql_error();
				$return.= "\n\n".$row2[1].";\n\n";
			}
        }
        else {
            $success = false;
            echo "Error selecting database '$name'. ".mysql_error();
        }
		
		$this->addToFile($destination, $return);        

        return $success;
        mysql_close($link);
        
    }
	
	private function addToFile($destination, $content) {
		//save file
        $handle = @fopen($destination,'a+');

        if (!fwrite($handle,$content)) {
			return false;
            echo "<div class=\"solid-error\">Error saving database dump to '$destination'</div>";
        }
		fclose($handle);
	}


}