<?php
require_once '../../config/boot.php';
require_once '../../classes/System.class.php';
$system = new System( '../../config/host.json' );

session_start ();

if (empty ( $_SESSION ['user_id'] )) {
	exit;
}

header("Content-type: text/plain");

switch ($_SERVER["REQUEST_METHOD"]) {
	case 'GET' :
		exit;
		
	case 'POST' :
		ToolBox::formatUserPost($_POST);
		$fb = new Feedback();

		switch($_POST['task']) {
			case 'deletion':
				if (isset($_POST['id'])) {
					$relationship = new Relationship($_POST['id']);
					
					if ( $relationship->delete() ) {
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
