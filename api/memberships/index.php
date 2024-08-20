<?php
require_once '../../config/boot.php';
require_once '../../classes/System.php';
$system = new System( '../../config/host.json' );

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
					$m = new Membership($_POST['id']);
					if ($m->feed()) {
						$m->feedIndividual();
					}
					$individual = $m->getIndividual();
					
					if ( $m->delete() ) {
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
				
			case 'updateDescription' :
				if (isset($_POST['id'])) {
					$m = new Membership($_POST['id']);
					$m->setDescription($_POST['description']);
					
					if ($m->toDB()) {
						$fb->setMessage('C\'est enregistré.');
						$fb->setType('success');
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
		break;
}
