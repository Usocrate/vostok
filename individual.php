<?php
require_once 'config/boot.php';
require_once 'classes/System.class.php';
$system = new System( 'config/host.json' );

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
	<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
	<link type="text/css" rel="stylesheet" href="<?php echo FONTAWESOME_CSS_URI ?>" integrity="<?php echo FONTAWESOME_CSS_URI_INTEGRITY ?>" crossorigin="anonymous" />	
	<link type="text/css" rel="stylesheet" href="<?php echo JQUERY_UI_CSS_THEME_URI ?>"></link>
	<link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>theme.css"></link>
	<?php echo $system->writeHtmlHeadTagsForFavicon(); ?>
	<script src="<?php echo JQUERY_URI; ?>"></script>
	<script src="<?php echo JQUERY_UI_URI; ?>"></script>
	<script src="vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>
	<script src="js/individual-photo.js"></script>
</head>
<body id="individualDoc">
<?php include 'navbar.inc.php'; ?>
<div class="container-fluid">
	<h1 class="bd-title"><?php echo ToolBox::toHtml($doc_title); ?> <small><a href="individual_edit.php?individual_id=<?php echo $individual->getId() ?>"><i class="fas fa-edit"></i></a> <a href="index.php?individual_task_id=pin&individual_id=<?php echo $individual->getId() ?>&memberships_focus=onLastPinned"><i class="fas fa-thumbtack"></i></a> <a href="<?php echo ToolBox::getGoogleQueryUrl($individual->getWholeName()) ?>" target="_blank"><i class="fab fa-google"></i></a></small></h1>
  	<div class="row">
    	<div class="col-lg-3">
    		<div class="card" style="width:100%">
    		<?php
    			if ($individual->hasPhoto()) {
    				echo '<a href="individual_edit.php?individual_id='.$individual->getId().'" class="implicit card-img-top-wrapper">';
    				echo '<img is="individual-photo" data-individual-id="'.$individual->getId().'" src="' . $individual->getReworkedPhotoUrl () . '" class="card-img-top"></img>';
	    			echo '</a>';
	    		} else {
					echo '<a href="individual_edit.php?individual_id='.$individual->getId().'" class="implicit card-img-top-wrapper">';
					echo '<img src="'.$system->getSkinUrl().'/images/missingThumbnail.svg" class="card-img-top missing-thumbnail" />';
					echo '</a>';
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
				if ($individual->getAddress()) {
					$links[] = $individual->getAddress();
				}
				if ($individual->getPhoneNumber()) {
					$links[] = $individual->getHtmlLinkToPhoneCall();
				}
				if ($individual->getMobilePhoneNumber()) {
					$links[] = $individual->getHtmlLinkToMobilePhoneCall();
				}
				if ($individual->getEmailAddress()) {
					$links[] = $individual->getEmailHtml();
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
						echo '<li class="list-group-item">'.$l.'</li>';
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
				    	<a class="nav-link <?php if (strcmp($focus,'onRelatedIndividuals')==0) echo ' active' ?>" id="relationsTabSelector" href="#relations-tab" data-toggle="tab">Relations <span class="badge badge-secondary"><?php echo count($relatedIndividuals) ?></span></a>
			    	</li>
				    <li class="nav-item">
				    	<a class="nav-link <?php if (strcmp($focus,'onDescription')==0) echo ' active' ?>" id="descriptionTabSelector" href="#description-tab" data-toggle="tab">Notes</span></a>
			    	</li>			    	
				  </ul>
		  		
		  		<!-- Tab panes -->
				<div class="tab-content">
				    <div role="tabpanel" class="tab-pane <?php if (strcmp($focus,'onMemberships')==0) echo 'active' ?>" id="memberships-tab">
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
																
								echo '<div class="membership-description-area" data-ms-id="'.$ms->getId().'">';
								echo $ms->hasDescription() ? $ms->getDescription() : 'Il était une fois...';
								echo '</div>';
																
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
								// $item[1] : Identifiant de la relation
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
								
								echo '<div class="relationship-description-area" data-rs-id="'.$item[1].'">';
								echo empty($item[3]) ? 'Il était une fois...' : $item[3];
								echo '</div>';
								
								echo '</li>';
							}
							echo '<li class="list-group-item"><a href="individualToIndividualRelationship_edit.php?item0_id='.$individual->getId().'" class="btn btn-sm btn-secondary"><i class="fas fa-plus"></i></a></li>';
							?>
						</ul>	    	
						<?php endif; ?>
				    </div>
				    <div role="tabpanel" class="tab-pane <?php if (strcmp($focus,'onDescription')==0) echo 'active' ?>" id="description-tab">
				    	<div id="individual_description_i" class="individual-description-area"><?php echo $individual->hasDescription() ? $individual->getDescription():'Il était une fois...'; ?></div>
				    </div>
		</div>
			</div>
	    </div>
  	</div>

	<script>
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
				  	case 'descriptionTabSelector':
				  		focus = 'onDescription';
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
	<script>
		const trombiUrl = '<?php echo $system->getTrombiUrl() ?>';
		const trombiReworkUrl = '<?php echo $system->getTrombiReworkUrl() ?>';
		const imageFileExtensions = JSON.parse('<?php echo json_encode($system->getImageFileExtensions()) ?>');
	
		document.addEventListener("DOMContentLoaded", function() {
			const i = document.getElementById('individual_description_i');

			customElements.define("individual-photo", IndividualPhoto, { extends: "img" });
			
			if (i!==null) {
				i.contentEditable = true;
				//i.focus();
				i.addEventListener('blur', function (event) {
				  event.preventDefault();
				  var xhr = new XMLHttpRequest();
				  xhr.open("POST", "api/individuals/", true);
				  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
				  xhr.responseType = 'json';
				  xhr.onreadystatechange = function () {
				    if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
				    	if (this.response.data.location !== undefined) {
					    	window.location.replace(this.response.data.location);
				    	}
			    	}				  
				  };
				  xhr.send("id=<?php echo $individual->getId() ?>&task=updateDescription&description="+encodeURIComponent(i.innerHTML));			
				});
			}
			
			var msItems = document.getElementsByClassName('membership-description-area');
			Array.prototype.filter.call(msItems, function(i){
				i.contentEditable = true;
				i.addEventListener('blur', function (event) {
				  event.preventDefault();
				  var xhr = new XMLHttpRequest();
				  xhr.open("POST", "api/memberships/", true);
				  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
				  xhr.responseType = 'json';
				  xhr.onreadystatechange = function () {
				    if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
				    	if (this.response.data.location !== undefined) {
					    	window.location.replace(this.response.data.location);
				    	}
			    	}				  
				  };
				  xhr.send("id="+i.dataset.msId+"&task=updateDescription&description="+encodeURIComponent(i.innerHTML));			
				});
			});
			
			var rsItems = document.getElementsByClassName('relationship-description-area');
			Array.prototype.filter.call(rsItems, function(i){
				i.contentEditable = true;
				i.addEventListener('blur', function (event) {
				  event.preventDefault();
				  var xhr = new XMLHttpRequest();
				  xhr.open("POST", "api/relationships/", true);
				  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
				  xhr.responseType = 'json';
				  xhr.onreadystatechange = function () {
				    if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
				    	if (this.response.data.location !== undefined) {
					    	window.location.replace(this.response.data.location);
				    	}
			    	}				  
				  };
				  xhr.send("id="+i.dataset.rsId+"&task=updateDescription&description="+encodeURIComponent(i.innerHTML));			
				});
			});			
		});
	</script>
</body>
</html>