<?php
require_once '../../config/boot.php';
require_once '../../classes/System.class.php';
$system = new System( '../../config/host.json' );

session_start();

if (empty($_SESSION['user_id'])) {
	exit;
}

echo empty($_REQUEST['query']) ? Lead::knownTypesToJson() : Lead::knownTypesToJson($_REQUEST['query']);