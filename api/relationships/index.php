<?php
function __autoload($class_name) {
	$path = '../../classes/';
	if (is_file ( $path . $class_name . '.class.php' )) {
		include_once $path . $class_name . '.class.php';
	} elseif ($path . $class_name . '.interface.php') {
		include_once $path . $class_name . '.interface.php';
	}
}
$system = new System ( '../../config/host.json' );

require '../../config/boot.php';

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
