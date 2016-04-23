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

$relationship = new Relationship ();

// Formatage des données saisies par l'utilisateur
if (isset ( $_POST ))
	ToolBox::formatUserPost ( $_POST );

if (! empty ( $_REQUEST ['relationship_id'] )) {
	// la participation à traiter est identifiée
	$relationship->setId ( $_REQUEST ['relationship_id'] );
	$relationship->feed ();
	
	if (isset ( $_POST ['relationship_deletion'] )) {
		// demande de suppression de la participation
		$item0 = $relationship->getItem ( 0 );
		$relationship->delete ();
		header ( 'location:society.php?society_id=' . $item0->getId () );
	} else {
		// récupération des données en base
		$item0 = & $relationship->getItem ( 0 );
		if (is_object ( $item0 ))
			$item0->feed ();
		$item1 = & $relationship->getItem ( 1 );
		if (is_object ( $item1 ))
			$item1->feed ();
	}
} else {
	// la relation est nouvelle
	if (isset ( $_REQUEST ['item0_id'] )) {
		$relationship->setItem ( new Society ( $_REQUEST ['item0_id'] ), 0 );
		$item0 = & $relationship->getItem ( 0 );
		$item0->feed ();
	}
	if (isset ( $_REQUEST ['item1_id'] )) {
		$relationship->setItem ( new Society ( $_REQUEST ['item1_id'] ), 1 );
		$item1 = & $relationship->getItem ( 1 );
		$item1->feed ();
	}
}

if (isset ( $_POST ['relationship_submission'] )) {
	// enregistrement des données de la participation
	$relationship->feed ( $_POST );
	if (is_null ( $item0 ) && ! empty ( $_POST ['item0_name'] )) {
		$item0 = new Society ();
		$item0->setName ( $_POST ['item0_name'] );
		if (! $item0->identifyFromName ())
			$item0->toDB ();
		$relationship->setItem ( $item0, 0 );
		// print_r($society);
	}
	if (is_null ( $item1 ) && ! empty ( $_POST ['item1_name'] )) {
		$item1 = new Society ();
		$item1->setName ( $_POST ['item1_name'] );
		if (! $item1->identifyFromName ())
			$item1->toDB ();
		$relationship->setItem ( $item1, 1 );
	}
	if ($relationship->toDB ()) {
		header ( 'location:society.php?society_id=' . $item0->getId () );
		exit ();
	}
}
if (isset ( $item0 ) && isset ( $item1 )) {
	$doc_title = 'Une relation entre ' . $item0->getName () . ' et ' . $item1->getName ();
} else {
	$doc_title = isset ( $item0 ) && $item0->getId () ? 'Une relation de ' . $item0->getName () : 'Une relation';
}
?>
<!doctype html>
<html lang="fr">
<head>
    <title><?php echo $doc_title ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_URI ?>" type="text/css" />
    <link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_THEME_URI ?>" type="text/css" />
    <script type="text/javascript" src="<?php echo YUI_SEEDFILE_URI ?>"></script>
    <script type="text/javascript" src="js/controls.js"></script>
    <link rel="stylesheet" href="<?php echo SKIN_URL ?>main.css" type="text/css">
<script type="text/javascript" src="<?php echo JQUERY_URI; ?>"></script><script type="text/javascript" src="<?php echo BOOTSTRAP_JS_URI; ?>"></script></head>
<body>
<?php include 'navbar.inc.php'; ?>
<div class="container-fluid">
	<h1><?php echo ToolBox::toHtml($doc_title); ?></h1>
	
	<section>
		<form id="relationship_form" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
			<input name="item0_class" type="hidden" value="Society" />
			<input name="item1_class" type="hidden" value="Society" />
			<?php
			if ($relationship->getId ())
				echo '<input name="relationship_id" type="hidden" value="' . $relationship->getId () . '" />';
			?>

			<?php
			if (! isset ( $item0 ) || ! $item0->getId ()) {
				echo '<div class="form-group">';
			    echo '<label for="s1_name_i">Nom de la première société</label>';
				echo '<input id="s1_name_i" name="item0_name" type="text" maxlength="255" class="form-control" />';
				echo '</div>';
			} else {
				echo '<small>Première Société : </small>';
				echo '<a href="society.php?society_id=' . $item0->getId () . '">' . $item0->getName () . '</a>';
				echo '<input name="item0_id" type="hidden" value="' . $item0->getId () . '"/>';
			}
			if (! isset ( $item1 )) {
				$item1 = new Society ();
				echo '<div class="form-group">';
				echo '<label for="s2_name_i">Nom de la deuxième société</label>';
				echo '<input id="s2_name_i" name="item1_name" type="text" maxlength="255" class="form-control" />';
				echo '</div>';
			} else {
				echo '<small>Deuxième Société : </small>';
				echo '<a href="society.php?society_id=' . $item1->getId () . '">' . $item1->getName () . '</a>';
				echo '<input name="item1_id" type="hidden" value="' . $item1->getId () . '"/>';
			}
			?>

			
			<div class="form-group">
    			<label>Rôle première société</label>
    			<input name="item0_role" type="text" value="<?php echo $relationship->getItemRole(0); ?>" size="20" class="form-control" />
			</div>
			
			<div class="form-group">
    			<label>Rôle deuxième société</label>
    			<input name="item1_role" type="text" value="<?php echo $relationship->getItemRole(1); ?>" size="20" class="form-control" />
			</div>
			
			<div class="form-group">
    			<label>Début</label>
    			<input name="init_date" type="date" value="<?php echo $relationship->getAttribute('init_date'); ?>" size="20" class="form-control" />
			</div>
			
			<div class="form-group">
    			<label>Fin</label>
    			<input name="end_date" type="date" value="<?php echo $relationship->getAttribute('end_date'); ?>" size="20" class="form-control" />
			</div>
			
			<div class="form-group">
    			<label>Commentaire</label>
    			<textarea name="description" cols="51" rows="5" class="form-control">
    				<?php echo $relationship->getAttribute('description'); ?>
    			</textarea>
			</div>
			
			<div class="form-group">
    			<label for="relationship_url_input">url</label>
    			<input id="relationship_url_input" name="url" type="url" size="35" class="form-control" onchange="javascript:checkUrlInput('relationship_url_input', 'relationship_web_link');" value="<?php echo $relationship->getUrl(); ?>" />
    			<a id="relationship_web_link" href="#" style="display: none">[voir]</a>
			</div>
			
			<button name="relationship_submission" type="submit" value="1" class="btn btn-primary">Enregistrer</button>
			<?php if ($relationship->getId()) : ?>
				<button name="relationship_deletion" type="submit" value="1" class="btn btn-default">Supprimer</button>
			<?php endif; ?>
		</form>
	</section>
</div>
<script type="text/javascript">
	YUI().use("autocomplete", "autocomplete-highlighters", function (Y) {
		Y.on('domready', function () {
			Y.one('body').addClass('yui3-skin-sam');

			if(Y.one('#s1_name_i')!==null) {
				Y.one('#s1_name_i').plug(Y.Plugin.AutoComplete, {
			 	resultHighlighter: 'phraseMatch',
			 	resultListLocator: 'names',
			 	minQueryLength:3,
			 		source: '<?php echo APPLI_URL ?>society_names.json.php?query={query}'
			 	});
			}
		 	
			Y.one('#s2_name_i').plug(Y.Plugin.AutoComplete, {
		 	resultHighlighter: 'phraseMatch',
		 	resultListLocator: 'names',
		 	minQueryLength:3,
		 		source: '<?php echo APPLI_URL ?>society_names.json.php?query={query}'
		 	}); 	
		});
	});
</script>
</body>
</html>