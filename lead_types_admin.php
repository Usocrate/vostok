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
	$user->feed ();
}

$doc_title = 'Type de piste : Fusionner 2 catégories';
$messages = array ();

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
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
    <link type="text/css" rel="stylesheet" href="<?php echo FONTAWESOME_CSS_URI ?>" integrity="<?php echo FONTAWESOME_CSS_URI_INTEGRITY ?>" crossorigin="anonymous" />    
    <link type="text/css" rel="stylesheet" href="<?php echo JQUERY_UI_CSS_THEME_URI ?>"></link>
    <link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>theme.css"></link>
    <?php echo $system->writeHtmlHeadTagsForFavicon(); ?>
    <script src="<?php echo JQUERY_URI; ?>"></script>
    <script src="<?php echo JQUERY_UI_URI; ?>"></script>
    <script src="<?php echo BOOTSTRAP_JS_URI ?>" integrity="<?php echo BOOTSTRAP_JS_URI_INTEGRITY ?>" crossorigin="anonymous"></script>
</head>
<body>
<?php include 'navbar.inc.php'; ?>
<div class="container-fluid">

	<h1 class="bd-title"><?php echo ToolBox::toHtml($doc_title); ?></h1>
	
	<?php
	if (count ( $messages ) > 0) {
		echo '<section>';
		foreach ( $messages as $m ) {
			echo '<p>' . $m . '</p>';
		}
		echo '</section>';
	}
	?>
	<section>
    	<form action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
    		<div class="form-group">
        		<label for="lead_type_todrop">La catégorie obsolète</label>
        		<select name="lead_type_todrop" class="form-control">
        			<?php echo Lead::getKnownTypesAsOptionsTags()?>
        		</select>
    		</div>
    		<small> à remplacer par </small> 
    		<div class="form-group">
        		<label>La catégorie référence</label>
        		<select name="lead_type_ref" class="form-control">
        			<?php echo Lead::getKnownTypesAsOptionsTags()?>
        		</select>
    		</div>
    		<input name="task" type="hidden" value="lead_types_merge" />
    		<button type="submit" class="btn btn-primary">Ok</button>
    	</form>
	</section>
</div>	
</body>
</html>
