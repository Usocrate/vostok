<?php
require_once 'config/boot.php';
require_once 'classes/System.class.php';
$system = new System( 'config/host.json' );

session_start ();

if (empty ( $_SESSION ['user_id'] )) {
	header ( 'Location:login.php' );
	exit ();
} else {
	$user = new User ( $_SESSION ['user_id'] );
	$user->feed();
}

$doc_title = 'Résultat de la recherche';
$name_substring = isset($_REQUEST['name_substring']) ? $_REQUEST['name_substring'] : '';
$count_max = 70;
$entities = $system->getEntities($name_substring, 0, $count_max);

?>
<!doctype html>
<html lang="fr">
<head>
    <title><?php echo $system->getAppliName() ?>: Résultat de la recherche</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
    <link type="text/css" rel="stylesheet" href="<?php echo FONTAWESOME_CSS_URI ?>" integrity="<?php echo FONTAWESOME_CSS_URI_INTEGRITY ?>" crossorigin="anonymous" />    
    <link type="text/css" rel="stylesheet" href="<?php echo JQUERY_UI_CSS_THEME_URI ?>"></link>
    <link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>theme.css"></link>
    <?php echo $system->writeHtmlHeadTagsForFavicon(); ?>
    <script type="application/javascript" src="js/controls.js"></script>
	<script src="<?php echo JQUERY_URI; ?>"></script>
	<script src="<?php echo JQUERY_UI_URI; ?>"></script>
	<script src="js/imagesloaded.pkgd.min.js"></script>
	<script src="<?php echo BOOTSTRAP_JS_URI ?>" integrity="<?php echo BOOTSTRAP_JS_URI_INTEGRITY ?>" crossorigin="anonymous"></script>
</head>
<body>
<?php include 'navbar.inc.php'; ?>
<div class="container-fluid">
	<?php
		if (count($entities)==0) {
			echo '<h1 class="bd-title">Aucun résultat</h1>';
			echo '<p>Introduire une <a href="society_edit.php?society_name='.urlencode(ucfirst($_REQUEST['name_substring'])).'">nouvelle société</a> ou un <a href="individual_edit.php?individual_lastname='.urlencode(ucfirst($_REQUEST['name_substring'])).'">nouvel individu</a>.</p>';
		} else {
			echo '<h1 class="bd-title">'.ToolBox::toHtml($doc_title).' <small>'.$count_max.' max</small></h1>';
			echo '<ul class="list-group">';
			foreach ($entities as $e) {
				echo '<li class="list-group-item">';
				
				$name = empty($_REQUEST['name_substring']) ? ToolBox::toHtml($e['name']) : str_ireplace(ToolBox::toHtml($_REQUEST['name_substring']), '<small>'.ToolBox::toHtml($_REQUEST['name_substring']).'</small>', ToolBox::toHtml($e['name']));
				
				switch($e['type']) {
					case 'individual':
						echo '<i class="fas fa-user-circle colored"></i> <a href="individual.php?individual_id='.$e['id'].'">'.$name.'</a>';
						break;
					case 'society':
						echo '<i class="fas fa-users colored"></i> <a href="society.php?society_id='.$e['id'].'">'.$name.'</a>';
						break;					
				}
				echo '</li>';
			}
			echo '</ul>';			
		}
	?>
</div>
</body>
</html>