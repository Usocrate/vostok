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
    <title><?php echo ToolBox::toHtml($system->getAppliName()) ?>: Répartition des sociétés par activité</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_URI ?>" type="text/css" />
    <link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_THEME_URI ?>" type="text/css" />
    <script language="JavaScript" type="application/javascript" src="js/controls.js"></script>
    <link rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>main.css" type="text/css">
    <script type="text/javascript" src="<?php echo JQUERY_URI; ?>"></script>
    <script type="text/javascript" src="<?php echo JQUERY_UI_URI; ?>"></script>
    <script type="text/javascript" src="<?php echo BOOTSTRAP_JS_URI; ?>"></script>
</head>
<body>
<?php include 'navbar.inc.php'; ?>
<div class="container-fluid">
	<h1><?php echo ToolBox::toHtml($doc_title); ?></h1>

	<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
		<div class="row">
			<div class="col-md-6">
				<section>
				<table class="table">
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
        					echo '<a href="societies_list.php?society_newsearch=1&amp;industry_id=' . $i->getId () . '">';
        					echo $i->getName ();
        					echo '</a>';
        					echo '</td>';
        					echo '<td>';
        					echo '<span class="badge">';
        					echo '<a href="societies_list.php?society_newsearch=1&amp;industry_id=' . $i->getId () . '">';
        					echo $i->getSocietiesNb ();
        					echo '</span>';
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
								<button type="submit" name="task" value="industries_merge" class="btn btn-primary">Fusionner</button>
							</td>
						</tr>
					</tfoot>
				</table>
				</section>
			</div>
			<div class="col-md-6">
				<section>
    				<label>nouvelle activité</label> <input name="newindustry_name" type="text" size="15" />
    				<button name="task" type="submit" value="newindustry" class="btn btn-primary">déclarer</button>
				</section>
			</div>
		</div>
	</form>
</div>	
</body>
</html>