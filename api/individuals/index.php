<?php

require_once '../../config/boot.php';
require_once '../../classes/System.php';
$system = new System( '../../config/host.json' );
$systemIdInSession = $system->getAppliName();

session_start ();

if (empty ( $_SESSION[$systemIdInSession]['user_id'] )) {
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
					$i = new Individual($_POST['id']);
				
					if ($i->delete()) {
						$fb->setMessage('C\'est oublié.');
						$fb->setType('success');
						$fb->addDatum('location', $system->getAppliUrl());
					} else {
						$fb->setMessage('Mince, problème !');
						$fb->setType('error');
					}
				}
				break;
			case 'updateDescription' :
				if (isset($_POST['id'])) {
					$i = new Individual($_POST['id']);
					$i->setDescription($_POST['description']);
					
					if ($i->toDB()) {
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
		exit;
}
