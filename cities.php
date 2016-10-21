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
ToolBox::getDBAccess ();

if (empty ( $_SESSION ['user_id'] )) {
	header ( 'Location:login.php' );
	exit ();
} else {
	$user = new User ( $_SESSION ['user_id'] );
	$user->feed ();
}

$rowset = $system->getSocietiesGroupByCityRowset ();
$doc_title = 'Les sociétés classées par ville';
?>
<!doctype html>
<html lang="fr">
<head>
<title><?php echo ToolBox::toHtml($system->getAppliName()) ?>: Répartition des sociétés par ville</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_URI ?>" type="text/css" />
<link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_THEME_URI ?>" type="text/css" />
<link rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>main.css" type="text/css">
<script type="text/javascript" src="<?php echo JQUERY_URI; ?>"></script>
<script type="text/javascript" src="<?php echo BOOTSTRAP_JS_URI; ?>"></script>
</head>
<body>
<?php include 'navbar.inc.php'; ?>
<div class="container-fluid">

		<h1><?php echo ToolBox::toHtml($doc_title); ?></h1>

		<table class="table">
			<thead>
				<tr>
					<th>Ville</th>
					<th>Nombre</th>
				</tr>
			</thead>
			<tbody>
		<?php
		while ( $row = mysql_fetch_assoc ( $rowset ) ) {
			echo '<tr>';
			echo '<td>';
			echo empty ( $row ['city'] ) ? '<small>n.c.</small>' : '<a href="societies_list.php?society_newsearch=1&amp;society_city=' . $row ['city'] . '">' . $row ['city'] . '</a>';
			echo '</td>';
			echo '<td>';
			echo '<span class="badge">';
			echo '<a href="societies_list.php?society_newsearch=1&amp;society_city=' . $row ['city'] . '">';
			echo $row ['nb'];
			echo '</a>';
			echo '</span>';
			echo '</td>';
			echo '</tr>';
		}
		?>
		</tbody>
		</table>
	</div>
</body>
</html>
