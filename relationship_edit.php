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

$relationship = new Relationship ();

// Formatage des données saisies par l'utilisateur
if (isset ( $_POST )) {
	ToolBox::formatUserPost ( $_POST );
}

if (! empty ( $_REQUEST ['relationship_id'] )) {
	// la participation à traiter est identifiée
	$relationship->setId ( $_REQUEST ['relationship_id'] );
	$relationship->feed ();
	
	if (isset ( $_POST ['relationship_deletion'] )) {
		// demande de suppression de la participation
		$item0 = $relationship->getItem(0);
		$relationship->delete();
		header('location:society.php?society_id='.$item0->getId());
		exit;
	} else {
		// récupération des données en base
		$item0 = $relationship->getItem ( 0 );
		if (is_object ( $item0 )) {
			$item0->feed ();
		}
		$item1 = $relationship->getItem ( 1 );
		if (is_object ( $item1 )) {
			$item1->feed ();
		}
	}
} else {
	// la relation est nouvelle
	// on récupère les identifiants, éventuellement fournis, des sociétés impliquées  
	if (isset ( $_REQUEST ['item0_id'] )) {
		$item0 = new Society ( $_REQUEST ['item0_id'] );
		$item0->feed();
		$relationship->setItem ($item0, 0);
	}
	if (isset ( $_REQUEST ['item1_id'] )) {
		$item1 = new Society ( $_REQUEST ['item1_id'] );
		$item1->feed();
		$relationship->setItem($item1, 1);
	}
}

if (isset ( $_POST ['relationship_submission'] )) {
	// enregistrement des données de la participation
	$relationship->feed ( $_POST );
	if (is_null ( $item0 ) && ! empty ( $_POST ['item0_name'] )) {
		$item0 = new Society ();
		$item0->setName ( $_POST ['item0_name'] );
		if (! $item0->identifyFromName ()) {
			$item0->toDB ();
		}
		$relationship->setItem ( $item0, 0 );
	}
	if (is_null ( $item1 ) && ! empty ( $_POST ['item1_name'] )) {
		$item1 = new Society ();
		$item1->setName ( $_POST ['item1_name'] );
		if (! $item1->identifyFromName ()) {
			$item1->toDB ();
		}
		$relationship->setItem ( $item1, 1 );
	}
	if ($relationship->toDB ()) {
		header ( 'location:society.php?society_id=' . $item0->getId () );
		exit ();
	}
}
if (isset ( $item0 ) && isset ( $item1 )) {
	$h1_content = 'Une relation entre ' . $item0->getHtmlLinkToSociety() . ' et ' . $item1->getHtmlLinkToSociety();
} else {
	$h1_content = isset ( $item0 ) && $item0->getId() ? 'Une relation de ' . $item0->getHtmlLinkToSociety() : 'Une relation';
}
?>
<!doctype html>
<html lang="fr">
<head>
    <title><?php echo strip_tags($h1_content) ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_URI ?>" type="text/css" />
    <link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_THEME_URI ?>" type="text/css" />
    <link rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>main.css" type="text/css">
    <script type="text/javascript" src="js/controls.js"></script>
	<script type="text/javascript" src="<?php echo JQUERY_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo JQUERY_UI_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo BOOTSTRAP_JS_URI; ?>"></script>
<body>
<?php include 'navbar.inc.php'; ?>
<div class="container-fluid">
	<h1><?php echo $h1_content ?></h1>
	<section>
		<form id="relationship_form" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
			<input name="item0_class" type="hidden" value="Society" />
			<input name="item1_class" type="hidden" value="Society" />
			<?php
			if ($relationship->getId ())
				echo '<input name="relationship_id" type="hidden" value="' . $relationship->getId () . '" />';

			if (! isset ( $item0 ) || ! $item0->getId ()) {
				// la première société est à définir
				echo '<div class="form-group">';
			    echo '<label for="s1_name_i">Nom de la première société</label>';
				echo '<input id="s1_name_i" name="item0_name" type="text" maxlength="255" class="form-control" />';
				echo '</div>';
				echo '<div class="form-group">';
    			echo '<label>Son rôle</label>';
    			echo '<input id="s1_role_i" name="item0_role" type="text" value="'.$relationship->getItemRole(0).'" size="20" class="form-control" />';
				echo '</div>';
			} else {
				// la première société est définie
				echo '<div class="form-group">';
				echo '<input name="item0_id" type="hidden" value="' . $item0->getId () . '"/>';
    			echo '<label>Rôle '. $item0->getHtmlLinkToSociety() . '</label>';
    			echo '<input id="s1_role_i" name="item0_role" type="text" value="'.$relationship->getItemRole(0).'" size="20" class="form-control" />';
				echo '</div>';
			}
			if (! isset ( $item1 )) {
				// la deuxième société est à définir
				$item1 = new Society ();
				echo '<div class="form-group">';
				echo '<label for="s2_name_i">Nom de la deuxième société</label>';
				echo '<input id="s2_name_i" name="item1_name" type="text" maxlength="255" class="form-control" />';
				echo '</div>';
				echo '<div class="form-group">';
    			echo '<label>Son rôle</label>';
    			echo '<input id="s2_role_i" name="item1_role" type="text" value="'.$relationship->getItemRole(1).'" size="20" class="form-control" />';
				echo '</div>';				
			} else {
				// la deuxième société est définie
				echo '<div class="form-group">';
				echo '<input name="item1_id" type="hidden" value="' . $item1->getId () . '"/>';
    			echo '<label>Rôle '. $item1->getHtmlLinkToSociety() . '</label>';
    			echo '<input id="s2_role_i" name="item1_role" type="text" value="'.$relationship->getItemRole(1).'" size="20" class="form-control" />';
				echo '</div>';
			}
			?>

			<div class="form-group">
    			<label>Année de démarrage</label>
    			<input name="init_year" type="text" value="<?php echo $relationship->getAttribute('init_year'); ?>" size="4" class="form-control" />
			</div>
			
			<div class="form-group">
    			<label>Année de clôture</label>
    			<input name="end_year" type="text" value="<?php echo $relationship->getAttribute('end_year'); ?>" size="4" class="form-control" />
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
			
			<button name="relationship_submission" type="submit" value="1" class="btn btn-primary">Enregistrer</button>
			<?php if ($relationship->getId()) : ?>
				<button name="relationship_deletion" type="submit" value="1" class="btn btn-default">Supprimer</button>
			<?php endif; ?>
		</form>
	</section>
</div>
<script type="text/javascript">
	$(document).ready(function(){
	    $('#s1_name_i').autocomplete({
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
				$('#s1_name_i').val( ui.item.value );
	        	return false;
	        },
	        select: function( event, ui ) {
				$('#s1_name_i').val( ui.item.value );
	        	return false;
	        }
	   	}).autocomplete( "instance" )._renderItem = function( ul, item ) {
		    return $( "<li>" ).append(item.label).appendTo( ul );
	    };
	    $('#s2_name_i').autocomplete({
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
				$('#s2_name_i').val( ui.item.value );
	        	return false;
	        },
	        select: function( event, ui ) {
				$('#s2_name_i').val( ui.item.value );
	        	return false;
	        }
	   	}).autocomplete( "instance" )._renderItem = function( ul, item ) {
		    return $( "<li>" ).append(item.label).appendTo( ul );
	    };	    
	    $('#s1_role_i').autocomplete({
			minLength: 2,
	   		source: function( request, response ) {
	            $.ajax({
					method:'GET',
	                url:'relationship_roles.json.php',
	                dataType: 'json',
	                data:{
	                    'query': request.term
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
				$('#s1_role_i').val( ui.item.value );
	        	return false;
	        },
	        select: function( event, ui ) {
				$('#s1_role_i').val( ui.item.value );
	        	return false;
	        }
	   	}).autocomplete( "instance" )._renderItem = function( ul, item ) {
		    return $( "<li>" ).append(item.label).appendTo( ul );
	    };
	    $('#s2_role_i').autocomplete({
			minLength: 2,
	   		source: function( request, response ) {
	            $.ajax({
					method:'GET',
	                url:'relationship_roles.json.php',
	                dataType: 'json',
	                data:{
	                    'query': request.term
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
				$('#s2_role_i').val( ui.item.value );
	        	return false;
	        },
	        select: function( event, ui ) {
				$('#s2_role_i').val( ui.item.value );
	        	return false;
	        }
	   	}).autocomplete( "instance" )._renderItem = function( ul, item ) {
		    return $( "<li>" ).append(item.label).appendTo( ul );
	    };	    
	})
</script>
</body>
</html>