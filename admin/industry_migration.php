<?php
function __autoload($class_name) {
	$path = './classes/';
	if (is_file ( $path . $class_name . '.class.php' )) {
		include_once $path . $class_name . '.class.php';
	} elseif ($path . $class_name . '.interface.php') {
		include_once $path . $class_name . '.interface.php';
	}
}
$system = new System ( './config/host.json' );

require '../config/boot.php';

session_start ();
// l'utilisateur doit être identifié
if (empty ( $_SESSION ['user_id'] )) {
	header ( 'Location:login.php' );
	exit ();
}

ToolBox::getDBAccess ();

$sql = 'SELECT society_id, society_industry FROM society WHERE society_industry IS NOT NULL';
$rowset = mysql_query ( $sql );
while ( $row = mysql_fetch_array ( $rowset ) ) {
	$i = new Industry ();
	$i->setName ( $row ['society_industry'] );
	if (! $i->identifyFromName ()) {
		$i->toDB ();
	}
	$sql = 'INSERT INTO society_industry';
	$sql .= ' SET society_id=' . $row ['society_id'] . ', industry_id=' . $i->getId ();
	mysql_query ( $sql );
}
?>