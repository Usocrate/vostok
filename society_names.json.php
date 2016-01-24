<?php
require 'config/main.inc.php';

session_start();

if (empty($_SESSION['user_id'])) {
	exit;
}

ToolBox::getDBAccess();

echo empty($_REQUEST['query']) ? Society::knownNamesToJson() : Society::knownNamesToJson($_REQUEST['query']);
?>