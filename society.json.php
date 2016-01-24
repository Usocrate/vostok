<?php
require 'config/main.inc.php';

session_start();

if (empty($_SESSION['user_id'])) {
	header('Location:login.php');
	exit;
}

ToolBox::getDBAccess();

$society = new Society();
if (isset($_REQUEST['society_id'])) {
	$society->setId($_REQUEST['society_id']);
}
$society->initFromDB();
header("Content-type: text/plain");
echo $society->getJson();
?>