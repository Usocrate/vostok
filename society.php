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

$society = new Society();
if (isset($_REQUEST['society_id'])) {
	$society->setId($_REQUEST['society_id']);
}
$society->initFromDB();

// participations
$memberships = $society->getMemberships();
if ($memberships) {
	// regroupement des participations par individu	
	$members = array();
	foreach ($memberships as $ms) {
		$i = $ms->getIndividual();
		$key = $i->getId();
		if( isset($members[$key]) ) {
			array_push($members[$key], $ms);
		} else {
			$members[$key] = array($ms);
		}
	}
	unset($memberships);
	
	
	$actualMembers = array();
	$formerMembers = array();
	
	foreach ($members as $id=>$memberships) {
		$pastMembershipCount = 0;
		foreach ($memberships as $ms) {
			if ($ms->hasEndYear()) {
				$pastMembershipCount++;
			}
		}
		count($memberships) == $pastMembershipCount ? $formerMembers[$id]=$memberships : $actualMembers[$id]=$memberships;
	}
}

// participations
$relatedSocieties = $society->getRelatedSocieties();

// pistes
$leads = $society->getLeads();

// évènements
$events = $society->getEvents();

$doc_title = $society->getName();

if (isset($_REQUEST['focus'])) {
	if (!isset($_SESSION['preferences'])) {
		$_SESSION['preferences'] = array();
	}
	if (!isset($_SESSION['preferences']['society'])) {
		$_SESSION['preferences']['society'] = array();
	}
	$_SESSION['preferences']['society']['focus'] = $_REQUEST['focus'];
}

if (!empty($_SESSION['preferences']['society']['focus'])) {
	$focus = $_SESSION['preferences']['society']['focus'];
} else {
	$focus = 'onRelatedSocieties';
}
//print_r($_SESSION);
//print_r($preferences);
?>
<!doctype html>
<html lang="fr">
<head>
    <title>Un des comptes (sa fiche détaillées)</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
    <link type="text/css" rel="stylesheet" href="<?php echo FONTAWESOME_CSS_URI ?>" integrity="<?php echo FONTAWESOME_CSS_URI_INTEGRITY ?>" crossorigin="anonymous" />    
    <link type="text/css" rel="stylesheet" href="<?php echo JQUERY_UI_CSS_THEME_URI ?>"></link>
    <link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>theme.css"></link>
    <?php echo $system->writeHtmlHeadTagsForFavicon(); ?>
	<script src="<?php echo JQUERY_URI; ?>"></script>
	<script src="<?php echo JQUERY_UI_URI; ?>"></script>
	<script src="js/masonry.pkgd.min.js"></script>
	<script src="js/imagesloaded.pkgd.min.js"></script>
	<script src="js/individual-photo.js"></script>	
	<script src="<?php echo BOOTSTRAP_JS_URI ?>" integrity="<?php echo BOOTSTRAP_JS_URI_INTEGRITY ?>" crossorigin="anonymous"></script>
</head>
<body id="societyDoc">
<?php include 'navbar.inc.php'; ?>
<div class="container-fluid">
	<h1 class="bd-title"><?php echo ToolBox::toHtml($doc_title); ?> <small><a href="society_edit.php?society_id=<?php echo $society->getId() ?>"><i class="fas fa-edit"></i></a> <a href="<?php echo ToolBox::getGoogleQueryUrl($society->getName()) ?>" target="_blank"><i class="fab fa-google"></i></a></small></h1>
    <?php
    	$html = '';
        //
        // adresse physique et url
        //
        $address_elt = array();
        if ($society->getStreet() || $society->getCity() || $society->getPostalcode()){
            $geo_elt = array();
            if ($society->getStreet()) {
                $geo_elt[] = ToolBox::toHtml($society->getStreet());
            }
            if ($society->getPostalCode()) {
                $geo_elt[] = $society->getPostalCode();
            }
            if ($society->getCity()) {
                $geo_elt[] = '<a href="societies.php?society_newsearch=1&society_city='.urlencode($society->getCity()).'" class="implicit">'.ToolBox::toHtml($society->getCity()).'</a>';
            }
            if (count($geo_elt)>0) {
                $address_elt[] = implode(' ', $geo_elt);
            }
        }
        if ($society->getUrl()) {
            $address_elt [] = '<a href="'.$society->getUrl().'" target="_blank">'.$society->getUrl().'</a>';
        }
        if (count($address_elt)>0) {
            $html.= '<address>'.implode(' <small>-</small> ', $address_elt).'</address>';
        }
        //
        // description
        //
        if ($society->getDescription()) {
            $html.= '<blockquote>'.ToolBox::toHtml($society->getDescription()).'</blockquote>';
        }
        //
        // activités
        //
        $industries = $society->getIndustries();
        if (count($industries)>0) {
            $html.= '<div>';
            foreach ($industries as $i) {
                $html.= '<span class="badge badge-info">'.$i->getHtmlLink().'</span> ';
            }
            $html.= '</div>';

			$inSameIndustrySocieties = $society->getInSameIndustrySocieties();
			if (count($inSameIndustrySocieties) > 0) {
				$html.= '<div>';
				$html.= '<p><small>Similaire à : </small>';
				$links = array();
				foreach($inSameIndustrySocieties as $item) {
					$links[] = '<a href="./society.php?society_id='.$item->getId().'">'.$item->getName().'</a>';
				}
				$html.= implode(', ',$links);
				$html.= '</p>';
				$html.= '</div>';
			}
        }
        if (!empty($html)) {
        	echo '<section>'.$html.'</section>';
        }
    ?>

	<div>
	  <!-- Nav tabs -->
	  <ul class="nav nav-tabs">
	    <li class="nav-item">
	    	<a class="nav-link <?php if (strcmp($focus,'onRelatedSocieties')==0) echo ' active' ?>" id="societiesTabSelector" href="#societies-tab" data-toggle="tab">Sociétés liées <span class="badge badge-secondary"><?php echo isset($relatedSocieties) ? count($relatedSocieties) : '0' ?></span></a>
    	</li>
	    <li class="nav-item">
	    	<a class="nav-link <?php if (strcmp($focus,'onIndividuals')==0) echo ' active' ?>" id="individualsTabSelector" href="#individuals-tab" aria-controls="individuals-tab" role="tab" data-toggle="tab">Gens <span class="badge badge-secondary"><?php echo isset($members) ? count($members) : '0' ?></span></a>
    	</li>
	    <li class="nav-item">
	    	<a class="nav-link <?php if (strcmp($focus,'onLeads')==0) echo ' active' ?>" id="leadsTabSelector" href="#leads-tab" aria-controls="leads-tab" role="tab" data-toggle="tab">Pistes <span class="badge badge-secondary"><?php echo isset($leads) ? count($leads) : '0' ?></span></a>
    	</li>
	    <li class="nav-item">
	    	<a class="nav-link <?php if (strcmp($focus,'onEvents')==0) echo ' active' ?>" id="eventsTabSelector" href="#events-tab" aria-controls="events-tab" role="tab" data-toggle="tab">Evénements <span class="badge badge-secondary"><?php echo isset($events) ? count($events) : '0' ?></span></a>
	    </li>
	  </ul>
	  
	  <!-- Tab panes -->
	  <div class="tab-content">
	    <div role="tabpanel" class="tab-pane<?php if (strcmp($focus,'onRelatedSocieties')==0) echo ' active' ?>" id="societies-tab">
			
				<?php
				$groups = array();
				foreach ($relatedSocieties as $item) {
					if (!isset($groups[$item[2]])) {
						$groups[$item[2]] = array();
					}
					$groups[$item[2]][] = $item;
				}
				foreach ($groups as $name=>$societies) {
					echo '<section>';

					echo '<div class="d-lg-flex flex-lg-row justify-content-between align-items-center mb-3 mt-3">';
					echo '<h2><a href="relationshipSocietyRole.php?role='.ToolBox::toHtml($name).'">'.ToolBox::toHtml($name).'</a>';
					if (count($societies) > 1) {
						echo ' <small>'.count($societies).'</small>';
					}
					echo '</h2>';
					echo '<div>';
					echo '<a href="societyToSocietyRelationship_edit.php?item0_id='.$society->getId().'&item1_role='.ToolBox::toHtml($name).'" class="btn btn-sm btn btn-outline-secondary" ><i class="fas fa-plus"></i></a>';
					echo '</div>';
					echo '</div>';
					echo '<ul class="list-group list-group-flush">';
					foreach ($societies as $item) {
						// $item[0] : Société
						// $item[1] : Identifiant de la relation;
						// $item[2] : Rôle
						// $item[3] : Description
						echo '<li class="container p-2 list-group-item">';
						echo '<div class="row">';
						
						echo '<div class="col-lg-5 align-self-start">';
						echo '<h3><a href="society.php?society_id='.$item[0]->getId().'">'.$item[0]->getNameForHtmlDisplay().'</a> <small><a href="societyToSocietyRelationship_edit.php?relationship_id='.$item[1].'"><i class="fas fa-edit"></i></a></small></h3>';
						if (!empty($item[5])) {
							echo '<div><small>Jusqu\'en '.$item[5].'</small></div>';
						}
						elseif (!empty($item[4])) {
							echo '<div><small>Depuis '.$item[4].'</small></div>';
						}
						echo '</div>';
						
						echo '<div class="col-lg-7 align-self-center">';
						if (!empty($item[3])) {
							echo '<div>'.ToolBox::toHtml($item[3]).'</div>';
						}
						echo '</div>';
						
						echo '</div>';
						echo '</li>';
					}
					echo '</ul>';
					echo '</section>';
				}
				echo '<div><a href="societyToSocietyRelationship_edit.php?item0_id='.$society->getId().'" class="btn btn-sm btn-secondary"><i class="fas fa-plus"></i></a></div>';
				?>
			
	    </div>
	    <div role="tabpanel" class="tab-pane<?php if (strcmp($focus,'onIndividuals')==0) echo ' active' ?>" id="individuals-tab">
			<h2>Les gens<small><a href="membership_edit.php?society_id=<?php echo $society->getId() ?>"> <i class="fas fa-plus"></i></a></small></h2>
			<?php
				if (isset($members)) {
				    if (count($actualMembers)>0) {
    			  		echo '<div class="il">';
    			  		echo '<div class="masonryGutterSizer"></div>';
    			  		foreach ($actualMembers as $id=>$memberships) {
    						$i = new Individual($id);
    						$i->feed();
    						echo '<div class="card">';
    						if ($i->hasPhoto()) {
    							echo '<a href="individual.php?individual_id='.$i->getId().'" class="implicit card-img-top-wrapper">';
    							echo '<img is="individual-photo" data-individual-id="'.$i->getId().'" src="' . $i->getReworkedPhotoUrl () . '" class="card-img-top"></img>';
    							echo '</a>';
    						} else {
    							//echo '<img src="'.$system->getSkinUrl().'/images/missingThumbnail.svg" class="card-img-top missing-thumbnail" />';
    						}
    						
    						$card_title_tag = '<h3 class="card-title"><a href="individual.php?individual_id='.$i->getId().'">'.ToolBox::toHtml($i->getWholeName()).'</a></h3>';
    						
    						if (count($memberships)>1) {
    						    echo '<div class="card-body">'.$card_title_tag.'</div>';
    						    echo '<ul class="list-group list-group-flush">';
    						    foreach ($memberships as $ms) {
    						        echo '<li class="list-group-item">';
    						        echo '<a href="membership_edit.php?membership_id='.$ms->getId().'" class="implicit">'.ToolBox::toHtml($ms->getTitle()).'</a>';
    						        if ( $ms->getPeriod() ) echo ' <small>('.$ms->getPeriod().')</small>';
    						        if ( $ms->hasDescription() ) {
    						        	echo $ms->getHtmlExpandableDescription();
    						        }
    						        //if ($ms->getDepartment()) echo '<p><small>'.ToolBox::toHtml($ms->getDepartment()).'</small></p>';
    						        echo '</li>';
    						    }
    						    echo '</ul>';
    						} else {
    						    echo '<div class="card-body">';
    						    echo $card_title_tag;
    						    echo '<p>';	
    						    echo '<a href="membership_edit.php?membership_id='.current($memberships)->getId().'" class="implicit">'.ToolBox::toHtml(current($memberships)->getTitle()).'</a>';
    						    if ( current($memberships)->getPeriod() ) echo ' <small>('.current($memberships)->getPeriod().')</small>';
    						    echo '</p>';
    						    if ( current($memberships)->hasDescription() ) {
    						    	echo current($memberships)->getHtmlExpandableDescription();
    						    	//echo '<div class="description">'.ToolBox::toHtml(current($memberships)->getDescription()).'</div>';
    						    }
    						    //if (current($memberships)->getDepartment()) echo '<p><small>'.ToolBox::toHtml(current($memberships)->getDepartment()).'</small></p>';
    						    echo '<div><a href="membership_edit.php?membership_id='.current($memberships)->getId().'" class="btn btn-sm btn-outline-secondary">édition</a></div>';
    						    echo '</div>';
    						}
    						echo '</div>';
    				  	}
    					echo '</div>';

    					if (count($relatedSocieties)>0 && count($actualMembers)>1) {
    						echo '<div><a href="society_individual_transfer.php?society_id='.$society->getId().'">Transférer des gens</a></div>';
    					}
				    }
					
					if (count($formerMembers)>0) {
    					echo '<div class="il-legend">';
    					echo count($formerMembers) > 1 ? 'Ont quitté '.$society->getHtmlLinkToSociety().'...':'A quitté '.$society->getHtmlLinkToSociety().'...';
    					echo '</div>';
    					echo '<div class="il">';
    					echo '<div class="masonryGutterSizer"></div>';
    					foreach ($formerMembers as $id=>$memberships) {
    					    $i = new Individual($id);
    					    $i->feed();
    					    echo '<div class="card">';
    					    if ($i->hasPhoto()) {
    					    	echo '<a href="individual.php?individual_id='.$i->getId().'" class="implicit card-img-top-wrapper">';
    					    	echo '<img is="individual-photo" data-individual-id="'.$i->getId().'" src="' . $i->getReworkedPhotoUrl () . '" class="card-img-top"></img>';
    					    	echo '</a>';
    					    } else {
    					    	//echo '<img src="'.$system->getSkinUrl().'/images/missingThumbnail.svg" class="card-img-top missing-thumbnail" />';
    					    }
    					    
    					    $card_title_tag = '<h3 class="card-title"><a href="individual.php?individual_id='.$i->getId().'">'.ToolBox::toHtml($i->getWholeName()).'</a></h3>';
    					    
    					    if (count($memberships)>1) {
    					        echo '<div class="card-body">'.$card_title_tag.'</div>';
    					        echo '<ul class="list-group list-group-flush">';
    					        foreach ($memberships as $ms) {
    					            echo '<li class="list-group-item">';
    					            echo '<a href="membership_edit.php?membership_id='.$ms->getId().'" class="implicit">'.ToolBox::toHtml($ms->getTitle()).'</a>';
    					            if ( $ms->getPeriod() ) echo ' <small>('.$ms->getPeriod().')</small>';
    					            if ( $ms->hasDescription() ) {
    					            	echo $ms->getHtmlExpandableDescription();
    					            }
    					            //if ($ms->getDepartment()) echo '<p><small>'.ToolBox::toHtml($ms->getDepartment()).'</small></p>';
    					            echo '</li>';
    					        }
    					        echo '</ul>';
    					    } else {
    					        echo '<div class="card-body">';
    					        echo $card_title_tag;
    					        echo '<p>';
    					        echo '<a href="membership_edit.php?membership_id='.current($memberships)->getId().'" class="implicit">'.ToolBox::toHtml(current($memberships)->getTitle()).'</a>';
    					        if ( current($memberships)->getPeriod() ) echo ' <small>('.current($memberships)->getPeriod().')</small>';
    					        echo '</p>';
    					        echo current($memberships)->getHtmlExpandableDescription();
    					        //if (current($memberships)->getDepartment()) echo '<p><small>'.ToolBox::toHtml(current($memberships)->getDepartment()).'</small></p>';
    					        
    					        echo '<div><a href="membership_edit.php?membership_id='.current($memberships)->getId().'" class="btn btn-sm btn-outline-secondary">édition</a></div>';
    					        echo '</div>';
    					    }
    					    
    					    echo '</div>';
    					}
    					echo '</div>';
					}
				}
			?>
	    </div>
	    <div role="tabpanel" class="tab-pane<?php if (strcmp($focus,'onLeads')==0) echo ' active' ?>" id="leads-tab">
			<ul class="list-group list-group-flush">
				<?php
				foreach ($leads as $l) {
					echo '<li class="list-group-item">';
					echo '<h2>';
					echo '<a href="lead_edit.php?lead_id='.$l->getId().'">';
					echo $l->getShortDescription() ? ToolBox::toHtml($l->getShortDescription()) : 'Piste n°'.$l->getId();
					echo '</a>';
					if ($l->getCreationDate()) echo ' <small>('.ToolBox::toHtml($l->getCreationDateFr()).')';
					echo ' <a href="lead_edit.php?lead_id='.$l->getId().'"><i class="fas fa-edit"></i></a>';
					echo '</small>';
					echo '</h2>';
					echo '</li>';
				}
				echo '<li class="list-group-item"><a href="lead_edit.php?society_id='.$society->getId().'" class="btn btn-sm btn-secondary"><i class="fas fa-plus"></i></a></li>';
				?>
			</ul>
	    </div>
	    <div role="tabpanel" class="tab-pane<?php if (strcmp($focus,'onEvents')==0) echo ' active' ?>" id="events-tab">
			<ul class="list-group list-group-flush">
				<?php
				foreach ($events as $e) {
					echo '<li class="list-group-item">';
					echo '<h2>';
					echo '<a href="society_event_edit.php?event_id='.$e->getId().'">';
					echo date("d/m/Y", ToolBox::mktimeFromMySqlDatetime($e->getDatetime()));
					echo '</a>';
					echo ' <small>('.ToolBox::toHtml(ucfirst($e->getType())).')';
					echo ' <a href="society_event_edit.php?event_id='.$e->getId().'"><i class="fas fa-edit"></i></a>';					
					echo '</small>';
					echo '</h2>';
					echo '<p>'.nl2br(ToolBox::toHtml($e->getComment())).'</p>';
					echo '</li>';
				}
				echo '<li class="list-group-item"><a href="society_event_edit.php?society_id='.$society->getId().'" class="btn btn-sm btn-secondary"><i class="fas fa-plus"></i></a></li>';
				?>
			</ul>
	    </div>
	  </div>
	</div>
</div>
<script>
	const trombiReworkUrl = '<?php echo $system->getTrombiReworkUrl() ?>';
	var masonries = [];

	document.addEventListener("DOMContentLoaded", function() {
		const ils = document.querySelectorAll('.il');
		
		customElements.define("individual-photo", IndividualPhoto, { extends: "img" });

		imagesLoaded(ils, function(){
			for (let il of ils) {
				let m = new Masonry( il, {
					itemSelector: '.card',
					columnWidth:  '.card',
					gutter: '.masonryGutterSizer'
				});
				masonries.push(m);
			}
		});

	    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
			var focus;
			var scope = 'society';

			switch(e.target.id) {
			  	case 'societiesTabSelector':
			  		focus = 'onRelatedSocieties';
			  		break;
			  	case 'individualsTabSelector':
			  		focus = 'onIndividuals';
			  		for (let m of masonries) {
				  		m.layout();
			  		}
			  		break;
			  	case 'leadsTabSelector':
			  		focus = 'onLeads';
			  		break;
			  	case 'eventsTabSelector':
			  		focus = 'onEvents';
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