<?php
require_once 'ctrls/manage.class.php';
$manage = new manage;

$smarty->display('header.tpl');

if(isset($_GET['goAfterLogin'])) {
	$_SESSION['msgs'][] = "<b>Please login first, and I will direct you to that page.</b>";
	$smarty->assign("goAfterLogin", $_GET['goAfterLogin']);
}

$manage->displayMessagesWithSmarty(); //Assigns AND DISPLAYS any generated $_SESSION vars to smarty

if ($manage->is_logged_in()) echo 'You are logged in, my friend. Head over to your <a href="index.php">control panel</a>.';
else $smarty->display('login_form.tpl');

if (DEMO) echo "<p>Welcome to the demo version of Ultimate PHP Site Backup. Some features have been disabled. The password is 'admin'.</p>";

$smarty->display('footer.tpl');
?>