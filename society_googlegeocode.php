<?php
require_once 'config/boot.php';
require_once 'classes/System.php';
$system = new System( './config/host.json' );
$systemIdInSession = $system->getAppliName();

session_start();

if (empty($_SESSION[$systemIdInSession]['user_id'])) {
	header('Location:login.php');
	exit;
}

$society = new Society();
if (isset($_REQUEST['society_id'])) {
	$society->setId($_REQUEST['society_id']);
	$society->feed();
}

$address = $society->getAddress();
if (!empty($address)) {
	echo $society->getGoogleGeocodeAsJson();
}
?>