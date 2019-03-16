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

session_start();

if (empty ($_SESSION['user_id'])) {
	header('Location:login.php');
	exit;
} else {
	$user = new User($_SESSION['user_id']);
	$user->feed();
}


$individual = new Individual($_REQUEST['individual_id']);
$individual->feed();

// participations
$memberships = $individual->getMemberships();

// individus liés
$relatedIndividuals = $individual->getRelatedIndividuals();

$doc_title = $individual->getWholeName();

//var_dump($_SESSION);

if (isset($_SESSION['preferences']['individual']['focus']) && strcmp($_SESSION['preferences']['individual']['focus'], 'onTweets')==0 && !$individual->hasTwitterId()) {
	unset($_SESSION['preferences']['individual']['focus']);	
}

if (!empty($_SESSION['preferences']['individual']['focus'])) {
	$focus = $_SESSION['preferences']['individual']['focus'];
} else {
	$focus = 'onMemberships';
}
?>
<!doctype html>
<html lang="fr">
<head>
	<title><?php echo $doc_title ?></title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<link type="text/css" rel="stylesheet" href="<?php echo FONTAWESOME_CSS_URI ?>" integrity="<?php echo FONTAWESOME_CSS_URI_INTEGRITY ?>" crossorigin="anonymous" />	
	<link type="text/css" rel="stylesheet" href="<?php echo JQUERY_UI_CSS_THEME_URI ?>"></link>
	<link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>main.css"></link>
	<?php echo $system->writeHtmlHeadTagsForFavicon(); ?>
	<script type="text/javascript" src="<?php echo JQUERY_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo JQUERY_UI_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo BOOTSTRAP_JS_URI ?>" integrity="<?php echo BOOTSTRAP_JS_URI_INTEGRITY ?>" crossorigin="anonymous"></script>
</head>
<body id="individualDoc">
<?php include 'navbar.inc.php'; ?>
<div class="container-fluid">
	<h1 class="bd-title"><?php echo ToolBox::toHtml($doc_title); ?> <small><a href="individual_edit.php?individual_id=<?php echo $individual->getId() ?>"><i class="fas fa-edit"></i></a> <a href="index.php?individual_task_id=pin&individual_id=<?php echo $individual->getId() ?>&memberships_focus=onLastPinned"><i class="fas fa-thumbtack"></i></a> <a href="<?php echo ToolBox::getGoogleQueryUrl($individual->getWholeName()) ?>" target="_blank"><i class="fab fa-google"></i></a></small></h1>
  	<div class="row">
    	<div class="col-lg-3">
    		<div class="card" style="width:100%">
    		<?php
    			// photo
				if ($individual->getPhotoUrl()) {
					echo '<img src="' . $individual->getPhotoUrl () . '"  class="card-img-top" />';
				} else {
					echo '<a href="individual_edit.php?individual_id='.$individual->getId().'" class="implicit">';
					echo '<img src="'.$system->getSkinUrl().'/images/missingThumbnail.svg" class="card-img-top missing-thumbnail" />';
					echo '</a>';
				}
				
				if ($individual->hasDescription()) {
					echo '<div class="card-body">';
					echo Toolbox::toHtml($individual->getDescription());
					echo '</div>';
				}
				if ($individual->getBirthDate()) {
					echo '<div class="card-body">';
					echo '<p><small>naissance : </small>'.$individual->getBirthDateFr().'</p>';
					echo '</div>';
				}
				
				// liens vers comptes des réseaux sociaux
				$links = array();
				if ($individual->hasTwitterId()) {
					$links[] = $individual->getHtmlLinkToTwitter();
				}
				if ($individual->hasLinkedinId()) {
					$links[] = $individual->getHtmlLinkToLinkedin();
				}
				if ($individual->getWeb()) {
					$links[] = $individual->getHtmlLinkToWeb();
				}
				if ($individual->getCvUrl()) {
					$links[] = '<a href="'.$individual->getCvUrl().'">cv</a>';
				}
				if (count($links) > 0) {
					echo '<ul class="list-group list-group-flush">';
					foreach ($links as $l) {
						echo '<li class="list-group-item"><small>'.$l.'</small></li>';
					}
					echo '</ul>';
				}
			?>			
		</div>
    	</div>
	    <div class="col-lg">
	    	<div>
				<!-- Nav tabs -->
				<ul class="nav nav-tabs">
				    <li class="nav-item">
				    	<a class="nav-link <?php if (strcmp($focus,'onMemberships')==0) echo ' active' ?>" id="membershipsTabSelector" href="#memberships-tab" data-toggle="tab">Participations <span class="badge badge-secondary"><?php echo count($memberships) ?></span></a>
			    	</li>
				    <li class="nav-item">
				    	<a class="nav-link <?php if (strcmp($focus,'onRelatedIndividuals')==0) echo ' active' ?>" id="relationsTabSelector" href="#relations-tab" aria-controls="individuals-tab" role="tab" data-toggle="tab">Relations <span class="badge badge-secondary"><?php echo count($relatedIndividuals) ?></span></a>
			    	</li>
			    	<?php
				    	if ($individual->hasTwitterId()) {
				    		echo '<li class="nav-item"><a class="nav-link';
				    		if (strcmp($focus,'onTweets')==0) echo ' active';
				    		echo '" id="tweetsTabSelector" href="#tweets-tab" data-toggle="tab">Tweets</a></li>';
				    	}
			    	?>
				  </ul>
		  		
		  		<!-- Tab panes -->
				<div class="tab-content">
				    <div role="tabpanel" class="tab-pane <?php if (strcmp($focus,'onMemberships')==0) echo 'active' ?>" id="memberships-tab">
						<!-- <h2>Participations <small><a href="membership_edit.php?individual_id=<?php echo $individual->getId() ?>."><i class="fas fa-plus"></i></a></small></h2>-->
						<?php
						if (isset($memberships)){
							echo '<ul class="list-group list-group-flush">';
							foreach ($memberships as $ms) {
								$s = $ms->getSociety();
								echo '<li class="list-group-item">';
								echo '<h2>';
								echo $s->getHtmlLinkToSociety();
								echo ' <small>';
								echo '<a href="membership_edit.php?membership_id='.$ms->getId().'"><i class="fas fa-edit"></i></a>';
								echo ' <a href="'.ToolBox::getGoogleQueryUrl($s->getName().' "'.$individual->getWholeName().'"').'" target="_blank"><i class="fab fa-google"></i></a>';
								echo '</small>';
								echo '</h2>';
								
								$more = array();
								if ($ms->getDepartment()) {
								    $more[] = $ms->getDepartment();
								}
								if ($ms->getTitle()) {
								    $more[] = '<a href="title.php?title='.urlencode($ms->getTitle()).'" class="implicit">'.ToolBox::toHtml(ucfirst($ms->getTitle())).'</a>';
								}
								if ($ms->getPeriod()) {
									$more[] = $ms->getPeriod();
															
								}
								if (count($more)>0) {
									echo '<div><small>'.implode(' - ', $more).'</small></div>';
								}					
								
								if ($ms->getDescription()) {
									echo '<p>'.ToolBox::toHtml($ms->getDescription()).'</p>';
								}
								
								if ($ms->getUrl()) {
									echo '<p>'.$ms->getHtmlLinkToWeb().'</p>';
								}
								$data = array();
								if ($ms->getPhone()) $data[] = $ms->getPhone();
								if ($ms->getEmail()) {
									$data[] = '<a href="mailto:'.ToolBox::toHtml($individual->getFirstName()).'%20'.ToolBox::toHtml($individual->getLastName()).'%20<'.$ms->getEmail().'>">'.$ms->getEmail().'</a>';
								}
								if (count($data)>0) {
									echo '<p>'.implode('<span> | </span>', $data).'</p>';
								}
								echo '</li>';
							}
							echo '<li class="list-group-item"><a href="membership_edit.php?individual_id='.$individual->getId().'" class="btn btn-sm btn-secondary"><i class="fas fa-plus"></i></a></li>';
							echo '</ul>';
						}
						?>
				    </div>
				    <div role="tabpanel" class="tab-pane <?php if (strcmp($focus,'onRelatedIndividuals')==0) echo 'active' ?>" id="relations-tab">
						<?php if (isset($relatedIndividuals)): ?>
						<ul class="list-group list-group-flush">
							<?php
							foreach ($relatedIndividuals as $item) {
								// $item[0] : Individu
								// $item[1] : Identifiant de la relation;
								// $item[2] : Rôle
								// $item[3] : Description
								// $item[4] : Period object
								echo '<li class="list-group-item">';
								echo '<h2>';
								echo '<a href="individual.php?individual_id='.$item[0]->getId().'">'.ToolBox::toHtml($item[0]->getWholeName()).'</a>';
								echo ' <small>';
								echo '<a href="individualToIndividualRelationship_edit.php?relationship_id='.$item[1].'"><i class="fas fa-edit"></i></a>';
								echo ' <a href="'.ToolBox::getGoogleQueryUrl('"'.$item[0]->getWholeName().'" "'.$individual->getWholeName().'"').'" target="_blank"><i class="fab fa-google"></i></a>';
								echo '</small>';
								echo '</h2>';

								$baseline = array();
								$baseline[] = empty($item[2]) ? '?' : ToolBox::toHtml(ucfirst($item[2]));
								if ($item[4]->isDefined()) {
									$baseline[] = $item[4]->toString();
									
								}
								echo '<div><small>'.implode(' - ',$baseline).'</small></div>';

								if (!empty($item[3])) {
									echo '<p>';
									echo ToolBox::toHtml($item[3]);
									echo '</p>';
								}
								echo '</li>';
							}
							echo '<li class="list-group-item"><a href="individualToIndividualRelationship_edit.php?item0_id='.$individual->getId().'" class="btn btn-sm btn-secondary"><i class="fas fa-plus"></i></a></li>';
							?>
						</ul>	    	
						<?php endif; ?>
				    </div>
				    <?php  if ($individual->hasTwitterId()) :?>
				    <div role="tabpanel" class="tab-pane <?php if (strcmp($focus,'onTweets')==0) echo 'active' ?>" id="tweets-tab">
				    	<?php echo $individual->embedTwitterTimeline(); ?>
				    </div>
				    <?php endif; ?>
		</div>
			</div>
	    </div>
	    <div class="col-lg-3">
	    	<div class="card" style="width:100%">
	    		<div class="card-header text-center"><i class="fab fa-google"></i></div>
	    		<ul class="list-group list-group-flush">
	    			<?php
	    				$links = array();
						$links[] = '<a href="'.$individual->getGoogleQueryUrl().'" target="_blank">'.Toolbox::toHtml($individual->hasFirstName() ? $individual->getFirstName() : $individual->getWholeName()).' chez Google</a>';
						$links[] = '<a href="'.$individual->getGoogleQueryUrl('vidéos').'" target="_blank">En vidéo</a>';
						$links[] = '<a href="'.$individual->getGoogleQueryUrl('actualités').'" target="_blank">Actualités</a>';
						foreach ($links as $l) {
							echo '<li class="list-group-item"><small>'.$l.'</small></li>';
						}
	    			?>
	    		</ul>
			</div>
	    	
    		<?php
				// coordonnées
				$contact_data = array();
				if ($individual->getAddress()) {
					$contact_data[] = $individual->getAddress();
				}
				if ($individual->getPhoneNumber()) {
					$contact_data[] = $individual->getHtmlLinkToPhoneCall();
				}
				if ($individual->getMobilePhoneNumber()) {
					$contact_data[] = $individual->getHtmlLinkToMobilePhoneCall();
				}
				if ($individual->getEmailAddress()) {
					$contact_data[] = $individual->getEmailHtml();
				}
				if (count($contact_data) >0) {
					echo '<div class="card"><div class="card-header">Coordonnées</div>';
					echo '<ul class="list-group list-group-flush">';
					foreach ($contact_data as $d) {
						echo '<li class="list-group-item">'.$d.'</li>';
					}
					echo '</ul></div>';
				}
			?>
			
	    </div>
  	</div>

	<script type="text/javascript">
		$(document).ready(function() {
		    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
				var focus;
				var scope = 'individual';
	
				switch(e.target.id) {
				  	case 'membershipsTabSelector':
				  		focus = 'onMemberships';
				  		break;
				  	case 'relationsTabSelector':
				  		focus = 'onRelatedIndividuals';
				  		break;
				  	case 'tweetsTabSelector':
				  		focus = 'onTweets';
				  		break;	
				}
	
				$.ajax({
					  url: 'api/session.ws.php?focus='+focus+'&scope='+scope,
					  beforeSend: function( xhr ) {
					    xhr.overrideMimeType( "text/plain; charset=x-user-defined" );
					  }
				});
			});
		});
	</script>
</body>
</html>