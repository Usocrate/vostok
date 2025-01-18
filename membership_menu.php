<?php
require_once 'config/boot.php';
require_once 'classes/System.php';
$system = new System ( 'config/host.json' );

session_start ();

if (empty ( $_SESSION ['user_id'] )) {
	header ( 'Location:login.php' );
	exit ();
} else {
	$user = new User ( $_SESSION ['user_id'] );
	$user->feed ();
}

$membership = new Membership ();

$fb = new UserFeedBack ();

if (! empty ( $_REQUEST ['membership_id'] )) {
	//
	// la participation à traiter est identifiée
	//
	$membership->setId ( $_REQUEST ['membership_id'] );
	if (! $membership->feed ()) {
		header ( 'location:index.php' );
		exit ();
	}		
} else {
	header ( 'location:index.php' );
	exit ();
}

// on détermine le titre de la page
if ($membership->isSocietyIdentified () && $membership->isIndividualIdentified ()) {
	$h1_content = $membership->getHtmlLinkToIndividual ('friendly') . ' <small>chez ' . $membership->getHtmlLinkToSociety () . '</small>';
} elseif ($membership->isIndividualIdentified ()) {
	$h1_content = 'Une participation de ' . $membership->getHtmlLinkToIndividual ();
} elseif ($membership->isSocietyIdentified ()) {
	$h1_content = 'Une participation à ' . $membership->getHtmlLinkToSociety ();
} else {
	$h1_content = 'Une participation';
}
?>
<!doctype html>
<html lang="fr">
<head>
	<title><?php echo strip_tags($h1_content); ?></title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
	<link type="text/css" rel="stylesheet" href="<?php echo FONTAWESOME_CSS_URI ?>"	integrity="<?php echo FONTAWESOME_CSS_URI_INTEGRITY ?>"	crossorigin="anonymous" />
	<link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>theme.css"></link>
	<?php echo $system->writeHtmlHeadTagsForFavicon(); ?>
	<script src="<?php echo JQUERY_URI; ?>"></script>
	<script src="vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>
</head>
<body>
<?php include 'navbar.inc.php'; ?>
<div class="container-fluid">
	<h1 class="bd-title"><?php echo $h1_content ?></h1>
	<?php echo $fb->toHtml() ?>
	<section>
		<h2>Options</h2>
		<ul class="list-group">
			<li class="list-group-item">
			  	<h3><a href="membership_edit.php?membership_id=<?php echo $membership->getId() ?>">Formulaire d&apos;édition standard</a></h3>
				<p class="mb-1">Pour modifier depuis un même écran toutes les caractéristiques de la participation</p>
			</li>
			<li class="list-group-item">
				<h3><a href="membership_transfer.php?membership_id=<?php echo $membership->getId() ?>">Transférer la participation à un homonyme</a></h3>
				<p class="mb-1">Si la participation concerne un homonyme</p>
			</li>
		</ul>
	</section>	
</div>
</body>
</html>