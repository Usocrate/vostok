<?php
require_once '../../config/boot.php';
require_once '../../classes/System.php';
$system = new System( '../../config/host.json' );
$systemIdInSession = $system->getAppliName();

session_start();

if (empty($_SESSION[$systemIdInSession]['user_id'])) {
	exit;
}

echo empty($_REQUEST['query']) ? Lead::knownSourcesToJson() : Lead::knownSourcesToJson($_REQUEST['query']);
?>