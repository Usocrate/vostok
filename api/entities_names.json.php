<?php
require_once '../config/boot.php';
require_once '../classes/System.class.php';
$system = new System( '../config/host.json' );

session_start();

if (empty($_SESSION['user_id'])) {
	exit;
}

$data = empty($_REQUEST['query']) ? $system->getEntities() : $system->getEntities(array("name substring"=>$_REQUEST['query']));

//echo '{"entities":' . json_encode ( $data ) . '}';

echo json_encode ( $data, JSON_UNESCAPED_UNICODE);

?>