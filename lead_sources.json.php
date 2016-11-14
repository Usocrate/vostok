<?php
function __autoload($class_name) {
	$path = './classes/';
	if (is_file ( $path . $class_name . '.class.php' )) {
		include_once $path . $class_name . '.class.php';
	} elseif ($path . $class_name . '.interface.php') {
		include_once $path . $class_name . '.interface.php';
	}
}
$system = new System( './config/host.json' );

require 'config/boot.php';

session_start();

if (empty($_SESSION['user_id'])) {
	exit;
}

echo empty($_REQUEST['query']) ? Lead::knownSourcesToJson() : Lead::knownSourcesToJson($_REQUEST['query']);
?>