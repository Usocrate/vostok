<?php
require_once '../config/boot.php';
require_once '../classes/System.class.php';
$system = new System( '../config/host.json' );

session_start();

if (empty($_SESSION['user_id'])) {
	exit;
}

$count_max = isset($_REQUEST['count_max']) ? $_REQUEST['count_max'] : null;

$data = $system->getEntities($_REQUEST['name_substring'], 0, $count_max);

echo json_encode ( $data, JSON_UNESCAPED_UNICODE);

?>