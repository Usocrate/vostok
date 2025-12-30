<?php
require_once 'config/boot.php';
require_once 'classes/System.php';
$system = new System( './config/host.json' );
$systemIdInSession = $system->getAppliName();

session_start ();

if (empty ( $_SESSION[$systemIdInSession]['user_id'] )) {
	header ( 'Location:login.php' );
	exit ();
} else {
	$user = new User ( $_SESSION[$systemIdInSession]['user_id'] );
	$user->feed ();
}

// nombre de societies à afficher par page
$page_items_nb = 14;

// si ordre d'effectuer une nouvelle recherche,
// les données propres à la sélection de societies courante sont réinitialisées
if (isset ( $_REQUEST ['society_newsearch'] ) || ! isset( $_SESSION[$systemIdInSession]['society_search'] )) {
	$_SESSION[$systemIdInSession]['society_search'] = array ();
	$_SESSION[$systemIdInSession]['society_search']['criteria'] = array ();

	if (isset ( $_REQUEST ['society_name'] ) && ! empty($_REQUEST ['society_name']) ) {
		$_SESSION[$systemIdInSession]['society_search']['criteria']['name'] = $_REQUEST ['society_name'];
	}
	
	if (isset ( $_REQUEST ['industry_id'] ) && ! empty ( $_REQUEST ['industry_id']) ) {
		$_SESSION[$systemIdInSession]['society_search']['criteria']['industry_id'] = $_REQUEST ['industry_id'];
	}
	
	if (isset ( $_REQUEST ['society_city'] ) && ! empty($_REQUEST ['society_city']) ) {
		$_SESSION[$systemIdInSession]['society_search']['criteria']['city'] = $_REQUEST ['society_city'];
	}
	
	$_SESSION[$systemIdInSession]['society_search']['page_index'] = 1;
	$_SESSION[$systemIdInSession]['society_search']['sort'] = 'Last created first';
}

// nb de comptes correspondant aux critères
$items_nb = $system->getSocietiesNb ( $_SESSION[$systemIdInSession]['society_search']['criteria'] );
$pages_nb = ceil ( $items_nb / $page_items_nb );

// changement de page
if (isset ( $_REQUEST ['society_search_page_index'] )) {
	$_SESSION[$systemIdInSession]['society_search'] ['page_index'] = $_REQUEST ['society_search_page_index'];
}

// sélection de sociétés correspondant aux critères (dont le nombre dépend de la variable $page_items_nb)
$page_debut = ($_SESSION[$systemIdInSession]['society_search'] ['page_index'] - 1) * $page_items_nb;
$societies = $system->getSocieties( $_SESSION[$systemIdInSession]['society_search']['criteria'], $_SESSION[$systemIdInSession]['society_search']['sort'], $page_debut, $page_items_nb );

// si une seule société redirection vers fiche individuelle.
if (count ( $societies ) == 1) {
    // on considère que la recherche est arrivée à son terme
    unset($_SESSION[$systemIdInSession]['society_search']);
	header ( 'Location:society.php?society_id=' . $societies [0]->getId () );
	exit ();
}

// redirection vers création de fiche société.
if (count ( $societies ) == 0 && isset($_SESSION[$systemIdInSession]['society_search']['criteria']['name'])) {
    $name = $_SESSION[$systemIdInSession]['society_search']['criteria']['name'];
    // on considère que la recherche est arrivée à son terme
    unset($_SESSION[$systemIdInSession]['society_search']);
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
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
    <link type="text/css" rel="stylesheet" href="<?php echo PHOSPHOR_URI ?>"></link>    
    <link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>theme.css"></link>
    <?php echo $system->writeHtmlHeadTagsForFavicon(); ?>
	<script src="<?php echo JQUERY_URI; ?>"></script>
	<script src="js/masonry.pkgd.min.js"></script>
	<script src="js/imagesloaded.pkgd.min.js"></script>
	<script src="vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>
	<script src="js/society-city-autocomplete.js"></script>	
</head>
<body id="societiesListDoc">
<?php include 'navbar.inc.php'; ?>
<div class="container-fluid">
	<h1><?php echo ToolBox::toHtml($doc_title); ?> <small><a href="society_edit.php"><i class="ph-bold ph-plus"></i></a></small></h1>
	<section>
	   	<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" class="form-inline">
			<div class="form-group m-2">
	    		<label for="s_name_i" class="mr-2">Nom</label>
	    		<input id="s_name_i" name="society_name" type="text" value="<?php if (isset($_SESSION[$systemIdInSession]['society_search']['criteria']['name'])) echo $_SESSION[$systemIdInSession]['society_search']['criteria']['name']; ?>" class="form-control" /> 
			</div>
			<div class="form-group m-2">
	    		<label for="s_industry_i" class="mr-2">Activité</label>
	    		<select id="s_industry_i" name="industry_id" class="form-control">
	    			<option value="">-- choisir --</option>
	    			<?php echo isset($_SESSION[$systemIdInSession]['society_search']['criteria']['industry_id']) ? $system->getIndustryOptionsTags($_SESSION[$systemIdInSession]['society_search']['criteria']['industry_id']) : $system->getIndustryOptionsTags(); ?>
	    		</select>
			</div>
			<div class="form-group m-2">
	    		<label for="s_city_i" class="mr-2">Ville</label>
	    		<input id="s_city_i" name="society_city" is="society-city-autocomplete" value="<?php if (isset($_SESSION[$systemIdInSession]['society_search']['criteria']['city'])) echo $_SESSION[$systemIdInSession]['society_search']['criteria']['city']; ?>" class="form-control"></input>
			</div>
	   		<button type="submit" name="society_newsearch" value="filtrer" class="btn btn-secondary m-2">Filtrer</button>
	   		<?php if( count($_SESSION[$systemIdInSession]['society_search']['criteria']) > 0) echo ' <a href="societies.php?society_newsearch=1">Toutes les sociétés</a>'  ?>
		</form>
   	</section>
   	<div class="row">
		<div class="col-md-7 col-lg-9">
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
							if ($s->getCity() && empty($_SESSION[$systemIdInSession]['society_search']['criteria']['city'])) {
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
		    		<div class="mt-4 mb-4">
						<button type="button" class="btn btn-outline-secondary" onclick="check('society_id[]')">Tout cocher</button> /
						<button type="button" class="btn btn-outline-secondary" onclick="uncheck('society_id[]')">Tout décocher</button>
						<label for="task_id">Pour la sélection :</label>
						<button name="task_submission" type="submit" value="1" class="btn btn-secondary">Fusionner</button> 
					</div>
		    	</form>
		    	<div class="mt-4 mb-4">
		    		<?php
		    		if ($pages_nb > 1) {
		    			$params = array ();
		    			echo ToolBox::getHtmlPagesNav ( $_SESSION[$systemIdInSession]['society_search'] ['page_index'], $pages_nb, $params, 'society_search_page_index' );
		    		}
		    		?>
		    	</div>
		    </section>
	    </div>
   		<div class="col-md-5 col-lg-3">
			<section>
				<?php
				$criteria = array();
				if (isset($_SESSION[$systemIdInSession]['society_search']['criteria']['name'])) {
					$criteria['society_name_like_pattern'] = $_SESSION[$systemIdInSession]['society_search']['criteria']['name'];
				}				
				if (isset($_SESSION[$systemIdInSession]['society_search']['criteria']['industry_id'])) {
					$criteria['industry_id'] = $_SESSION[$systemIdInSession]['society_search']['criteria']['industry_id'];
				}
				if (isset($_SESSION[$systemIdInSession]['society_search']['criteria']['city'])) {
					$criteria['society_city'] = $_SESSION[$systemIdInSession]['society_search']['criteria']['city'];
				}				
				$memberships = $system->getMemberships($criteria, 'Last updated first', 0, 8);
				if ($memberships) {
			  		echo '<div class="il">';
			  		echo '<div class="masonryGutterSizer"></div>';
			  		foreach ($memberships as $ms) {
						$i = $ms->getIndividual();
						$s = $ms->getSociety();
						echo '<div class="card">';
						if ($i->getPhotoUrl()) {
							echo '<a href="individual.php?individual_id='.$i->getId().'" class="implicit card-img-top-wrapper">';
							echo '<img src="' . $i->getPhotoUrl () . '"  class="card-img-top" />';
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
								}
								if (count($position_elt)>0) {
									echo '<p>'.implode(' / ', $position_elt).'</p>';
								}
		
								if ( $ms->getPeriod() ) $smallTag_elt[] = '<p><small>'.$ms->getPeriod().'</small></p>';
							echo '</div>';
							
							echo '<a href="membership_edit.php?membership_id='.$ms->getId().'" class="btn btn-sm btn-outline-secondary">Editer</a>';
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
<script>
	const apiUrl = '<?php echo $system->getApiUrl() ?>';
	
	document.addEventListener("DOMContentLoaded", function() {
		customElements.define("society-city-autocomplete", SocietyCityAutocomplete, { extends: "input" });
		
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