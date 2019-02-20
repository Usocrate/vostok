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

$searchPattern = isset($_REQUEST['searchPattern']) ? $_REQUEST['searchPattern'] : null;
$roleType = isset($_REQUEST['roleType']) ? $_REQUEST['roleType'] : null;

//var_dump($_REQUEST);

echo Relationship::knownRolesToJson($searchPattern, $roleType);
?>