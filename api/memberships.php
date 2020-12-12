<?php
function __autoload($class_name) {
	$path = '../classes/';
	if (is_file ( $path . $class_name . '.class.php' )) {
		include_once $path . $class_name . '.class.php';
	} elseif ($path . $class_name . '.interface.php') {
		include_once $path . $class_name . '.interface.php';
	}
}
$system = new System ( '../config/host.json' );

require '../config/boot.php';

session_start ();

if (empty ( $_SESSION ['user_id'] )) {
	exit ();
}

$criteria = array ();

//var_dump($_GET);

if (isset ( $_GET ['individual_id'] ) && ! empty ( $_GET ['individual_id'] )) {
	$criteria ['individual_id'] = $_GET ['individual_id'];
} elseif (isset($_GET ['individual_firstname']) && isset($_GET ['individual_lastname'])) {
	$i = new Individual();
	$i->setFirstName(urldecode($_GET ['individual_firstname']));
	$i->setLastName(urldecode($_GET ['individual_lastname']));
	$i->identifyFromName();
	if ($i->hasId()) {
		$criteria ['individual_id'] = $i->getId();
	}
	unset($i);
}

if (isset ( $_GET ['society_id'] ) && ! empty ( $_GET ['society_id'] )) {
	$criteria ['society_id'] = $_GET ['society_id'];
}

$data = $system->getMemberships ( $criteria );

//header ( 'charset=utf-8' );
echo json_encode ( $data, JSON_UNESCAPED_UNICODE);
