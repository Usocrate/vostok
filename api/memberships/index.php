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
		exit;
		
	case 'POST' :
		ToolBox::formatUserPost($_POST);
		$fb = new Feedback();

		switch($_POST['task']) {
			case 'deletion':
				if (isset($_POST['id'])) {
					$membership = new Membership($_POST['id']);
					if ($membership->feed()) {
						$membership->feedIndividual();
					}
					$individual = $membership->getIndividual();
					
					if ( $membership->delete() ) {
						$fb->setMessage('La participation est oubliée.');
						$fb->setType('success');
						if (is_a($individual, 'Individual')) {
							$fb->addDatum('location', 'individual.php?individual_id='.$individual->getId());
						} else {
							$fb->addDatum('location', $system->getAppliUrl());
						}
					} else {
						$fb->setMessage('La participation n\'a pu être effacée');
						$fb->setType('error');
					}
				}
				break;
		}
		echo $fb->toJson();
		exit;
		
	case 'DELETE' :
		break;
}
