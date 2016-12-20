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
ToolBox::getDBAccess();

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
?>
<!doctype html>
<html lang="fr">
<head>
    <title>Un des comptes (sa fiche détaillées)</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_URI ?>" type="text/css" />
    <link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_THEME_URI ?>" type="text/css" />
    <link rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>main.css" type="text/css">
	<script type="text/javascript" src="<?php echo JQUERY_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo JQUERY_UI_URI; ?>"></script>
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
                    $geo_elt[] = '<a href="societies_list.php?society_newsearch=1&society_city='.$society->getCity().'">'.ToolBox::toHtml($society->getCity()).'</a>';
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
            }
        ?>
	</section>
	
	<div class="row">
		<div class="col-md-8">
			<section>
				<h2>Les pistes<small><a href="lead_edit.php?society_id=<?php echo $society->getId() ?>"> <span class="glyphicon glyphicon-plus"></span></a></small></h2>
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
			</section>

			<section>
				<h2>Évènements<small><a href="society_event_edit.php?society_id=<?php echo $society->getId() ?>"> <span class="glyphicon glyphicon-plus"></span></a></small></h2>
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
			</section>
		</div>
		<div class="col-md-4">
			<section>
				<h2>Les gens<small><a href="membership_edit.php?society_id=<?php echo $society->getId() ?>"> <span class="glyphicon glyphicon-plus"></span></a></small></h2>
				<ul class="list-group">
					<?php
					foreach ($memberships as $ms) {
						$i = $ms->getIndividual();
						echo '<li class="list-group-item">';
						echo '<h3>';
						echo '<a href="individual.php?individual_id='.$i->getId().'">'.ToolBox::toHtml($i->getWholeName()).'</a>';
						$smallTag_elt = array();
						if ($ms->getDepartment()) $smallTag_elt[] = ToolBox::toHtml($ms->getDepartment());
						if ($ms->getTitle()) $smallTag_elt[] = ToolBox::toHtml($ms->getTitle());
						if (count($smallTag_elt)>0) {
							echo '<div><small>'.implode(' / ', $smallTag_elt).'</small></div>';
						}
						echo '</h3>';
						echo '<div><a href="membership_edit.php?membership_id='.$ms->getId().'" title="éditer la participation de '.ToolBox::toHtml($i->getWholeName()).'"><span class="glyphicon glyphicon-edit"></span> édition</a></div>';
						echo '</li>';
					}
					?>
				</ul>
			</section>

			<section>
				<h2>Sociétés liées<small><a href="relationship_edit.php?item0_class=Society&amp;item0_id=<?php echo $society->getId() ?>"> <span class="glyphicon glyphicon-plus"></span></a></a></small></h2>
				<ul class="list-group">
					<?php
					foreach ($relatedSocieties as $item) {
						$society = $item[0];
						$relationship_id = $item[1];
						$role = $item[2];
						$description = $item[3];
						echo '<li class="list-group-item">';
						echo '<h3>';
						echo '<a href="society.php?society_id='.$society->getId().'">'.$society->getNameForHtmlDisplay().'</a>';
						echo ' <small>(';
						echo '<a href="relationship_edit.php?relationship_id='.$relationship_id.'">';
						echo empty($role) ? '?' : ToolBox::toHtml($role);
						echo '</a>';
						echo ')</small>';
						echo '</h3>';
						if (!empty($description)) {
							echo '<p>';
							echo ToolBox::toHtml($description);
							echo '</p>';
						}
						echo '</li>';
					}
					?>
				</ul>
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
				$('#society_city_i').val( ui.item.value );
	        	return false;
	        },
	        select: function( event, ui ) {
				$('#society_city_i').val( ui.item.value );
	        	return false;
	        }
	   	}).autocomplete( "instance" )._renderItem = function( ul, item ) {
		    return $( "<li>" ).append(item.value + ' <small>(' + item.count +')</small>').appendTo( ul );
	    };
	})
</script>
</body>
</html>