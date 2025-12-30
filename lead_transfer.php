<?php
require_once 'config/boot.php';
require_once 'classes/System.php';
$system = new System( './config/host.json' );
$systemIdInSession = $system->getAppliName();

session_start();

if (empty ($_SESSION[$systemIdInSession]['user_id'])) {
	header('Location:login.php');
	exit;
} else {
	$user = new User($_SESSION[$systemIdInSession]['user_id']);
	$user->feed();
}

// messages à délivrer
$messages = array();

// Formatage des données saisies par l'utilisateur
if (isset($_POST)) {
	ToolBox::formatUserPost($_POST);
}

if (isset($_REQUEST['lead_id']) && is_numeric($_REQUEST['lead_id'])) {
	//
	// la piste est connue
	//
	$lead = new Lead($_REQUEST['lead_id']);
	//$lead->feed();
	//$society = $lead->getSociety();
	//if (isset($society)) $society->feed();
}

if (isset($_REQUEST['cmd'])) {
	switch($_REQUEST['cmd']) {
		case 'transfer' :
			if (!empty($_REQUEST['targetSociety_id'])) {
				$lead->setSociety(new Society($_REQUEST['targetSociety_id']));
				if ($lead->toDB()) {
					header('Location:society.php?society_id='.$_REQUEST['targetSociety_id']);
					exit;
				}
			}
			break;
	}
}