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

// messages à délivrer
$messages = array ();

// Formatage des données saisies par l'utilisateur
if (isset ( $_POST )) {
	ToolBox::formatUserPost ( $_POST );
}

if (isset ( $_REQUEST ['society_id'] ) && is_numeric ( $_REQUEST ['society_id'] )) {
	$society = new Society ( $_REQUEST ['society_id'] );
}

if (isset ( $_POST ['cmd'] )) {
	switch ($_POST ['cmd']) {
		case 'transfer' :
			//print_r ( $_POST );
			if (! empty ( $_POST ['targetSociety_id'] )) {
				$targetSociety = new Society ( $_POST ['targetSociety_id'] );
				foreach ( $_POST ['individualToTransfer'] as $id ) {
					$individualToTransfer = new Individual ( $id );
					$newMembership = new Membership ();
					$newMembership->setIndividual ( $individualToTransfer );
					$newMembership->setSociety ( $targetSociety );
					$newMembership->setInitYear ( $_POST ['transfer_year'] );
					$newMembership->setTitle ( $_POST ['targetTitle'] );
					$newMembership->toDB();
					
					if (isset($_POST['endSocietyMemberships']) && $_POST['endSocietyMemberships']==true) {
						$individualToTransfer->endSocietyMemberships($society, $_POST['transfer_year']);
					}
				}
			}
			header('Location:'.$targetSociety->getDisplayUrl());
			exit;
	}
}
$h1_content = 'Transférer les gens de ' . $society->getHtmlLinkToSociety ();
?>
<!doctype html>
<html lang="fr">
<head>
	<title><?php strip_tags($h1_content) ?></title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
	<link type="text/css" rel="stylesheet" href="<?php echo FONTAWESOME_CSS_URI ?>" integrity="<?php echo FONTAWESOME_CSS_URI_INTEGRITY ?>" crossorigin="anonymous" />
	<link type="text/css" rel="stylesheet" href="<?php echo JQUERY_UI_CSS_THEME_URI ?>"></link>
	<link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>theme.css"></link>
	<?php echo $system->writeHtmlHeadTagsForFavicon(); ?>    
	<script src="js/controls.js"></script>
	<script src="<?php echo JQUERY_URI; ?>"></script>
	<script src="<?php echo JQUERY_UI_URI; ?>"></script>
	<script src="js/masonry.pkgd.min.js"></script>
	<script src="js/imagesloaded.pkgd.min.js"></script>
	<script src="<?php echo BOOTSTRAP_JS_URI ?>"	integrity="<?php echo BOOTSTRAP_JS_URI_INTEGRITY ?>" crossorigin="anonymous"></script>
</head>
<body>
<?php include 'navbar.inc.php'; ?>
<div class="container-fluid">
		<h1 class="bd-title"><?php echo $h1_content ?></h1>

		<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
    	<?php
		if (isset ( $_REQUEST ['society_id'] )) {
			echo '<input name="society_id" type="hidden" value="' . $society->getId () . '" />';
		}
		?>
    	<div class="row">
				<div class="col-md-3">
					<h2>Vers ?</h2>
					<?php
					$societies = $society->getRelatedSocieties ();
					foreach ( $societies as $item ) {
						// $item[0] : Société
						// $item[1] : Identifiant de la relation;
						// $item[2] : Rôle
						// $item[3] : Description
						$radio_count = 0;
						echo '<div class="form-check">';
						echo '<input class="form-check-input" type="radio" name="targetSociety_id" id="radio' . $radio_count . '" value="' . $item [0]->getId () . '">';
						echo '<label class="form-check-label" for="radio' . $radio_count . '">' . $item [0]->getHtmlLinkToSociety () . '</label>';
						echo '<div>' . ToolBox::toHtml ( $item [2] ) . '</div>';
						echo '</div>';
						$radio_count ++;
					}
					?>
				</div>

				<div class="col-md-6">
					<h2>Qui ?</h2>
					<?php
					$memberships = $society->getMemberships ();
					if ($memberships) {
						// regroupement des participations par individu
						$members = array ();
						foreach ( $memberships as $ms ) {
							$i = $ms->getIndividual ();
							$key = $i->getId ();
							if (isset ( $members [$key] )) {
								array_push ( $members [$key], $ms );
							} else {
								$members [$key] = array (
										$ms
								);
							}
						}
						unset ( $memberships );

						$actualMembers = array ();
						$formerMembers = array ();

						foreach ( $members as $id => $memberships ) {
							$pastMembershipCount = 0;
							foreach ( $memberships as $ms ) {
								if ($ms->hasEndYear ()) {
									$pastMembershipCount ++;
								}
							}
							count ( $memberships ) == $pastMembershipCount ? $formerMembers [$id] = $memberships : $actualMembers [$id] = $memberships;
						}

						if (count ( $actualMembers ) > 0) {
							echo '<div class="il">';
							echo '<div class="masonryGutterSizer"></div>';
							foreach ( $actualMembers as $id => $memberships ) {
								$i = new Individual ( $id );
								$i->feed ();
								echo '<div class="card">';
								if ($i->getPhotoUrl ()) {
									echo '<a href="individual.php?individual_id=' . $i->getId () . '">';
									echo '<img src="' . $i->getPhotoUrl () . '"  class="card-img-top" />';
									echo '</a>';
								}

								$card_title_tag = '<h3 class="card-title"><a href="individual.php?individual_id=' . $i->getId () . '">' . ToolBox::toHtml ( $i->getWholeName () ) . '</a></h3>';

								if (count ( $memberships ) > 1) {
									echo '<div class="card-body">' . $card_title_tag . '</div>';
									echo '<ul class="list-group list-group-flush">';
									foreach ( $memberships as $ms ) {
										echo '<li class="list-group-item">';
										echo '<a href="membership_edit.php?membership_id=' . $ms->getId () . '" class="implicit">' . ToolBox::toHtml ( $ms->getTitle () ) . '</a>';
										if ($ms->getPeriod ())
											echo ' <small>(' . $ms->getPeriod () . ')</small>';
										if ($ms->hasDescription ())
											echo '<p><small>' . ToolBox::toHtml ( $ms->getDescription () ) . '</small></p>';
										// if ($ms->getDepartment()) echo '<p><small>'.ToolBox::toHtml($ms->getDepartment()).'</small></p>';
										echo '</li>';
									}
									echo '</ul>';
								} else {
									echo '<div class="card-body">';
									echo $card_title_tag;
									echo '<p>';
									echo '<a href="membership_edit.php?membership_id=' . current ( $memberships )->getId () . '" class="implicit">' . ToolBox::toHtml ( current ( $memberships )->getTitle () ) . '</a>';
									if (current ( $memberships )->getPeriod ())
										echo ' <small>(' . current ( $memberships )->getPeriod () . ')</small>';
									echo '</p>';
									if (current ( $memberships )->hasDescription ())
										echo '<p><small>' . ToolBox::toHtml ( current ( $memberships )->getDescription () ) . '</small></p>';
									// if (current($memberships)->getDepartment()) echo '<p><small>'.ToolBox::toHtml(current($memberships)->getDepartment()).'</small></p>';

									echo '<div><a href="membership_edit.php?membership_id=' . current ( $memberships )->getId () . '" class="btn btn-sm btn-outline-secondary">édition</a></div>';
									echo '</div>';
								}
								echo '<div class="card-footer"><div class="form-check form-check-inline"><input name="individualToTransfer[]" class="form-check-input" type="checkbox" id="" value="' . $i->getId () . '">
  <label class="form-check-label" for="">A transférer</label></div></div>';
								echo '</div>';
							}
							echo '</div>';
						}
					}
					?>
				</div>
				<div class="col-md-3">
					<h2>Comment ?</h2>
					<div class="form-group">
						<div class="form-check">
							<input class="form-check-input" type="checkbox"
								name="endSocietyMemberships" id="endSocietyMemberships_i"
								value="true"> <label class="form-check-label"
								for="endSocietyMemberships_i">Ce transfert marque la fin de la participation de chacun à <?php echo $society->getNameForHtmlDisplay() ?></label>
						</div>
					</div>
					<div class="form-group">
						<label for="transfer_year_i">Année de transfert</label> <input
							name="transfer_year" type="text" class="form-control"
							id="transfer_year_i" value="<?php echo date("Y") ?>">
					</div>
					<div class="form-group">
						<label for="title_i">Rôle de chacun dans la nouvelle société</label>
						<input name="targetTitle" type="text" class="form-control"
							id="title_i" value="membre">
					</div>
					<button name="cmd" type="submit" value="transfer" class="btn btn-primary">lancer le transfert</button>
				</div>
			</div>
		</form>
	</div>
	<script>
	$(document).ready(function() {
		$('.il').masonry({
	      itemSelector: '.card',
	      columnWidth: '.card',
	      gutter: '.masonryGutterSizer'
	    }).imagesLoaded().progress(function() {
				$('.il').masonry('layout');
			}
		);

	});
</script>
</body>
</html>