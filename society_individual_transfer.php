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
	<script src="<?php echo FONTAWESOME_KIT_URI ?>" crossorigin="anonymous"></script>
	<link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>theme.css"></link>
	<?php echo $system->writeHtmlHeadTagsForFavicon(); ?>    
	<script src="<?php echo JQUERY_URI; ?>"></script>
	<script src="js/masonry.pkgd.min.js"></script>
	<script src="js/imagesloaded.pkgd.min.js"></script>
	<script src="vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>
</head>
<body id="societyIndividualTransferDoc">
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
						echo '<div class="form-check">';
						echo '<input class="form-check-input" type="radio" name="targetSociety_id" id="rs-radio' . $item [0]->getId () . '" value="' . $item [0]->getId () . '">';
						echo '<label class="form-check-label" for="rs-radio' . $item [0]->getId () . '">' . $item [0]->getHtmlLinkToSociety () . '</label>';
						echo '<div>' . ToolBox::toHtml ( $item [2] ) . '</div>';
						echo '</div>';
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
									echo '<a href="individual.php?individual_id=' . $i->getId () . '" class="implicit card-img-top-wrapper">';
									echo '<img src="' . $i->getPhotoUrl () . '"  class="card-img-top" />';
									echo '</a>';
								}
				
								echo '<div class="card-body">';
								echo '<h3 class="card-title">' . ToolBox::toHtml ( $i->getWholeName () ) . '</h3>';
								echo '<p>' . ToolBox::toHtml ( current ( $memberships )->getTitle () ) . '</p>';
								echo '</div>';
								
								echo '<div class="card-footer">';
									echo '<div class="form-check form-check-inline">';
									echo '<input name="individualToTransfer[]" class="form-check-input" type="checkbox" id="i-box'.$i->getId ().'" value="' . $i->getId () . '">';
									echo '<label class="form-check-label" for="i-box'.$i->getId ().'">A transférer</label>';
									echo '</div>';
								echo '</div>';
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
					<button name="cmd" type="submit" value="transfer" class="btn btn-primary">Lancer le transfert</button>
				</div>
			</div>
		</form>
	</div>
	<script>
		document.addEventListener("DOMContentLoaded", function() {
			const ils = document.querySelectorAll('.il');
			imagesLoaded(ils, function(){
				for (let il of ils) {
					new Masonry( il, {
						itemSelector: '.card',
						columnWidth:  '.card',
						gutter: '.masonryGutterSizer'
					});
				}
			});
		});
	</script>
</body>
</html>