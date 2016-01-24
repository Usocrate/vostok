<?php
require 'config/main.inc.php';

session_start();

if (empty($_SESSION['user_id'])) {
	exit;
}

ToolBox::getDBAccess();

echo empty($_REQUEST['query']) ? Lead::knownTypesToJson() : Lead::knownTypesToJson($_REQUEST['query']);