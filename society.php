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

$society = new Society();
if (isset($_REQUEST['society_id'])) {
	$society->setId($_REQUEST['society_id']);
}
$society->initFromDB();

// participations
$memberships = $society->getMemberships();

// participations
$relatedSocieties = $society->getRelatedSocieties();

// pistes
$leads = $society->getLeads();

// évènements
$events = $society->getEvents();

$doc_title = $society->getName();

if (isset($_SESSION['preferences']['society']['focus'])) {
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
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_URI ?>" type="text/css" />
    <link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_THEME_URI ?>" type="text/css" />
    <link rel="stylesheet" href="<?php echo JQUERY_UI_CSS_THEME_URI ?>" type="text/css" />
    <link rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>main.css" type="text/css">
	<script type="text/javascript" src="<?php echo JQUERY_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo JQUERY_UI_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo MASONRY_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo IMAGESLOADED_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo BOOTSTRAP_JS_URI; ?>"></script>
</head>
<body id="societyDoc">
<?php include 'navbar.inc.php'; ?>
<div class="container-fluid">
	<h1><?php echo ToolBox::toHtml($doc_title); ?> <small><a href="society_edit.php?society_id=<?php echo $society->getId() ?>"><span class="glyphicon glyphicon-edit"></span></a></small></h1>
    <section>
        <?php
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
                    $geo_elt[] = '<a href="societies_list.php?society_newsearch=1&society_city='.urlencode($society->getCity()).'">'.ToolBox::toHtml($society->getCity()).'</a>';
                }
                if (count($geo_elt)>0) {
                    $address_elt[] = implode(' ', $geo_elt);
                }
            }
            if ($society->getUrl()) {
                $address_elt [] = '<a href="'.$society->getUrl().'" target="_blank">'.$society->getUrl().'</a>';
            }
            if (count($address_elt)>0) {
                echo '<address>'.implode(' <small>-</small> ', $address_elt).'</address>';
            }
            //
            // description
            //
            if ($society->getDescription()) {
                echo '<blockquote>'.ToolBox::toHtml($society->getDescription()).'</blockquote>';
            }
            //
            // activités
            //
            $industries = $society->getIndustries();
            if (count($industries)>0) {
                echo '<div>';
                foreach ($industries as $i) {
                    echo '<span class="label label-default">'.$i->getHtmlLink().'</span> ';
                }
                echo '<div>';
                
				
				$inSameIndustrySocieties = $society->getInSameIndustrySocieties();
				if ($inSameIndustrySocieties > 0) {
					echo '<div>';
					echo '<p>';
					echo '<small>Similaire à : </small>';
					$links = array();
					foreach($inSameIndustrySocieties as $item) {
						$links[] = '<a href="./society.php?society_id='.$item->getId().'">'.$item->getName().'</a>';
					}
					echo implode($links, ', ');
					echo '</p>';
					echo '</div>';
				}
            }
        ?>
	</section>
	
	<div>
	  <!-- Nav tabs -->
	  <ul class="nav nav-tabs" role="tablist">
	    <li role="presentation" <?php if (strcmp($focus,'onRelatedSocieties')==0) echo  'class="active"' ?>><a id="societiesTabSelector" href="#societies-tab" data-toggle="tab">Sociétés liées <span class="badge"><?php echo count($relatedSocieties) ?></span></a></li>
	    <li role="presentation" <?php if (strcmp($focus,'onIndividuals')==0) echo 'class="active"' ?>><a id="individualsTabSelector" href="#individuals-tab" aria-controls="individuals-tab" role="tab" data-toggle="tab">Gens <span class="badge"><?php echo count($memberships) ?></span></a></li>
	    <li role="presentation" <?php if (strcmp($focus,'onLeads')==0) echo 'class="active"' ?>><a id="leadsTabSelector" href="#leads-tab" aria-controls="leads-tab" role="tab" data-toggle="tab">Pistes <span class="badge"><?php echo count($leads) ?></span></a></li>
	    <li role="presentation" <?php if (strcmp($focus,'onEvents')==0) echo 'class="active"' ?>><a id="eventsTabSelector" href="#events-tab" aria-controls="events-tab" role="tab" data-toggle="tab">Evénements <span class="badge"><?php echo count($events) ?></span></a></li>
	  </ul>
	
	  <!-- Tab panes -->
	  <div class="tab-content">
	    <div role="tabpanel" class="tab-pane<?php if (strcmp($focus,'onRelatedSocieties')==0) echo ' active' ?>" id="societies-tab">
			<h2>Sociétés liées<small><a href="relationship_edit.php?item0_class=Society&amp;item0_id=<?php echo $society->getId() ?>"> <span class="glyphicon glyphicon-plus"></span></a></a></small></h2>
			<?php if (isset($relatedSocieties)): ?>
			<ul class="list-group">
				<?php
				foreach ($relatedSocieties as $item) {
					// $item[0] : Société
					// $item[1] : Identifiant de la relation;
					// $item[2] : Rôle
					// $item[3] : Description
					echo '<li class="list-group-item">';
					echo '<h3>';
					echo '<a href="society.php?society_id='.$item[0]->getId().'">'.$item[0]->getNameForHtmlDisplay().'</a>';
					echo ' <small>(';
					echo '<a href="relationship_edit.php?relationship_id='.$item[1].'">';
					echo empty($item[2]) ? '?' : ToolBox::toHtml($item[2]);
					echo '</a>';
					echo ')</small>';
					echo '</h3>';
					if (!empty($item[3])) {
						echo '<p>';
						echo ToolBox::toHtml($item[3]);
						echo '</p>';
					}
					echo '</li>';
				}
				?>
			</ul>
			<?php endif; ?>
	    </div>
	    <div role="tabpanel" class="tab-pane<?php if (strcmp($focus,'onIndividuals')==0) echo ' active' ?>" id="individuals-tab">
			<h2>Les gens<small><a href="membership_edit.php?society_id=<?php echo $society->getId() ?>"> <span class="glyphicon glyphicon-plus"></span></a></small></h2>
			<?php
				if ($memberships) {
			  		echo '<div class="il">';
			  		echo '<div class="masonryGutterSizer"></div>';
			  		foreach ($memberships as $ms) {
						$i = $ms->getIndividual();
						$i->feed();
						echo '<div class="thumbnail">';
						if ($i->getPhotoUrl()) {
							echo $i->getPhotoHtml();
						} else {
							echo '<img src="'.$system->getSkinUrl().'/images/missingThumbnail.svg" class="img-responsive" />';
						}
						echo '<div class="caption">';
							echo '<h3>';
							echo '<a href="individual.php?individual_id='.$i->getId().'">'.ToolBox::toHtml($i->getWholeName()).'</a>';
							$position_elt = array();
							if ($ms->getDepartment()) $position_elt[] = ToolBox::toHtml($ms->getDepartment());
							if ($ms->getTitle()) $position_elt[] = ToolBox::toHtml($ms->getTitle());
							
							$smallTag_elt = array();
							
							if (count($position_elt)>0) {
								$smallTag_elt[] = implode(' / ', $position_elt);
							}
							if ( $ms->getPeriod() ) $smallTag_elt[] = '('.$ms->getPeriod().')';
							
							if (count($smallTag_elt)>0) {
								echo '<div><small>'.implode(' ', $smallTag_elt).'</small></div>';
							}					
							echo '</h3>';
							echo '<p><a href="membership_edit.php?membership_id='.$ms->getId().'" title="éditer la participation de '.ToolBox::toHtml($i->getWholeName()).'"><span class="glyphicon glyphicon-edit"></span> édition</a></p>';
						echo '</div>';
						echo '</div>';
				  	}
					echo '</div>';				  	
				}
			?>
	    </div>
	    <div role="tabpanel" class="tab-pane<?php if (strcmp($focus,'onLeads')==0) echo ' active' ?>" id="leads-tab">
			<h2>Les pistes<small><a href="lead_edit.php?society_id=<?php echo $society->getId() ?>"> <span class="glyphicon glyphicon-plus"></span></a></small></h2>
			<?php if ($leads): ?>
			<ul class="list-group">
				<?php
				foreach ($leads as $l) {
					echo '<li class="list-group-item">';
					echo '<h3>';
					echo '<a href="lead_edit.php?lead_id='.$l->getId().'">';
					echo $l->getShortDescription() ? ToolBox::toHtml($l->getShortDescription()) : 'Piste n°'.$l->getId();
					echo '</a>';
					if ($l->getCreationDate()) echo ' <small>('.ToolBox::toHtml($l->getCreationDateFr()).')</small>';
					echo '</h3>';
					echo '</li>';
				}
				?>
			</ul>
			<?php endif; ?>
	    </div>
	    <div role="tabpanel" class="tab-pane<?php if (strcmp($focus,'onEvents')==0) echo ' active' ?>" id="events-tab">
			<h2>Évènements<small><a href="society_event_edit.php?society_id=<?php echo $society->getId() ?>"> <span class="glyphicon glyphicon-plus"></span></a></small></h2>
			<?php if ($events): ?>
			<ul class="list-group">
				<?php
				foreach ($events as $e) {
					echo '<li class="list-group-item">';
					echo '<h3>';
					echo '<a href="society_event_edit.php?event_id='.$e->getId().'">';
					echo date("d/m/Y", ToolBox::mktimeFromMySqlDatetime($e->getDatetime()));
					echo '<small> ('.ToolBox::toHtml(ucfirst($e->getType())).')</small>';
					echo '</a>';
					echo '</h3>';
					echo '<p>'.nl2br(ToolBox::toHtml($e->getComment())).'</p>';
					echo '</li>';
				}
				?>
			</ul>
			<?php endif; ?>
	    </div>
	  </div>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function() {
		
		// https://www.sitepoint.com/bootstrap-tabs-play-nice-with-masonry/
		
	    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
			var focus;
			var scope = 'society';

			switch(e.target.id) {
			  	case 'societiesTabSelector':
			  		focus = 'onRelatedSocieties';
			  		break;
			  	case 'individualsTabSelector':
			  		focus = 'onIndividuals';
			  		$('.il').masonry('layout');
			  		break;
			  	case 'leadsTabSelector':
			  		focus = 'onLeads';
			  		break;
			  	case 'eventsTabSelector':
			  		focus = 'onEvents';
			  		break;			  		
			}

			$.ajax({
				  url: 'session.ws.php?focus='+focus+'&scope='+scope,
				  beforeSend: function( xhr ) {
				    xhr.overrideMimeType( "text/plain; charset=x-user-defined" );
				  }
			});
		});

		$('.il').masonry({
	      itemSelector: '.thumbnail',
	      columnWidth: '.thumbnail',
	      gutter: '.masonryGutterSizer'
	    }).imagesLoaded().progress(function() {
				$('.il').masonry('layout');
			}
		);

	});
</script>
</body>
</html>