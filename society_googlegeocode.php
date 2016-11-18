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
	header('Location:login.php');
	exit;
}
ToolBox::getDBAccess();

$society = new Society();
if (isset($_REQUEST['society_id'])) {
	$society->setId($_REQUEST['society_id']);
}
$society->initFromDB();

$address = $society->getAddress();
if (!empty($address)) {
	//echo print_r(json_decode($society->getGoogleGeocodeAsJson()));
	echo $society->getGoogleGeocodeAsJson();
}
?>