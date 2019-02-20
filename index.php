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
	<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<link type="text/css" rel="stylesheet" href="<?php echo FONTAWESOME_CSS_URI ?>" integrity="<?php echo FONTAWESOME_CSS_URI_INTEGRITY ?>" crossorigin="anonymous" />
	<link type="text/css" rel="stylesheet" href="<?php echo JQUERY_UI_CSS_THEME_URI ?>"></link>
	<link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>main.css"></link>
	<?php echo $system->writeHtmlHeadTagsForFavicon(); ?>
	<script type="text/javascript" src="<?php echo JQUERY_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo JQUERY_UI_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo MASONRY_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo IMAGESLOADED_URI ?>"></script>
	<script type="text/javascript" src="<?php echo BOOTSTRAP_JS_URI ?>" integrity="<?php echo BOOTSTRAP_JS_URI_INTEGRITY ?>" crossorigin="anonymous"></script>
</head>
<body id="indexDoc">
    <?php include 'navbar.inc.php'; ?>
    <div class="container-fluid">
		<h1 class="sr-only"><?php echo ToolBox::toHtml($doc_title); ?></h1>
		<div class="row justify-content-md-center">
			<div class="col-md-12">
				<section style="display:none">
					<form method="post" action="societies_list.php" class="form-inline d-lg-flex">
						<div class="form-group m-2 flex-lg-fill">
							<label for="s_name_i" class="mr-2">Une société</label>
							<input id="s_name_i" name="society_name" type="text" class="form-control flex-lg-fill" placeholder="nom" />
						</div>
						<button type="submit" name="society_newsearch" value="1" class="btn btn-default m-2">Retrouver</button>
					</form>
				</section>
				<section>
					<?php
						$industries = $system->getLastUsedIndustries(30);
						$weights = array_column($industries, 'weight');
						$maxWeight = max($weights);
						$minWeight = min($weights);
						$minEm = 1;
						$maxEm = 1.5;

						echo '<div class="tagCloud">';
						foreach($industries as $item) {
							if ($maxWeight == $minWeight) {
								$em = ($maxEm + $minEm) / 2;
							} else {
								$weight = $item['weight'];
								$r = ($weight - $minWeight) / ($maxWeight - $minWeight);
								$em = round($minEm + ($r * ($maxEm - $minEm)),1);
							}
							echo '<span class="badge badge-secondary tag" style="font-size:'.$em.'em; display:inline-block; margin:2px; padding:'.round($em/4,1).'em '.round($em/3,1).'em">';
							echo '<a href="societies_list.php?society_newsearch=1&industry_id='.$item['industry']->getId().'">';
							echo ToolBox::toHtml( $item['industry']->getName() );
							echo '</a></span>';
						}
						echo '<span class="seeMore" style="padding:0 1em"><a href="industries.php">Toutes les activités</a> <i class="fas fa-angle-right"></i></span>';
						echo '</div>';
					?>
				</section>
				<section>
					<form method="post" action="individuals.php" class="form-inline">
						<div class="form-group m-2">
							<label for="individual_lastName_i" class="mr-2">Qui ?</label>
							<input id="individual_wholeName_i" name="individual_wholeName" type="text" placeholder="prénom, nom" class="form-control" />
						</div>
						<button type="submit" name="individual_newsearch" value="1" class="btn btn-default m-2">Retrouver</button>
					</form>
				</section>
				
				<section>
				<?php
					switch($people_focus) {
						case 'onLastPinned' : 
					  		echo '<div class="il">';
					  		echo '<div class="masonryGutterSizer"></div>';
					  		foreach ($individuals as $i) {
								echo '<div class="card">';
								if ($i->getPhotoUrl()) {
									echo '<a href="individual.php?individual_id='.$i->getId().'" class="implicit">';
									echo '<img src="' . $i->getPhotoUrl () . '"  class="card-img-top" />';
									echo '</a>';
								} else {
									/*
									echo '<a href="individual_edit.php?individual_id='.$i->getId().'" class="implicit">';
									echo '<img src="'.$system->getSkinUrl().'/images/missingThumbnail.svg" class="card-img-top missing-thumbnail" />';
									echo '</a>';
									*/
								}
								echo '<div class="card-header">';
									echo '<a href="individual.php?individual_id='.$i->getId().'" class="implicit">'.ToolBox::toHtml($i->getWholeName()).'</a>';
								echo '</div>';
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
								if ($i->getPhotoUrl()) {
									echo '<a href="individual.php?individual_id='.$i->getId().'" class="implicit">';
									echo '<img src="' . $i->getPhotoUrl () . '"  class="card-img-top" />';
									echo '</a>';
								} else {
									/*
									echo '<a href="individual_edit.php?individual_id='.$i->getId().'" class="implicit">';
									echo '<img src="'.$system->getSkinUrl().'/images/missingThumbnail.svg" class="card-img-top missing-thumbnail" />';
									echo '</a>';
									*/
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
				</section>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		$(document).ready(function(){
		    $('#s_name_i').autocomplete({
				minLength: 2,
		   		source: function( request, response ) {
		            $.ajax({
						method:'GET',
		                url:'api/society_names.json.php',
		                dataType: 'json',
		                data:{
		                    'query': request.term
		                 },
		                 dataFilter: function(data,type){
		                     return JSON.stringify(JSON.parse(data).names);
		                 },
		                 success : function(data, textStatus, jqXHR){
							response(data);
		                 }
		         	})
		   		},
		        focus: function( event, ui ) {
					$('#s_name_i').val( ui.item.value );
		        	return false;
		        },
		        select: function( event, ui ) {
					$('#s_name_i').val( ui.item.value );
		        	return false;
		        },
		        _renderItem: function( ul, item ) {
				    //alert(JSON.stringify(item));
				    return $("<li>").append(item.label).appendTo(ul);
			    }
		   	});
		    
			$('.il').masonry({
				itemSelector: '.card',
				columnWidth: '.card',
				gutter: '.masonryGutterSizer'
		    }).imagesLoaded().progress(
				function() {
					$('.il').masonry('layout');	
				}
			);
		})
	</script>
</body>
</html>
