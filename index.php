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
	
	if ( isset($_REQUEST['memberships_focus']) ) {
	    if (!isset($_SESSION['preferences'])) {
	        $_SESSION['preferences'] = array();
	    }
        if ( ! isset($_SESSION['preferences']['memberships']) ) {
            $_SESSION['preferences']['memberships'] = array();
        }
        $_SESSION['preferences']['memberships']['focus'] = $_REQUEST['memberships_focus'];
	    //var_dump($_SESSION);
	}
	
	if (!empty($_SESSION['preferences']['memberships']['focus'])) {
		$memberships_focus = $_SESSION['preferences']['memberships']['focus'];
	} else {
		$memberships_focus = 'onLastPinned';
	}

	switch($memberships_focus) {
		case 'onLastPinned' : 
			$memberships = $system->getMemberships(array('active'=>true, 'everPinnedIndividual'=>true), 'Last pinned first', 0, 12);
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
	<link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_URI ?>" type="text/css" />
	<link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_THEME_URI ?>" type="text/css" />
	<link rel="stylesheet" href="<?php echo JQUERY_UI_CSS_THEME_URI ?>" type="text/css" />
	<link rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>main.css" type="text/css">
	<script type="text/javascript" src="<?php echo JQUERY_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo JQUERY_UI_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo MASONRY_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo IMAGESLOADED_URI ?>"></script>
	<script type="text/javascript" src="<?php echo BOOTSTRAP_JS_URI; ?>"></script>
</head>
<body id="indexDoc">
    <?php include 'navbar.inc.php'; ?>
    <div class="container-fluid">
		<h1><?php echo ToolBox::toHtml($doc_title); ?></h1>
		<div class="row justify-content-md-center">
			<div class="col-md-10 col-md-offset-1">
				<section>
					<form method="post" action="societies_list.php" class="form-inline">
						<div class="form-group">
							<label for="s_name_i">Une société</label>
							<input id="s_name_i" name="society_name" type="text" class="form-control" placeholder="nom" />
						</div>
						<button type="submit" name="society_newsearch" value="1" class="btn btn-default">Retrouver</button>
					</form>
				</section>
				<section>
					<?php
						$industries = $system->getLastUsedIndustries();
						$weights = array_column($industries, 'weight');
						$maxWeight = max($weights);
						$minWeight = min($weights);
						$minEm = 1;
						$maxEm = 1.5;

						echo '<div class="tagCloud">';
						foreach($system->getLastUsedIndustries() as $item) {
							if ($maxWeight == $minWeight) {
								$em = ($maxEm + $minEm) / 2;
							} else {
								$weight = $item['weight'];
								$r = ($weight - $minWeight) / ($maxWeight - $minWeight);
								$em = round($minEm + ($r * ($maxEm - $minEm)),1);
							}
							echo '<span class="label label-default" style="font-size:'.$em.'em; display:inline-block; margin:2px; padding:'.round($em/4,1).'em '.round($em/3,1).'em">';
							echo '<a href="societies_list.php?society_newsearch=1&industry_id='.$item['industry']->getId().'">';
							echo ToolBox::toHtml( $item['industry']->getName() );
							echo '</a></span>';
						}
						echo '</div>';
						echo '<div class="seeMore"><a href="industries.php">Toutes les activités</a></div>';
					?>
				</section>
				<section>
					<form method="post" action="individuals.php" class="form-inline">
						<div class="form-group">
							<label for="individual_lastName_i">Un individu</label> <input id="individual_lastName_i" name="individual_lastName" type="text" placeholder="nom de famille" class="form-control" />
						</div>
						<button type="submit" name="individual_newsearch" value="1" class="btn btn-default">Retrouver</button>
					</form>
				</section>
				
				<?php
				if ($memberships) {
					echo '<section>';
			  		echo '<div class="il">';
			  		echo '<div class="masonryGutterSizer"></div>';
			  		foreach ($memberships as $ms) {
						$i = $ms->getIndividual();
						$s = $ms->getSociety();
						echo '<div class="thumbnail">';
						if ($i->getPhotoUrl()) {
							echo $i->getPhotoHtml();
						} else {
							echo '<img src="'.$system->getSkinUrl().'/images/missingThumbnail.svg" class="img-responsive" />';
						}
						echo '<div class="caption">';
							echo '<h3>';
							echo '<a href="individual.php?individual_id='.$i->getId().'">'.ToolBox::toHtml($i->getWholeName()).'</a>';
							echo '<br><small>'.$s->getHtmlLinkToSociety().'</small>';
							echo '</h3>';
							echo '<div>';
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
							echo '<p><a href="membership_edit.php?membership_id='.$ms->getId().'" title="éditer la participation de '.ToolBox::toHtml($i->getWholeName()).'"><span class="glyphicon glyphicon-edit"></span> édition</a></p>';
						echo '</div>';
						echo '</div>';
				  	}
					echo '</div>';
					echo '<div>';
					switch($memberships_focus) {
						case 'onLastPinned' : 
							echo '<a href="index.php?memberships_focus=onLastUpdated">Voir les dernières participations mise à jour</a>';
							break;
						case 'onLastUpdated' : 
							echo '<a href="index.php?memberships_focus=onLastPinned">Voir les participations des derniers gens épinglés</a>';
							break;			
					}
					echo '</div>';
					echo '</section>';
				}
				?>
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
		                url:'society_names.json.php',
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
				itemSelector: '.thumbnail',
				columnWidth: '.thumbnail',
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
