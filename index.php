<?php

//The manage class loads all other necessary classes as well, such as db and smarty
require_once('ctrls/manage.class.php');
$manage = new manage;

//If not logged in, redirect to login.php
$manage->checkLoginAndDirect();

//Assign array of backup sets
$smarty->assign("backup_sets",$manage->db->getBcSList());
$smarty->assign("settings", $manage->db->settings);
$smarty->assign("docroot", str_replace("//", "/", dirname($_SERVER[DOCUMENT_ROOT])."/"));
$manage->checkSecurity(); //Checks for isssues with the app and assigns messages to $_SESSION
//Assign array of databases
$smarty->assign("dbArray", $manage->db->getDBList($manage->db->settings[DB_HOST], $manage->db->settings[DB_USERNAME], $manage->db->settings[DB_PASSWORD]));
$manage->displayMessagesWithSmarty("display:false"); //Assigns any generated $_SESSION messages to smarty WITHOUT displaying

//Display template
$smarty->display('index.tpl');

?>
