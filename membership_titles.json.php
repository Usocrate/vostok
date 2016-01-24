<?php
require 'config/main.inc.php';

session_start();

if (empty($_SESSION['user_id'])) {
	exit;
}

ToolBox::getDBAccess();

echo empty($_REQUEST['query']) ? Membership::knownTitlesToJson() : Membership::knownTitlesToJson($_REQUEST['query']);
?>