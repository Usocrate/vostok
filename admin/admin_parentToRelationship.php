<?php
	require 'config/main.inc.php';
	
	session_start();
	ToolBox::getDBAccess();
	
	/**
	 * critères de filtrage.
	 */
	$criterias = array();
	$criterias[] = '(society_parent_id <> "" OR society_parent_id IS NOT NULL)';

	$page_rowset = $system->getSocietiesRowset($criterias);
	
	//	la sélection de societies
	$societies = array();
?>
<html>
<body class="pure-skin-vostok">
<?php 	
while ($row = mysql_fetch_assoc($page_rowset)) {
	$societies[] = new Society();
	$societies[count($societies)-1]->feed($row);
	$societies[] = $s;
	$relationship = new Relationship();
	$relationship->setItem($societies[count($societies)-1], 0);
	$relationship->setItemRole('filiale', 0);
	$relationship->setItem($societies[count($societies)-1]->getParent(), 1);
	$relationship->setItemRole('maison-mère', 1);
	$relationship->toDB();			
}
?>
</body>
</html>