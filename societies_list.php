<?php
require_once 'config/main.inc.php';

session_start ();
ToolBox::getDBAccess ();

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
if (isset ( $_REQUEST ['society_newsearch'] ) || empty ( $_SESSION ['society_search'] )) {
	$_SESSION ['society_search'] = array ();
	if (isset ( $_REQUEST ['society_name'] )) {
		$_SESSION ['society_search'] ['name'] = $_REQUEST ['society_name'];
	}
	if (isset ( $_REQUEST ['industry_id'] ) && strcmp ( $_REQUEST ['industry_id'], '-1' ) != 0) {
		$_SESSION ['society_search'] ['industry_id'] = $_REQUEST ['industry_id'];
	}
	if (isset ( $_REQUEST ['society_city'] ) && strcmp ( $_REQUEST ['society_city'], '-1' ) != 0) {
		$_SESSION ['society_search'] ['city'] = $_REQUEST ['society_city'];
	}
	if (isset ( $_REQUEST ['society_postalcode'] )) {
		$_SESSION ['society_search'] ['postalcode'] = $_REQUEST ['society_postalcode'];
	}
	$_SESSION ['society_search'] ['page_index'] = 1;
	$_SESSION ['society_search'] ['sort_key'] = 'society_name';
	$_SESSION ['society_search'] ['sort_order'] = 'ASC';
}
if (! isset ( $_SESSION ['society_search'] ))
	$_SESSION ['society_search'] = array ();

/**
 * critères de filtrage.
 */
$criterias = array ();

// idée : un objet Society est utilisé comme pattern de recherche.
// mise en place inachevée ...
$search_pattern = new Society ();

if (! empty ( $_SESSION ['society_search'] ['name'] )) {
	$search_pattern->setName ( $_SESSION ['society_search'] ['name'] );
	$criterias [] = 'society_name LIKE "' . $search_pattern->getName () . '%"';
	// $criterias[] = 'society_name LIKE "'.$_SESSION['society_search']['name'].'%"';
}
//
// critère 'activité'
//
if (isset ( $_SESSION ['society_search'] ['industry_id'] )) {
	if (empty ( $_SESSION ['society_search'] ['industry_id'] )) {
		$criterias [] = 'industry_id IS NULL';
	} else {
		$criterias [] = 'industry_id = "' . $_SESSION ['society_search'] ['industry_id'] . '"';
	}
}

// critère 'ville'
if (isset ( $_SESSION ['society_search'] ['city'] )) {
	$search_pattern->setCity ( $_SESSION ['society_search'] ['city'] );
	if (empty ( $_SESSION ['society_search'] ['city'] )) {
		$criterias [] = '(society_city = "" OR society_city IS NULL)';
		// $criterias[] = 'society_city IS NULL';
	} else {
		$criterias [] = 'society_city = "' . $search_pattern->getCity () . '"';
		// $criterias[] = 'society_city = "'.$_SESSION['society_search']['city'].'"';
	}
}

// critère 'code postal'
if (isset ( $_SESSION ['society_search'] ['postalcode'] )) {
	$search_pattern->setPostalCode ( $_SESSION ['society_search'] ['postalcode'] );
	if (empty ( $_SESSION ['society_search'] ['postalcode'] )) {
		$criterias [] = '(society_postalcode = "" OR society_postalcode IS NULL)';
	} else {
		$criterias [] = 'society_postalcode LIKE "' . $search_pattern->getPostalCode () . '%"';
	}
}

// nb de comptes correspondant aux critères
$items_nb = $system->getSocietiesNb ( $criterias );
$pages_nb = ceil ( $items_nb / $page_items_nb );

// changement de page
if (isset ( $_REQUEST ['society_search_page_index'] )) {
	$_SESSION ['society_search'] ['page_index'] = $_REQUEST ['society_search_page_index'];
}

// sélection de sociétés correspondant aux critères (dont le nombre dépend de la variable $page_items_nb)
$page_debut = ($_SESSION ['society_search'] ['page_index'] - 1) * $page_items_nb;
$page_rowset = $system->getSocietiesRowset ( $criterias, $_SESSION ['society_search'] ['sort_key'], $_SESSION ['society_search'] ['sort_order'], $page_debut, $page_items_nb );

// la sélection de sociétés
$societies = array ();
while ( $row = mysql_fetch_assoc ( $page_rowset ) ) {
	$s = new Society ();
	$s->feed ( $row );
	$societies [] = $s;
}

// si une seule société redirection vers fiche individuelle.
if (count ( $societies ) == 1) {
	header ( 'Location:society.php?society_id=' . $societies [0]->getId () );
	exit ();
}
$doc_title = 'Les sociétés qui m\'intéressent';
?>
<!doctype html>
<html lang="fr">
<head>
    <title><?php echo APPLI_NAME ?>: Liste des Sociétés</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_URI ?>" type="text/css" />
    <link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_THEME_URI ?>" type="text/css" />
    <link rel="stylesheet" href="<?php echo SKIN_URL ?>main.css" type="text/css">
    <script type="application/javascript" src="js/controls.js"></script>
	<script type="text/javascript" src="<?php echo JQUERY_URI; ?>"></script><script type="text/javascript" src="<?php echo BOOTSTRAP_JS_URI; ?>"></script>
</head>
<body id="societiesListDoc">
<?php include 'navbar.inc.php'; ?>
<div class="container-fluid">
	<h1><?php echo ToolBox::toHtml($doc_title); ?></h1>
	
	   	<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" class="form-inline">
    		<div class="form-group">
        		<label for="s_name_i">nom</label>
        		<input id="s_name_i" name="society_name" type="text" value="<?php echo $search_pattern->getName(); ?>" class="form-control" /> 
    		</div>
    		
    		<div class="form-group">
        		<label for="s_industry_i">activité</label>
        		<select id="s_industry_i" name="industry_id" class="form-control">
        			<option id="s_industry_i" value="">-- choisir --</option>
        			<?php echo isset($_SESSION['society_search']['industry_id']) ? $system->getIndustryOptionsTags($_SESSION['society_search']['industry_id']) : $system->getIndustryOptionsTags(); ?>
        		</select>
    		</div>
    		
    		<div class="form-group">
        		<label for="s_city_i">ville</label>
        		<select id="s_city_i" name="society_city" class="form-control">
        			<?php echo $search_pattern->getCityOptionsTags(); ?>
        		</select>
    		</div>
    		
    		<div class="form-group">
        		<label for="s_pc_i">code postal</label>
        		<input id="s_pc_i" name="society_postalcode" type="text" size="5" value="<?php echo $search_pattern->getPostalCode(); ?>" class="form-control" />
    		</div>
    		
    		<button type="submit" name="society_newsearch" value="filtrer" class="btn btn-default">Filtrer</button>
    	</form>
   
       	<form method="post" action="societies_merge.php">
    		<table class="table">
    			<thead>
    				<tr>
    					<th></th>
    					<th>Nom</th>
    					<th>Description</th>
    					<th>Enregistrement</th>
    					<th>Web</th>
    				</tr>
    			</thead>
    			<tfoot>
    				<tr>
    					<td colspan="5" style="text-align: left">
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
    					</td>
    				</tr>
    			</tfoot>
    			<tbody>
    				<?php
    				foreach ( $societies as $s ) {
    					echo '<tr>';
    					echo '<td>';
    					echo '<input name="society_id[]" type="checkbox" value="' . $s->getId () . '" />';
    					echo '</td>';
    					echo '<td>';
    					echo '<strong><a href="society.php?society_id=' . $s->getId () . '">' . $s->getNameForHtmlDisplay () . '</a></strong>';
    					echo '</td>';
    					echo '<td>';
    					echo $s->getDescription () ? $s->getDescription () : '<small>aucune</small>';
    					echo '</td>';
    					echo '<td style="text-align:center">';
    					echo $s->getCreationDate () ? $s->getCreationDateFr () : '<small>à déterminer</small>';
    					echo '</td>';
    					echo '<td>';
    					echo $s->getWebHtmlLink ();
    					echo '</td>';
    					echo '</tr>';
    				}
    				?>
    			</tbody>
    		</table>
    	</form>
    	<div>
    		<?php
    		if ($pages_nb > 1) {
    			$params = array ();
    			echo ToolBox::getHtmlPagesNav ( $_SESSION ['society_search'] ['page_index'], $pages_nb, $params, 'society_search_page_index' );
    		}
    		?>
    	</div>
</div>	
</body>
</html>