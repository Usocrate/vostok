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

/**
 * paramètres de pagination
 **/
$page_items_nb = 20;

// si ordre d'effectuer une nouvelle recherche,
// les données propres à la sélection de leads courante sont réinitialisées
if (isset ($_REQUEST['lead_newsearch_order']) || empty ($_SESSION['lead_search'])) {
	ToolBox::formatUserPost($_REQUEST);
	$_SESSION['lead_search'] = array ();
	if (isset ($_REQUEST['lead_type']) && strcmp($_REQUEST['lead_type'],'-1')!=0) {
		$_SESSION['lead_search']['type'] = $_REQUEST['lead_type'];
	}
	if (isset ($_REQUEST['lead_source']) && strcmp($_REQUEST['lead_source'],'-1')!=0) {
		$_SESSION['lead_search']['source'] = $_REQUEST['lead_source'];
	}
	if (isset ($_REQUEST['lead_status']) && strcmp($_REQUEST['lead_status'],'-1')!=0) {
		$_SESSION['lead_search']['status'] = $_REQUEST['lead_status'];
	}
	$_SESSION['lead_search']['page_index'] = 1;
	$_SESSION['lead_search']['sort'] = 'Last created first';
}
if (!isset ($_SESSION['lead_search'])) {
	$_SESSION['lead_search'] = array ();
}
// critères de filtrage
$criteria = array ();
if (isset ($_SESSION['lead_search']['type'])) {
	if (empty($_SESSION['lead_search']['type'])) {
		$criteria['type'] = '';
	} else {
		$criteria['type'] = $_SESSION['lead_search']['type'];
	}
}
if (isset ($_SESSION['lead_search']['source'])) {
	if (empty($_SESSION['lead_search']['source'])) {
		$criteria['source'] = '';
	} else {
		$criteria['source'] = $_SESSION['lead_search']['source'];
	}
}
if (isset ($_SESSION['lead_search']['status'])) {
	$criteria['status'] = $_SESSION['lead_search']['status'];
}

// nb de pistes correspondant aux critères
$leads_nb = $system->getLeadsNb($criteria);

// nb de pages nécessaire à l'affichage des pistes
$pages_nb = ceil($leads_nb / $page_items_nb);

//	changement de page
if (isset ($_REQUEST['lead_search_page_index']))
	$_SESSION['lead_search']['page_index'] = $_REQUEST['lead_search_page_index'];

//	sélection de leads correspondant aux critères (dont le nombre dépend de la variable $page_items_nb)
$page_debut = ($_SESSION['lead_search']['page_index'] - 1) * $page_items_nb;
$page_rowset = $system->getLeadsRowset($criteria, $_SESSION['lead_search']['sort'], $page_items_nb, $page_debut);

//	la sélection de pistes
$leads = array ();
foreach ($page_rowset as $row) {
	$l = new Lead();
	$l->feed($row);
	$l->getIndividual()->feed($row);
	$l->getSociety()->feed($row);
	$leads[] = $l;
}

$doc_title = 'Les pistes ('.$leads_nb.')';
?>
<!doctype html>
<html lang="fr">
<head>
	<title><?php echo ToolBox::toHtml($system->getAppliName()).' : '.ToolBox::toHtml($doc_title) ?></title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<link type="text/css" rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_URI ?>" integrity="<?php echo BOOTSTRAP_CSS_URI_INTEGRITY ?>" crossorigin="anonymous"></link>
	<link type="text/css" rel="stylesheet" href="<?php echo FONTAWESOME_CSS_URI ?>" integrity="<?php echo FONTAWESOME_CSS_URI_INTEGRITY ?>" crossorigin="anonymous" />	
	<link type="text/css" rel="stylesheet" href="<?php echo JQUERY_UI_CSS_THEME_URI ?>"></link>
	<link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>main.css"></link>
	<?php echo $system->writeHtmlHeadTagsForFavicon(); ?>
	<script type="text/javascript" src="<?php echo JQUERY_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo JQUERY_UI_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo BOOTSTRAP_JS_URI ?>" integrity="<?php echo BOOTSTRAP_JS_URI_INTEGRITY ?>" crossorigin="anonymous"></script>
</head>
<body>
<?php include 'navbar.inc.php'; ?>
<div class="container-fluid">
	<h1>Les pistes <span class="badge badge-info"><?php echo $leads_nb ?></span> <small><a href="lead_edit.php"><i class="fas fa-plus"></i></a></small></h1>
	<section>
    	<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" class="form-inline">
    		<div class="form-group m-2">
        		<label for="lead_type_i" class="mr-2">type</label>
        		<select id="lead_type_i" name="lead_type" class="form-control">
        			<option value="-1">-- tous --</option>
        			<?php echo isset($_SESSION['lead_search']['type']) ? Lead::getKnownTypesAsOptionsTags($_SESSION['lead_search']['type']) : Lead::getKnownTypesAsOptionsTags() ?>
        		</select>
    		</div>
    		<div class="form-group m-2">
        		<label for="lead_source_i" class="mr-2">origine</label>
        		<select id="lead_source_i" name="lead_source" class="form-control">
        			<option value="-1">-- toutes --</option>
        			<?php echo isset($_SESSION['lead_search']['source']) ? Lead::getKnownSourcesAsOptionsTags($_SESSION['lead_search']['source']) : Lead::getKnownSourcesAsOptionsTags() ?>
        		</select>
        	</div>
        	<div class="form-group m-2">
        		<label for="lead_status_i" class="mr-2">état</label>
        		<select id="lead_status_i" name="lead_status" class="form-control">
        			<option value="-1">-- tous --</option>
        			<?php
        			$searchPattern = new Lead();
        			if (isset($_SESSION['lead_search']['status'])) {
        				$searchPattern->setStatus($_SESSION['lead_search']['status']);
        			}
        			echo $searchPattern->getStatusOptionsTags();
        			?>
        		</select>
    		</div>
    		<button type="submit" name="lead_newsearch_order" value="1" class="btn btn-default m-2">Filtrer</button>
    	</form>
	</section>
	<section>
		<?php
			if (count($leads) > 0) {
				echo '<ul class="list-group">';
				foreach ($leads as $l) {
					echo '<li class="list-group-item">';
					//title
					if ($l->getShortDescription()) {
					    echo '<h2>'.$l->getShortDescription().'<small><a href="lead_edit.php?lead_id=' . $l->getId() . '"> <i class="fas fa-edit"></i></a></small></h2>';
					}
					//	society
					$href = 'society.php?society_id=' . $l->society->getId();
					echo '<a href="' . $href . '">';
					echo $l->society->getNameForHtmlDisplay();
					echo '</a>';
					if ($l->society->getUrl()) {
						echo ' '.$l->society->getHtmlLinkToWeb();
					}
					echo '<br/>';
		
					// individual
					if ($l->individual->getId()) {
					   echo '<a href="individual.php?individual_id=' . $l->individual->getId(). '">'.$l->individual->getWholeName().'</a><br />';
					}
					//	baseline
					$baseline_elt = array ();
					if ($l->getType()) {
						$baseline_elt[] = $l->getType();
					}
					if ($l->getCreationDate()) {
						$baseline_elt[] = $l->getCreationDateFr();
					}
					if (count($baseline_elt) > 0) {
						echo '<div><small>' . implode(' - ', $baseline_elt) . '</small></div>';
					}
					echo '</li>';
				}
				echo '</ul>';
			}
		?>
	<div>
	<?php
		if ($pages_nb > 1) {
			$params = array ();
			echo ToolBox::getHtmlPagesNav($_SESSION['lead_search']['page_index'], $pages_nb, $params, 'lead_search_page_index');
		}
	?>
	</div>
	</section>
</div>
</body>
</html>