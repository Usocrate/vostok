<?php
//
// web services permettant de gérer les variables de session
//
session_start();

// enregistrement d'une préférence
if ( isset($_REQUEST['focus']) ) {
    if (!isset($_SESSION['preferences'])) {
        $_SESSION['preferences'] = array();
    }
    if ( isset($_REQUEST['scope']) ) {
        if ( ! isset($_SESSION['preferences'][$_REQUEST['scope']] ) ) {
            $_SESSION['preferences'][$_REQUEST['scope']] = array();
        }
        $_SESSION['preferences'][$_REQUEST['scope']]['focus'] = $_REQUEST['focus'];
    }
}
?>