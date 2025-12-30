<?php
//
// web services permettant de gérer les variables de session
//
require_once '../config/boot.php';
require_once '../classes/System.php';
$system = new System( '../config/host.json' );
$systemIdInSession = $system->getAppliName();

session_start();

// enregistrement d'une préférence
if ( isset($_REQUEST['focus']) ) {
	if (!isset($_SESSION[$systemIdInSession]['preferences'])) {
		$_SESSION[$systemIdInSession]['preferences'] = array();
    }
    if ( isset($_REQUEST['scope']) ) {
    	if ( ! isset($_SESSION[$systemIdInSession]['preferences'][$_REQUEST['scope']] ) ) {
    		$_SESSION[$systemIdInSession]['preferences'][$_REQUEST['scope']] = array();
        }
        $_SESSION[$systemIdInSession]['preferences'][$_REQUEST['scope']]['focus'] = $_REQUEST['focus'];
    }
}
?>