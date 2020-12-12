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

if (isset ( $_GET ['individual_id'] ) && !empty ( $_GET ['individual_id'] )) {
	$criteria ['individual_id'] = $_GET ['individual_id'];
}

if (isset ( $_GET ['individual_lastName'] ) && !empty ( $_GET ['individual_lastName'] )) {
	$criteria ['individual_lastName'] = $_GET ['individual_lastName'];
}

if (isset ( $_GET ['individual_firstName'] ) && !empty ( $_GET ['individual_firstName'] )) {
	$criteria ['individual_firstName'] = $_GET ['individual_firstName'];
}

if (isset ( $_GET ['society_id'] ) && ! empty ( $_GET ['society_id'] )) {
	$criteria ['society_id'] = $_GET ['society_id'];
}

$data = $system->getMemberships ( $criteria );
echo json_encode ( $data, JSON_UNESCAPED_UNICODE);
