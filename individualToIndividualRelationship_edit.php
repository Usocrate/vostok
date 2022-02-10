<?php
function __autoload($class_name) {
	$path = 'classes/';
	if (is_file ( $path . $class_name . '.class.php' )) {
		include_once $path . $class_name . '.class.php';
	} elseif ($path . $class_name . '.interface.php') {
		include_once $path . $class_name . '.interface.php';
	}
}
$system = new System( 'config/host.json' );

require_once 'config/boot.php';

session_start ();

if (empty ( $_SESSION ['user_id'] )) {
	header ( 'Location:login.php' );
	exit ();
} else {
	$user = new User ( $_SESSION ['user_id'] );
	$user->feed ();
}

$relationship = new Relationship ();

// Formatage des données saisies par l'utilisateur
if (isset ( $_POST )) {
	ToolBox::formatUserPost ( $_POST );
}

if (! empty ( $_REQUEST ['relationship_id'] )) {
	// la participation à traiter est identifiée
	$relationship->setId ( $_REQUEST ['relationship_id'] );
	$relationship->feed ();
		
	// récupération des données en base
	$item0 = $relationship->getItem ( 0 );
	if (is_object ( $item0 )) {
		$item0->feed ();
	}
	$item1 = $relationship->getItem ( 1 );
	if (is_object ( $item1 )) {
		$item1->feed ();
	}
} else {
	// la relation est nouvelle
	// on récupère les identifiants, éventuellement fournis, des individus impliqués
	if (isset ( $_REQUEST ['item0_id'] )) {
		$item0 = new Individual($_REQUEST['item0_id']);
		$item0->feed();
		$relationship->setItem ($item0, 0);
	}
	if (isset ( $_REQUEST ['item1_id'] )) {
		$item1 = new Individual($_REQUEST['item1_id'] );
		$item1->feed();
		$relationship->setItem($item1, 1);
	}
}

if (isset ( $_POST ['relationship_submission'] )) {
	// enregistrement des données de la participation
	$relationship->feed ( $_POST );
	if ( empty($item0) && !empty($_POST['item0_firstname']) && !empty($_POST['item0_lastname']) ) {
		$item0 = new Individual ();
		$item0->setFirstName ( $_POST ['item0_firstname'] );
		$item0->setLastName ( $_POST ['item0_lastname'] );
		if (! $item0->identifyFromName ()) {
			$item0->toDB ();
		}
		$relationship->setItem($item0,0);
	}
	if (empty($item1) && !empty($_POST['item1_firstname']) && !empty($_POST['item1_lastname'])) {
		$item1 = new Individual();
		$item1->setFirstName($_POST['item1_firstname']);
		$item1->setLastName($_POST['item1_lastname']);
		if(!$item1->identifyFromName()) {
			$item1->toDB();
		}
		$relationship->setItem($item1,1);
	}
	if ($relationship->toDB ()) {
		header ( 'location:individual.php?individual_id=' . $item0->getId () );
		exit ();
	}
}
if (isset ( $item0 ) && isset ( $item1 )) {
	$h1_content = 'Une relation entre ' . $item1->getHtmlLinkToIndividual() . ' et ' . $item0->getHtmlLinkToIndividual();
} else {
	$h1_content = isset ( $item0 ) && $item0->getId() ? 'Une relation de ' . $item0->getHtmlLinkToIndividual() : 'Une relation individuelle';
}
?>
<!doctype html>
<html lang="fr">
<head>
    <title><?php echo strip_tags($h1_content) ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
    <link type="text/css" rel="stylesheet" href="<?php echo FONTAWESOME_CSS_URI ?>" integrity="<?php echo FONTAWESOME_CSS_URI_INTEGRITY ?>" crossorigin="anonymous" />    
    <link type="text/css" rel="stylesheet" href="<?php echo JQUERY_UI_CSS_THEME_URI ?>"></link>
    <link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>theme.css"></link>
    <?php echo $system->writeHtmlHeadTagsForFavicon(); ?>
    <script src="js/controls.js"></script>
	<script src="<?php echo JQUERY_URI; ?>"></script>
	<script src="<?php echo JQUERY_UI_URI; ?>"></script>
	<script src="<?php echo BOOTSTRAP_JS_URI ?>" integrity="<?php echo BOOTSTRAP_JS_URI_INTEGRITY ?>" crossorigin="anonymous"></script>
<body>
<?php include 'navbar.inc.php'; ?>
<div class="container-fluid">
	<h1 class="bd-title"><?php echo $h1_content ?></h1>
	<section>
		<form id="relationship_form" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
			<input name="item0_class" type="hidden" value="Individual" />
			<input name="item1_class" type="hidden" value="Individual" />

			<?php
			if ($relationship->getId ())
				echo '<input name="relationship_id" type="hidden" value="' . $relationship->getId () . '" />';
			?>

			<?php
			if (! isset ( $item1 )) {
				// la deuxième personne est à définir
				$item1 = new Individual();
				echo '<div class="form-row">';
				echo '<div class="form-group col-md-3">';
			    echo '<label for="item1_firstname_i">Prénom</label>';
				echo '<input id="item1_firstname_i" name="item1_firstname" type="text" maxlength="255" class="form-control" />';
				echo '</div>';
				echo '<div class="form-group col-md-3">';
			    echo '<label for="item1_lastname_i">Nom</label>';
				echo '<input id="item1_lastname_i" name="item1_lastname" type="text" maxlength="255" class="form-control" />';
				echo '</div>';
				echo '<div class="form-group col-md-6">';
    			echo '<label for="item1_role_i">Son rôle dans la relation</label>';
    			echo '<input id="item1_role_i" name="item1_role" type="text" value="'.$relationship->getItemRole(1).'" size="20" class="form-control" />';
				echo '</div>';
				echo '</div>';				
			} else {
				// la deuxième personne est définie
				echo '<div class="form-group">';
				echo '<input name="item1_id" type="hidden" value="' . $item1->getId () . '"/>';
    			echo '<label for="item1_role_i">Rôle de '. $item1->getHtmlLinkToIndividual() . '</label>';
    			echo '<input id="item1_role_i" name="item1_role" type="text" value="'.$relationship->getItemRole(1).'" size="20" class="form-control" />';
				echo '</div>';
			}
			?>

			<?php
			if (! isset ( $item0 ) || ! $item0->getId ()) {
				// la première personne est à définir
				echo '<div class="form-row">';
				echo '<div class="form-group col-md-3">';
			    echo '<label for="item0_firstname_i">Prénom</label>';
				echo '<input id="item0_firstname_i" name="item0_firstname" type="text" maxlength="255" class="form-control" />';
				echo '</div>';
				echo '<div class="form-group col-md-3">';
			    echo '<label for="item0_lastname_i">Nom</label>';
				echo '<input id="item0_lastname_i" name="item0_lastname" type="text" maxlength="255" class="form-control" />';
				echo '</div>';
				echo '<div class="form-group col-md-6">';
    			echo '<label for="item0_role_i">Son rôle dans la relation</label>';
    			echo '<input id="item0_role_i" name="item0_role" type="text" value="'.$relationship->getItemRole(0).'" size="20" class="form-control" />';
				echo '</div>';
				echo '</div>';
			} else {
				// la première personne est définie
				echo '<div class="form-group">';
				echo '<input name="item0_id" type="hidden" value="' . $item0->getId () . '"/>';
    			echo '<label for="item0_role_i">Rôle de '. $item0->getHtmlLinkToIndividual() . '</label>';
    			echo '<input id="item0_role_i" name="item0_role" type="text" value="'.$relationship->getItemRole(0).'" size="20" class="form-control" />';
				echo '</div>';
			}
			?>
			<div class="form-row">
			<div class="form-group col-md-3">
    			<label>Année de démarrage</label>
    			<input name="init_year" type="text" value="<?php echo $relationship->getAttribute('init_year'); ?>" size="4" class="form-control" />
			</div>
			
			<div class="form-group col-md-3">
    			<label>Année de clôture</label>
    			<input name="end_year" type="text" value="<?php echo $relationship->getAttribute('end_year'); ?>" size="4" class="form-control" />
			</div>
			</div>
			
			<div class="form-group">
    			<label>Commentaire</label>
    			<textarea name="description" cols="51" rows="5" class="form-control"><?php echo $relationship->getAttribute('description'); ?></textarea>
			</div>
			
			<div class="form-group">
    			<label for="relationship_url_input">url</label>
    			<input id="relationship_url_input" name="url" type="url" size="35" class="form-control" onchange="javascript:checkUrlInput('relationship_url_input', 'relationship_web_link');" value="<?php echo $relationship->getUrl(); ?>" />
    			<a id="relationship_web_link" href="#" style="display: none">[voir]</a>
			</div>
			
			<div>
				<a href="/" class="btn btn-link">quitter</a>
				<button name="relationship_submission" type="submit" value="1" class="btn btn-primary">enregistrer</button>
			</div>
		</form>
		<?php
		if ($relationship->getId()) {
			echo '<p>Tu veux oublier cette relation ? C\'est <a id="delete_a" href="#">ici</a>.</p>';
		}
		?>
	</section>
</div>
<script>
	$(document).ready(function(){
	    $('#item0_role_i').autocomplete({
			minLength: 2,
	   		source: function( request, response ) {
	            $.ajax({
					method:'GET',
	                url:'api/relationships/roles.php',
	                dataType: 'json',
	                data:{
	                    'searchPattern': request.term,
	                    'roleType': 'individualRole'
	                 },
	                 dataFilter: function(data,type){
	                     return JSON.stringify(JSON.parse(data).roles);
	                 },
	                 success : function(data, textStatus, jqXHR){
						response(data);
	                 }
	         	})
	   		},
            focus: function( event, ui ) {
    			$('#item0_role_i').val( ui.item.role);
            	return false;
            },
            select: function( event, ui ) {
            	$('#item0_role_i').val( ui.item.role);
            	return false;
            }
	   	}).autocomplete("instance")._renderItem = function( ul, item ) {
		   	var content = '<div>'+item.role+' <small>('+item.nb+')</small></div>';
		   	return $( "<li>" ).append(content).appendTo( ul );
		};
	    $('#item1_role_i').autocomplete({
			minLength: 2,
	   		source: function( request, response ) {
	            $.ajax({
					method:'GET',
	                url:'api/relationships/roles.php',
	                dataType: 'json',
	                data:{
	                    'searchPattern': request.term,
	                    'roleType': 'individualRole'
	                 },
	                 dataFilter: function(data,type){
	                     return JSON.stringify(JSON.parse(data).roles);
	                 },
	                 success : function(data, textStatus, jqXHR){
						response(data);
	                 }
	         	})
	   		},
            focus: function( event, ui ) {
    			$('#item1_role_i').val( ui.item.role);
            	return false;
            },
            select: function( event, ui ) {
            	$('#item1_role_i').val( ui.item.role);
            	return false;
            }
	   	}).autocomplete("instance")._renderItem = function( ul, item ) {
		   	var content = '<div>'+item.role+' <small>('+item.nb+')</small></div>';
		   	return $( "<li>" ).append(content).appendTo( ul );
		};	    
	})
</script>
<?php if($relationship->hasId()): ?>
<script type="text/javascript">
	document.addEventListener("DOMContentLoaded", function() {
		const delete_a = document.getElementById('delete_a');
		delete_a.addEventListener('click', function (event) {
		  event.preventDefault();
		  var xhr = new XMLHttpRequest();
		  xhr.open("POST", "api/relationships/", true);
		  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		  xhr.responseType = 'json';
		  xhr.onreadystatechange = function () {
		    if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
		    	alert(this.response.message);
		    	if (this.response.data.location !== undefined) {
			    	window.location.replace(this.response.data.location);
		    	}
	    	}				  
		  };
		  xhr.send("id=<?php echo $relationship->getId() ?>&task=deletion");
		});
	});
</script>
<?php endif; ?>
</body>
</html>