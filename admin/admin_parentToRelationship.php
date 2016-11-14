<?php
function __autoload($class_name) {
	$path = '../classes/';
	if (is_file ( $path . $class_name . '.class.php' )) {
		include_once $path . $class_name . '.class.php';
	} elseif ($path . $class_name . '.interface.php') {
		include_once $path . $class_name . '.interface.php';
	}
}
$system = new System ( '../config/host.json' );

require 'config/boot.php';

session_start ();
ToolBox::getDBAccess ();

/**
 * critères de filtrage.
 */
$criterias = array ();
$criterias [] = '(society_parent_id <> "" OR society_parent_id IS NOT NULL)';

$page_rowset = $system->getSocietiesRowset ( $criterias );

// la sélection de societies
$societies = array ();
?>
<html>
<body>
<?php
while ( $row = mysql_fetch_assoc ( $page_rowset ) ) {
	$societies [] = new Society ();
	$societies [count ( $societies ) - 1]->feed ( $row );
	$societies [] = $s;
	$relationship = new Relationship ();
	$relationship->setItem ( $societies [count ( $societies ) - 1], 0 );
	$relationship->setItemRole ( 'filiale', 0 );
	$relationship->setItem ( $societies [count ( $societies ) - 1]->getParent (), 1 );
	$relationship->setItemRole ( 'maison-mère', 1 );
	$relationship->toDB ();
}
?>
</body>
</html>