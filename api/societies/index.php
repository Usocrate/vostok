<?php
require_once '../../config/boot.php';
require_once '../../classes/System.class.php';
$system = new System( '../../config/host.json' );

session_start();

if (empty($_SESSION['user_id'])) {
	exit;
}

header("Content-type: text/plain");

switch ($_SERVER["REQUEST_METHOD"]) {
	case 'GET' :
		$society = new Society();
		if (isset($_GET['id'])) {
			$society->setId($_GET['id']);
		}
		$society->initFromDB();
		echo $society->getJson();
		exit;
		
	case 'POST' :
		ToolBox::formatUserPost($_POST);
		$fb = new Feedback();
		
		switch($_POST['task']) {
			case 'deletion':
				if (isset($_POST['id'])) {
					$society = new Society($_POST['id']);
					
					if ($society->delete()) {
						$fb->setMessage('C\'est oublié.');
						$fb->setType('success');
						$fb->addDatum('location', $system->getAppliUrl());
					} else {
						$fb->setMessage('Mince, problème !');
						$fb->setType('error');
					}
				}
				break;
		}
		echo $fb->toJson();
		exit;
		
	case 'DELETE' :
		exit;
}
?>