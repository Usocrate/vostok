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
	
	$_SESSION ['society_search'] ['page_index'] = 1;
	$_SESSION ['society_search'] ['sort_key'] = 'society_name';
	$_SESSION ['society_search'] ['sort_order'] = 'ASC';
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
$page_rowset = $system->getSocietiesRowset( $_SESSION ['society_search']['criteria'], $_SESSION ['society_search'] ['sort_key'], $_SESSION ['society_search'] ['sort_order'], $page_debut, $page_items_nb );

// la sélection de sociétés
$societies = array ();
foreach ($page_rowset as $row) {
	$s = new Society ();
	$s->feed ( $row );
	$societies [] = $s;
}

// si une seule société redirection vers fiche individuelle.
if (count ( $societies ) == 1) {
	header ( 'Location:society.php?society_id=' . $societies [0]->getId () );
	exit ();
}

// redirection vers création de fiche société.
if (count ( $societies ) == 0 && isset($_SESSION ['society_search']['criteria']['name'])) {
	header ( 'Location:society_edit.php?society_name=' . $_SESSION ['society_search']['criteria']['name'] );
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
	<script type="text/javascript" src="<?php echo BOOTSTRAP_JS_URI; ?>"></script>
</head>
<body id="societiesListDoc">
<?php include 'navbar.inc.php'; ?>
<div class="container-fluid">
	<h1><?php echo ToolBox::toHtml($doc_title); ?> <small><a href="society_edit.php"><span class="glyphicon glyphicon-plus"></span></a></small></h1>
	<section>	
	   	<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" class="form-inline">
    		<div class="form-group">
        		<label for="s_name_i">nom</label>
        		<input id="s_name_i" name="society_name" type="text" value="<?php if (isset($_SESSION ['society_search']['criteria']['name'])) echo $_SESSION ['society_search']['criteria']['name']; ?>" class="form-control" /> 
    		</div>
    		<div class="form-group">
        		<label for="s_industry_i">activité</label>
        		<select id="s_industry_i" name="industry_id" class="form-control">
        			<option value="">-- choisir --</option>
        			<?php echo isset($_SESSION['society_search']['criteria']['industry_id']) ? $system->getIndustryOptionsTags($_SESSION['society_search']['criteria']['industry_id']) : $system->getIndustryOptionsTags(); ?>
        		</select>
    		</div>
    		<div class="form-group">
        		<label for="s_city_i">ville</label>
        		<input id="s_city_i" name="society_city" value="<?php if (isset($_SESSION ['society_search']['criteria']['city'])) echo $_SESSION ['society_search']['criteria']['city']; ?>" class="form-control"></input>
    		</div>
	   		<button type="submit" name="society_newsearch" value="filtrer" class="btn btn-default">Filtrer</button>
	   		<?php if( count($_SESSION['society_search']['criteria']) > 0) echo ' <a href="societies_list.php?society_newsearch=1">Toutes les sociétés</a>'  ?>
    	</form>
   </section>
   <section>
       	<form method="post" action="societies_merge.php">
    		<table class="table">
    			<thead>
    				<tr>
    					<th></th>
    					<th>Nom</th>
    					<th>Description</th>
    					<th>Ville</th>
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
    					echo '<td>';
    					echo $s->getCity () ? $s->getCity() : '<small>?</small>';
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
    </section>
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
	})
</script>
</body>
</html>