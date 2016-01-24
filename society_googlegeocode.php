<?php
require 'config/main.inc.php';

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

$adresse = $society->getAddress();
if (!empty($adresse)) {
	$url = "http://maps.google.com/maps/geo?q=".urlencode($adresse).'&key='.GOOGLE_MAPS_API_KEY.'&output=xml';
	$page = file_get_contents($url);
	$page = utf8_encode($page);
	$xml = simplexml_load_string($page);
	// $placemarks = $xml->xpath('/kml/Response/Placemark');
	echo $page;
	//var_dump($xml);
}
?>