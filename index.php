<?php
require_once 'config/boot.php';
require_once 'classes/System.php';
$system = new System( './config/host.json' );

session_start ();

if (empty ( $_SESSION ['user_id'] )) {
	header ( 'Location:login.php' );
	exit ();
} else {

	$user = new User ( $_SESSION ['user_id'] );
	$user->feed ();

	if (isset($_REQUEST['individual_task_id'])) {
	    // une tâche à effectuer
	    switch ($_REQUEST['individual_task_id']) {
	        case 'pin':
	            if (empty($_REQUEST['individual_id'])) {
	            	break;
	            }
	            $i = new Individual($_REQUEST['individual_id']);
	            $result = $i->Pin();
	    }
	}
	
	if ( isset($_REQUEST['people_focus']) ) {
	    if (!isset($_SESSION['preferences'])) {
	        $_SESSION['preferences'] = array();
	    }
        if ( ! isset($_SESSION['preferences']['people']) ) {
            $_SESSION['preferences']['people'] = array();
        }
        $_SESSION['preferences']['people']['focus'] = $_REQUEST['people_focus'];
	    //var_dump($_SESSION);
	}
	
	if (!empty($_SESSION['preferences']['people']['focus'])) {
		$people_focus = $_SESSION['preferences']['people']['focus'];
	} else {
		$people_focus = 'onLastPinned';
	}

	switch($people_focus) {
		case 'onLastPinned' : 
			$individuals = $system->getLastPinnedIndividuals(30);
			$individuals->setIndividualMemberships();
			break;
		case 'onLastUpdated' : 
			$memberships = $system->getMemberships(array('active'=>true), 'Last updated first', 0, 12);
			break;			
	}
}

$doc_title = 'Accueil';
?>
<!doctype html>
<html lang="fr">
<head>
	<title><?php echo ToolBox::toHtml($system->getAppliName()).' : '.ToolBox::toHtml($doc_title) ?></title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
	<script src="<?php echo FONTAWESOME_KIT_URI ?>" crossorigin="anonymous"></script>
	<link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>theme.css"></link>
	<?php echo $system->writeHtmlHeadTagsForFavicon(); ?>
	<script src="<?php echo JQUERY_URI; ?>"></script>
	<script src="vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>
	<script src="js/masonry.pkgd.min.js"></script>
	<script src="js/imagesloaded.pkgd.min.js"></script>
	<script src="js/individual-photo.js"></script>	
</head>
<body id="indexDoc">
    <?php include 'navbar.inc.php'; ?>
    <div class="container">
		<h1 class="sr-only"><?php echo ToolBox::toHtml($doc_title); ?></h1>
		<?php
			switch($people_focus) {
				case 'onLastPinned' : 
			  		echo '<div class="il">';
			  		echo '<div class="masonryGutterSizer"></div>';
			  		foreach ($individuals as $i) {
						echo '<div class="card">';
						if ($i->hasPhoto()) {
							echo '<a href="individual.php?individual_id='.$i->getId().'" class="implicit card-img-top-wrapper">';
							echo '<img is="individual-photo" data-individual-id="'.$i->getId().'" src="' . $i->getReworkedPhotoUrl () . '" class="card-img-top"></img>';
							echo '</a>';
						}
						echo '<div class="card-header">';
							echo '<a href="individual.php?individual_id='.$i->getId().'" class="implicit">'.ToolBox::toHtml($i->getWholeName()).'</a>';
						echo '</div>';
						//*
						echo '<ul class="list-group list-group-flush">';
						foreach ($i->getMemberships() as $ms) {
							$s = $ms->getSociety();
							echo '<li class="list-group-item">';
								//echo '<div class="card-text">';
								echo '<div>'.$s->getHtmlLinkToSociety('onIndividuals').'</div>';
								$href = 'membership_edit.php?membership_id='.$ms->getId();
								if ($ms->getTitle()) {
									echo '<a href="'.$href.'" class="implicit">';
									echo ToolBox::toHtml($ms->getTitle());
									echo '</a>';
									if ( $ms->getPeriod() ) {
										echo ' <small style="white-space: nowrap">('.$ms->getPeriod().')</small>';
									}
								} else {
									echo '<a href="'.$href.'" class="implicit">';
									echo '<i class="fas fa-edit"></i> éditer';
									echo '</a>';
								}
								//echo '</div>';
							echo '</li>';
						}
						echo '</ul>';
						//*/
						echo '</div>';
			  		}
					echo '</div>';
					echo '<div><a href="index.php?people_focus=onLastUpdated">Voir les dernières participations mise à jour</a></div>';
					break;
				case 'onLastUpdated' : 
			  		echo '<div class="il">';
			  		echo '<div class="masonryGutterSizer"></div>';
			  		foreach ($memberships as $ms) {
						$i = $ms->getIndividual();
						$s = $ms->getSociety();
						echo '<div class="card">';
						if ($i->hasPhoto()) {
							echo '<a href="individual.php?individual_id='.$i->getId().'" class="implicit card-img-top-wrapper">';
							echo '<img is="individual-photo" data-individual-id="'.$i->getId().'" src="' . $i->getReworkedPhotoUrl () . '" class="card-img-top"></img>';
							echo '</a>';
						}
						echo '<div class="card-body">';
							echo '<h3 class="card-title">';
							echo '<a href="individual.php?individual_id='.$i->getId().'">'.ToolBox::toHtml($i->getWholeName()).'</a>';
							echo '<br><small>'.$s->getHtmlLinkToSociety().'</small>';
							echo '</h3>';
							echo '<div class="card-text">';
								$position_elt = array();
								if ($ms->getDepartment()) {
									$position_elt[] = ToolBox::toHtml($ms->getDepartment());
								}
								if ($ms->getTitle()) {
									$position_elt[] = ToolBox::toHtml($ms->getTitle());
								};
								if (count($position_elt)>0) {
									echo '<p>'.implode(' / ', $position_elt).'</p>';
								}

								if ( $ms->getPeriod() ) $smallTag_elt[] = '<p><small>'.$ms->getPeriod().'</small></p>';
							echo '</div>';
							echo '<div><a href="membership_edit.php?membership_id='.$ms->getId().'" class="btn btn-sm btn-outline-secondary">édition</a></div>';
						echo '</div>';
						echo '</div>';
				  	}
					echo '</div>';
					echo '<div><a href="index.php?people_focus=onLastPinned">Voir les derniers gens épinglés</a></div>';
					break;			
			}
		?>
	</div>
	<script>
		const trombiUrl = '<?php echo $system->getTrombiUrl() ?>';
		const trombiReworkUrl = '<?php echo $system->getTrombiReworkUrl() ?>';
		const imageFileExtensions = JSON.parse('<?php echo json_encode($system->getImageFileExtensions()) ?>');
		
	
		document.addEventListener("DOMContentLoaded", function() {
			customElements.define("individual-photo", IndividualPhoto, { extends: "img" });
	
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

			document.getElementById('entity_search_i').focus();
		});
	</script>
</body>
</html>
