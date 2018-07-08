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
}

// nombre de societies à afficher par page
$page_items_nb = 14;

// si ordre d'effectuer une nouvelle recherche,
// les données propres à la sélection de societies courante sont réinitialisées
if (isset ( $_REQUEST ['society_newsearch'] ) || ! isset( $_SESSION ['society_search'] )) {
	$_SESSION ['society_search'] = array ();
	$_SESSION ['society_search']['criteria'] = array ();

	if (isset ( $_REQUEST ['society_name'] ) && ! empty($_REQUEST ['society_name']) ) {
		$_SESSION ['society_search']['criteria']['name'] = $_REQUEST ['society_name'];
	}
	
	if (isset ( $_REQUEST ['industry_id'] ) && ! empty ( $_REQUEST ['industry_id']) ) {
		$_SESSION ['society_search']['criteria']['industry_id'] = $_REQUEST ['industry_id'];
	}
	
	if (isset ( $_REQUEST ['society_city'] ) && ! empty($_REQUEST ['society_city']) ) {
		$_SESSION ['society_search']['criteria']['city'] = $_REQUEST ['society_city'];
	}
	
	$_SESSION ['society_search']['page_index'] = 1;
	$_SESSION ['society_search']['sort'] = 'Last created first';
}

// nb de comptes correspondant aux critères
$items_nb = $system->getSocietiesNb ( $_SESSION ['society_search']['criteria'] );
$pages_nb = ceil ( $items_nb / $page_items_nb );

// changement de page
if (isset ( $_REQUEST ['society_search_page_index'] )) {
	$_SESSION ['society_search'] ['page_index'] = $_REQUEST ['society_search_page_index'];
}

// sélection de sociétés correspondant aux critères (dont le nombre dépend de la variable $page_items_nb)
$page_debut = ($_SESSION ['society_search'] ['page_index'] - 1) * $page_items_nb;
$societies = $system->getSocieties( $_SESSION ['society_search']['criteria'], $_SESSION ['society_search']['sort'], $page_debut, $page_items_nb );

// si une seule société redirection vers fiche individuelle.
if (count ( $societies ) == 1) {
    // on considère que la recherche est arrivée à son terme
    unset($_SESSION['society_search']);
	header ( 'Location:society.php?society_id=' . $societies [0]->getId () );
	exit ();
}

// redirection vers création de fiche société.
if (count ( $societies ) == 0 && isset($_SESSION ['society_search']['criteria']['name'])) {
    $name = $_SESSION ['society_search']['criteria']['name'];
    // on considère que la recherche est arrivée à son terme
    unset($_SESSION['society_search']);
	header ( 'Location:society_edit.php?society_name=' . $name );
	exit ();
}

$doc_title = 'Les sociétés qui m\'intéressent';
?>
<!doctype html>
<html lang="fr">
<head>
    <title><?php echo $system->getAppliName() ?>: Liste des Sociétés</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_URI ?>" type="text/css" />
    <link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_THEME_URI ?>" type="text/css" />
    <link rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>main.css" type="text/css">
    <script type="application/javascript" src="js/controls.js"></script>
	<script type="text/javascript" src="<?php echo JQUERY_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo JQUERY_UI_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo MASONRY_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo IMAGESLOADED_URI ?>"></script>
	<script type="text/javascript" src="<?php echo BOOTSTRAP_JS_URI; ?>"></script>
</head>
<body id="societiesListDoc">
<?php include 'navbar.inc.php'; ?>
<div class="container-fluid">
	<h1><?php echo ToolBox::toHtml($doc_title); ?> <small><a href="society_edit.php"><span class="glyphicon glyphicon-plus"></span></a></small></h1>
	<section>
	   	<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" class="form-inline">
			<div class="form-group">
	    		<label for="s_name_i">Nom</label>
	    		<input id="s_name_i" name="society_name" type="text" value="<?php if (isset($_SESSION ['society_search']['criteria']['name'])) echo $_SESSION ['society_search']['criteria']['name']; ?>" class="form-control" /> 
			</div>
			<div class="form-group">
	    		<label for="s_industry_i">Activité</label>
	    		<select id="s_industry_i" name="industry_id" class="form-control">
	    			<option value="">-- choisir --</option>
	    			<?php echo isset($_SESSION['society_search']['criteria']['industry_id']) ? $system->getIndustryOptionsTags($_SESSION['society_search']['criteria']['industry_id']) : $system->getIndustryOptionsTags(); ?>
	    		</select>
			</div>
			<div class="form-group">
	    		<label for="s_city_i">Ville</label>
	    		<input id="s_city_i" name="society_city" value="<?php if (isset($_SESSION ['society_search']['criteria']['city'])) echo $_SESSION ['society_search']['criteria']['city']; ?>" class="form-control"></input>
			</div>
	   		<button type="submit" name="society_newsearch" value="filtrer" class="btn btn-default">Filtrer</button>
	   		<?php if( count($_SESSION['society_search']['criteria']) > 0) echo ' <a href="societies_list.php?society_newsearch=1">Toutes les sociétés</a>'  ?>
		</form>
   	</section>
   	<div class="row">
		<div class="col-md-7">
		   	<section>
		       	<form method="post" action="societies_merge.php">
		    		<div class="list-group">
						<?php
						foreach ( $societies as $s ) {
							echo '<div class="list-group-item">';
							echo '<div style="display:inline-block; vertical-align:top; margin:6px 6px 6px 0;">';
							echo '<input name="society_id[]" type="checkbox" value="' . $s->getId () . '" />';
							echo '</div>';
							echo '<div style="display:inline-block">';
							echo '<h2 class="list-group-item-heading"><a href="society.php?society_id=' . $s->getId () . '">' . $s->getNameForHtmlDisplay () . '</a>';
							if ($s->getUrl()) {
								echo ' <small>'.$s->getHtmlLinkToWeb ().'</small>';
							}
							echo '</h2>';
							echo '<div class="list-group-item-text">';
							if ($s->getCity() && empty($_SESSION ['society_search']['criteria']['city'])) {
								echo ' <p>'.$s->getCity ().'</p>';
							}
							if ($s->getDescription ())
								echo '<p>'.$s->getDescription ().'</p>';
							if ($s->getCreationDate ()) {
								echo '<p>Enregistrée le '.$s->getCreationDateFr().'</p>';
							}
							echo '</div>';
							echo '</div>';
							echo '</div>';
						}
						?>
		    		</div>
					<button type="button" class="btn btn-default" onclick="check('society_id[]')">tout cocher</button> /
					<button type="button" class="btn btn-default" onclick="uncheck('society_id[]')">tout décocher</button>
					<label for="task_id">Pour la sélection :</label>
		            <!--
					<select id="task_id" name="task_id">
						<option value="0">- choisir -</option>
						<option value="1"<?php if (isset($params['task_id']) && $params['task_id']==1) echo 'selected="selected"' ?>>fusionner</option>
						<option value="2"<?php if (isset($params['task_id']) && $params['task_id']==2) echo 'selected="selected"' ?>>supprimer</option>
					</select>
					-->
					<button name="task_submission" type="submit" value="1" class="btn btn-default">fusionner</button> 
		    	</form>
		    	<div>
		    		<?php
		    		if ($pages_nb > 1) {
		    			$params = array ();
		    			echo ToolBox::getHtmlPagesNav ( $_SESSION ['society_search'] ['page_index'], $pages_nb, $params, 'society_search_page_index' );
		    		}
		    		?>
		    	</div>
		    </section>
	    </div>
   		<div class="col-md-5">
			<section>
				<?php
				$criteria = array();
				if (isset($_SESSION ['society_search']['criteria']['name'])) {
					$criteria['society_name_like_pattern'] = $_SESSION ['society_search']['criteria']['name'];
				}				
				if (isset($_SESSION ['society_search']['criteria']['industry_id'])) {
					$criteria['industry_id'] = $_SESSION ['society_search']['criteria']['industry_id'];
				}
				if (isset($_SESSION ['society_search']['criteria']['city'])) {
					$criteria['society_city'] = $_SESSION ['society_search']['criteria']['city'];
				}				
				$memberships = $system->getMemberships($criteria, 'Last updated first', 0, 8);
				if ($memberships) {
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
				}
				?>
			</section>
		</div>	    
    </div>
</div>
<script type="text/javascript">
	$(document).ready(function(){
		$('#s_city_i').autocomplete({
			minLength: 1,
	   		source: function( request, response ) {
	            $.ajax({
					method:'GET',
	                url:'society_cities.json.php',
	                dataType: 'json',
	                data:{
	                    'query': request.term
	                 },
	                 dataFilter: function(data,type){
	                     return JSON.stringify(JSON.parse(data).cities);
	                 },
	                 success : function(data, textStatus, jqXHR){
						response(data);
	                 }
	         	})
	   		},
	        focus: function( event, ui ) {
				$('#s_city_i').val( ui.item.value );
	        	return false;
	        },
	        select: function( event, ui ) {
				$('#s_city_i').val( ui.item.value );
	        	return false;
	        }
	   	}).autocomplete( "instance" )._renderItem = function( ul, item ) {
		    return $( "<li>" ).append(item.value + ' <small>(' + item.count +')</small>').appendTo( ul );
	    };
	    
	    var $grid = $('.il').masonry({
	      itemSelector: '.thumbnail',
	      columnWidth: '.thumbnail',
	      gutter: '.masonryGutterSizer'
	    });
			
		$grid.imagesLoaded().progress(
			function() {
				$grid.masonry('layout');	
			}
		);
	})
</script>
</body>
</html>