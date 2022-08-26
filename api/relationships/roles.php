<?php
require_once '../../config/boot.php';
require_once '../../classes/System.class.php';
$system = new System( '../../config/host.json' );

session_start();

if (empty($_SESSION['user_id'])) {
	exit;
}

$searchPattern = isset($_REQUEST['searchPattern']) ? $_REQUEST['searchPattern'] : null;
$rolePlayerClass = isset($_REQUEST['rolePlayerClass']) ? $_REQUEST['rolePlayerClass'] : null;

//var_dump($_REQUEST);

header("Content-type: text/plain");

switch ($_SERVER["REQUEST_METHOD"]) {
	case 'GET' :
		echo Relationship::knownRolesToJson($searchPattern, $rolePlayerClass);
		exit;
		
	case 'POST' :
		exit;
		
	case 'DELETE' :
		exit;
}
?>