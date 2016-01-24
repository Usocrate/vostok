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

if (isset ( $_POST ['task'] )) {
	ToolBox::formatUserPost ( $_POST );
	switch ($_POST ['task']) {
		case 'newindustry' :
			if (! empty ( $_POST ['newindustry_name'] )) {
				$i = new Industry ();
				$i->setName ( $_POST ['newindustry_name'] );
				$i->toDB ();
			}
			break;
		case 'industries_merge' :
			if (isset ( $_POST ['industries_ids'] )) {
				$industriesToMerge = $system->getIndustriesFromIds ( $_POST ['industries_ids'] );
				while ( count ( $industriesToMerge ) > 1 ) {
					$result = array ();
					$result [] = $system->mergeIndustries ( $industriesToMerge [0], $industriesToMerge [1] );
					array_splice ( $industriesToMerge, 0, 2, $result );
				}
			}
			break;
		default :
			trigger_error ( 'La tâche à éxécuter est inconnue' );
	}
}
$doc_title = 'Les activités';
?>
<!doctype html>
<html lang="fr">
<head>
<title><?php echo APPLI_NAME ?>: Répartition des sociétés par activité</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<link rel="stylesheet" type="text/css" href="<?php echo PURE_SEEDFILE_URI ?>">

<script language="JavaScript" type="application/javascript" src="js/controls.js"></script>
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
		<div class="pure-u-1">
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" class="pure-form pure-form-stacked">
				<div class="pure-g-r">
					<div class="pure-u-1-2">
						<table class="pure-table">
							<thead>
								<tr>
									<th></th>
									<th>Activité</th>
									<th>Nombre</th>
								</tr>
							</thead>
							<tbody>
						<?php
						foreach ( $system->getIndustries () as $i ) {
							echo '<tr>';
							echo '<td><input name="industries_ids[]" type="checkbox" value="' . $i->getId () . '" /></td>';
							echo '<td>';
							echo $i->getName ();
							echo '</td>';
							echo '<td>';
							// print_r($row);
							echo '<a href="societies_list.php?society_newsearch=1&amp;industry_id=' . $i->getId () . '">';
							echo $i->getSocietiesNb ();
							echo '</a>';
							echo '</td>';
							echo '</tr>';
						}
						?>
					</tbody>
							<tfoot>
								<tr>
									<td colspan="3">
										<button type="button" onclick="check('industries_ids[]')">tout cocher</button>     /
										<button type="button" onclick="uncheck('industries_ids[]')">tout décocher</button> <label for="task">Pour la sélection :</label>
										<button type="submit" name="task" value="industries_merge" class="pure-button pure-button-primary">Fusionner</button>
									</td>
								</tr>
							</tfoot>
						</table>
					</div>
					<div class="pure-u-1-2">
						<label>nouvelle activité</label> <input name="newindustry_name" type="text" size="15" />
						<button name="task" type="submit" value="newindustry" class="pure-button pure-button-primary">déclarer</button>
					</div>
				</div>
			</form>
		</div>
		<div class="pure-u-1">
			<footer><?php include 'menu.inc.php'; ?></footer>
		</div>
	</div>
</body>
</html>