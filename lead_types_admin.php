<?php
require_once 'config/main.inc.php';

session_start ();
ToolBox::getDBAccess ();

if (empty ( $_SESSION ['user_id'] )) {
	header ( 'Location:login.php' );
	exit ();
} else {
	$user = new User ( $_SESSION ['user_id'] );
	$user->feed ();
}

$doc_title = 'Type de piste : Fusionner 2 catégories';
$messages = array ();

// DataBase Connection
ToolBox::getDBAccess ();

if (isset ( $_POST ['task'] )) {
	ToolBox::formatUserPost ( $_POST );
	switch ($_POST ['task']) {
		case 'lead_types_merge' :
			if (isset ( $_POST ['lead_type_todrop'] ) && isset ( $_POST ['lead_type_ref'] )) {
				$messages [] = $system->mergeLeadTypes ( $_POST ['lead_type_ref'], $_POST ['lead_type_todrop'] ) ? 'Le type ' . $_POST ['lead_type_ref'] . ' est maintenant le type de référence.' : 'échec de la fusion des types';
			}
			break;
		default :
			$messages [] = 'La tâche demandée (' . $_POST ['task'] . ') est inconnue.';
	}
}
?>
<!doctype html>
<html lang="fr">
<head>
<title><?php echo $doc_title ?></title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<link rel="stylesheet" type="text/css" href="<?php echo PURE_SEEDFILE_URI ?>">
<link rel="stylesheet" href="<?php echo SKIN_URL ?>main.css" type="text/css">
<link rel="stylesheet" href="<?php echo SKIN_URL ?>pure-skin-vostok.css" type="text/css">
</head>
<body class="pure-skin-vostok">
	<div class="pure-g-r">
		<div class="pure-u-1 ban">
			<header>
				<div class="brand">
					<a href="<?php echo APPLI_URL?>"><?php echo ToolBox::toHtml(APPLI_NAME) ?></a>
				</div><?php echo ToolBox::toHtml($doc_title); ?></header>
		</div>
		<?php
		if (count ( $messages ) > 0) {
			echo '<div class="pure-u-1">';
			foreach ( $messages as $m ) {
				echo '<p>' . $m . '</p>';
			}
			echo '</div>';
		}
		?>
		<div class="pure-u-1">
			<form action="<?php $_SERVER['PHP_SELF'] ?>" method="post" class="pure-form">
				<label for="lead_type_todrop">La catégorie obsolète</label> <select name="lead_type_todrop">
					<?php echo Lead::getKnownTypesAsOptionsTags()?>
				</select><small> à remplacer par </small> <label>La catégorie référence</label> <select name="lead_type_ref">
					<?php echo Lead::getKnownTypesAsOptionsTags()?>
				</select> <input name="task" type="hidden" value="lead_types_merge" />
				<button type="submit" class="pure-button pure-button-primary">Ok</button>
			</form>
		</div>
		<div class="pure-u-1">
			<footer><?php include 'menu.inc.php'; ?></footer>
		</div>
	</div>
</body>
</html>
