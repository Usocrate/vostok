<?php
require_once 'config/boot.php';
require_once 'classes/System.php';
$system = new System( 'config/host.json' );

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

	$membership->setId ( $_REQUEST ['membership_id'] );

	$heavierMembership = $system->getFirstHeavierMembershipInSociety ( $membership );
	
} else {
	header ( 'location:index.php' );
	exit ();
}

if (isset ( $_POST ['task'] )) {

	ToolBox::formatUserPost ( $_POST );

	switch ($_POST ['task']) {
		case 'membership_upgrade' :
			switch ($_POST ['location']) {
				case 'gohead' :
					$maxWeight = $system->getMembershipMaxWeightInSociety ( $membership->getSociety () );
					$membership->setWeight ( $maxWeight + 1 );
					if ($membership->toDB ()) {
						$fb->addSuccessMessage ( 'Le repositionnement est fait.' );
						$heavierMembership = $membership;
					}
					break;
				case 'join' :
					if (isset($heavierMembership)) {
						$targetWeight = $heavierMembership->getWeight();
						$membership->setWeight($targetWeight);
						if ($membership->toDB ()) {
							$fb->addSuccessMessage ( 'Le repositionnement est fait à la hauteur de '. $heavierMembership->getHtmlLinkToIndividual().'.');
						}
						$heavierMembership = $system->getFirstHeavierMembershipInSociety ( $membership );
					}
					break;
				case 'goBeyond' :
					$membership->setWeight ( $heavierMembership->getWeight () + 1 );
					if ($membership->toDB ()) {
						$fb->addSuccessMessage ( $membership->getHtmlLinkToIndividual().' est promu(e) devant '. $heavierMembership->getHtmlLinkToIndividual().'.');
					}
					$heavierMembership = $system->getFirstHeavierMembershipInSociety ( $membership );
					break;
			}
			break;
			
		case 'membership_downgrade' :
			switch ($_POST ['location']) {
				case 'downgrade' :
					if ($membership->getWeight () > 0) {
						$membership->setWeight ( $membership->getWeight () - 1 );
						if ($membership->toDB ()) {
							$fb->addSuccessMessage ( 'Le repositionnement est fait.' );
							$heavierMembership = $system->getFirstHeavierMembershipInSociety ( $membership );
						}
					}
					break;
				case 'reset' :
					$membership->setWeight ( 0 );
					if ($membership->toDB ()) {
						$fb->addSuccessMessage ( 'Le repositionnement est fait.' );
						$heavierMembership = $system->getFirstHeavierMembershipInSociety ( $membership );
					}
					break;
			}
			break;
	}
}

$h1_content = '<small>Quelle place pour </small>' . $membership->getHtmlLinkToIndividual () . ' <small>chez ' . $membership->getHtmlLinkToSociety () . ' ?</small>';

//print_r($membership);

?>
<!doctype html>
<html lang="fr">
<head>
	<title><?php echo strip_tags($h1_content); ?></title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
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
		<p>Promotion ?</p>
		<form id="membership_upgrade_form" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
	    	<?php echo '<input name="membership_id" type="hidden" value="' . $membership->getId () . '" />'; ?>
			<div class="form-group">
				<div class="form-check">
					<input id="head_i" name="location" type="radio"	class="form-check-input" value="gohead">
					<label for="head_i" class="form-check-label">En-tête de liste</label>
				</div>
				<?php if(isset($heavierMembership)): ?>
				<div class="form-check">
					<input id="join_i" name="location" type="radio"	class="form-check-input" value="join">
					<label for="join_i" class="form-check-label">Au même rang que <?php echo $heavierMembership->getHtmlLinkToIndividual() ?></label>
				</div>
				<div class="form-check">
					<input id="goBeyond_i" name="location" type="radio"	class="form-check-input" value="goBeyond">
					<label for="goBeyond_i" class="form-check-label">Devant <?php echo $heavierMembership->getHtmlLinkToIndividual() ?></label>
				</div>
				<?php endif; ?>
			</div>
			<div>
				<button name="task" type="submit" value="membership_upgrade" class="btn btn-primary">Enregistrer</button>
			</div>
		</form>
	</section>
	<section>
			<p>Rétrogradation ?</p>
			<form id="membership_downgrade_form" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
		    	<?php
					if ($membership->hasId ()) {
						echo '<input name="membership_id" type="hidden" value="' . $membership->getId () . '" />';
					}
				?>
				<div class="form-group">
					<div class="form-check">
						<input id="downgrade_i" name="location" type="radio" class="form-check-input" value="downgrade">
						<label for="downgrade_i" class="form-check-label">Rétrogradation</label>
					</div>
					<div class="form-check">
						<input id="reset_i" name="location" type="radio" class="form-check-input" value="reset">
						<label for="reset_i" class="form-check-label">Retour en bas de l&apos;échelle</label>
					</div>
				</div>
				<div><button name="task" type="submit" value="membership_downgrade" class="btn btn-primary">Enregistrer</button></div>
			</form>
	</section>
	</div>
</body>
</html>