<?php
function __autoload($class_name) {
	$path = './classes/';
	if (is_file ( $path . $class_name . '.class.php' )) {
		include_once $path . $class_name . '.class.php';
	} elseif ($path . $class_name . '.interface.php') {
		include_once $path . $class_name . '.interface.php';
	}
}
$system = new System( './config/host.json' );

require_once 'config/boot.php';

session_start ();

if (empty ( $_SESSION ['user_id'] )) {
	header ( 'Location:login.php' );
	exit ();
} else {
	$user = new User ( $_SESSION ['user_id'] );
	$user->feed ();
}

if (isset ( $_POST ['task'] )) {
	ToolBox::formatUserPost($_POST);
	//print_r($_POST);
	switch ($_POST ['task']) {
		case 'roles_merge' :
			/*
			if (isset ( $_POST ['titlesToMerge'] )) {
				$titlesToMerge = $_POST['titlesToMerge'];
				while ( count($tilesToMerge) > 1 ) {
					$result = array ();
					$result[] = $system->mergeTitles(current($titlesToMerge), next($titlesToMerge));
					array_splice($titlesToMerge, 0, 2, $result);
				}
			}
			break;
			*/
		default :
			trigger_error ( 'La tâche à exécuter est inconnue' );
	}
}

$doc_title = 'Les rôles des sociétés';
?>
<!doctype html>
<html lang="fr">
<head>
    <title><?php echo ToolBox::toHtml($system->getAppliName()) ?>: Rôles</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link type="text/css" rel="stylesheet" href="<?php echo FONTAWESOME_CSS_URI ?>" integrity="<?php echo FONTAWESOME_CSS_URI_INTEGRITY ?>" crossorigin="anonymous" />    
    <link type="text/css" rel="stylesheet" href="<?php echo JQUERY_UI_CSS_THEME_URI ?>"></link>
    <script language="JavaScript" type="application/javascript" src="js/controls.js"></script>
    <link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>main.css"></link>
    <?php echo $system->writeHtmlHeadTagsForFavicon(); ?>
    <script type="text/javascript" src="<?php echo JQUERY_URI; ?>"></script>
    <script type="text/javascript" src="<?php echo JQUERY_UI_URI; ?>"></script>
    <script type="text/javascript" src="<?php echo BOOTSTRAP_JS_URI ?>" integrity="<?php echo BOOTSTRAP_JS_URI_INTEGRITY ?>" crossorigin="anonymous"></script>
</head>
<body>
<?php include 'navbar.inc.php'; ?>
<div class="container-fluid">
	<h1 class="bd-title"><?php echo ToolBox::toHtml($doc_title); ?></h1>

	<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
	<section>
	<table class="table">
		<thead>
			<tr>
				<th style="display:none"></th>
				<th><?php echo isset($_SESSION['role_list_sort']) && strcmp($_SESSION['role_list_sort'], 'Alphabetical')==0 ? 'Rôle' : 'Rôle <a href="'.$_SERVER['PHP_SELF'].'?newsort=alpha"><small><i class="fas fa-filter"></i></small></a>' ?></th>
			</tr>
		</thead>		
		<tbody>
			<?php
			foreach ( Relationship::getKnownRoles(null,'societyRole') as $r) {
				echo '<tr>';
				echo '<td style="display:none"><input name="roles[]" type="checkbox" value="'.ToolBox::toHtml($r).'" /></td>';
				echo '<td>';
				echo empty($r) ? '<a href="societiesHavingThatRole.php?role=">Indéterminé</a>' : '<a href="societiesHavingThatRole.php?role='.urlencode($r).'">'.ToolBox::toHtml($r).'</a>';
				echo '</td>';
				echo '</tr>';
			}
			?>
		</tbody>
		<tfoot style="display:none">
			<tr>
				<td colspan="2">
					<button type="submit" name="task" value="titles_merge" class="btn btn-default">Fusionner</button>
				</td>
			</tr>
		</tfoot>
	</table>
	</section>
	</form>
</div>	
</body>
</html>