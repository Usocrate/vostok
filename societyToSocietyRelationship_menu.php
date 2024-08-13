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

$relationship = new Relationship($_REQUEST ['relationship_id'] );
$relationship->feed ();

//print_r($relationship);

// récupération des données en base
$item0 = $relationship->getItem ( 0 );
if (is_object ( $item0 )) {
	$item0->feed ();
	$relationship->setItem($item0,0);
}

$item1 = $relationship->getItem ( 1 );
if (is_object ( $item1 )) {
	$item1->feed ();
	$relationship->setItem($item1,1);
}

$itemToFocus = strcmp($_REQUEST ['societyToFocus_id'], $item0->getId())==0 ? $item0 : $item1;
$refItem = strcmp($_REQUEST ['societyToFocus_id'], $item0->getId())==0 ? $item1 : $item0;
?>
<!doctype html>
<html lang="fr">
<head>
    <title><?php echo strip_tags($h1_content) ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
    <link type="text/css" rel="stylesheet" href="<?php echo FONTAWESOME_CSS_URI ?>" integrity="<?php echo FONTAWESOME_CSS_URI_INTEGRITY ?>" crossorigin="anonymous" />    
    <link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>theme.css"></link>
    <?php echo $system->writeHtmlHeadTagsForFavicon(); ?>
	<script src="<?php echo JQUERY_URI; ?>"></script>
	<script src="vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>
</head>
<body class="menu">
<div class="container-fluid">
	<h1 class="bd-title"><?php echo $itemToFocus->getHtmlLinkToSociety() ?></h1>
	<p class="text-muted"><?php echo '... et ' . $refItem->getHtmlLinkToSociety() ?></p>
	<section>
	<h2>Options</h2>
	<ul class="list-group">
		<li class="list-group-item">
		  	<h3><a href="societyToSocietyRelationship_edit.php?relationship_id=<?php echo $relationship->getId() ?>">Formulaire d&apos;édition générique</a></h3>
			<p class="mb-1">Pour modifier depuis un même écran toutes les caractéristiques de la relation</p>
		</li>
		<li class="list-group-item disabled">
			<h3>Déplacer <?php echo $itemToFocus->getName() ?></h3>
			<span class="badge badge-warning">A développer</span>
			<p class="mb-1">S&apos;il est plus juste de lier <?php echo $itemToFocus->getName() ?> à une autre société liée à <?php echo $refItem->getName() ?></p>
		</li>
		<li class="list-group-item disabled">
			<h3>Changer de rôle</h3>
			<span class="badge badge-warning">A développer</span>
			<p class="mb-1">Si un rôle est plus approprié pour <?php echo $itemToFocus->getName() ?> en lien avec <?php echo $refItem->getName() ?></p>
		</li>		
	</ul>
	</section>
	<div><?php echo '<a href="'.$refItem->getDisplayUrl().'" class="btn btn-link">Quitter</a>'; ?></div>
</div>
</body>
</html>